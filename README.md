# Mini Helpdesk AI Assist (HAUS Gateway)

> Aplikasi helpdesk omnichannel internal berbasis web dengan tampilan mirip WhatsApp Web, dilengkapi **AI Co-Pilot** berbasis RAG untuk membantu agen menyusun balasan lebih cepat dan akurat.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Arsitektur Singkat](#arsitektur-singkat)
- [Prasyarat](#prasyarat)
- [Instalasi Lokal](#instalasi-lokal)
- [Environment Variables](#environment-variables)
- [Jalankan dengan Docker](#jalankan-dengan-docker)
- [Struktur Direktori](#struktur-direktori)
- [API Endpoints](#api-endpoints)
- [Alur Kerja Agen](#alur-kerja-agen)
- [Lisensi](#lisensi)

---

## Fitur Utama

| Fitur | Keterangan |
|-------|-----------|
| **WhatsApp Inbox** | Tampilan chat mirip WhatsApp Web; pesan masuk/keluar via [WAHA](https://waha.devlike.pro/) |
| **AI Suggestion Panel** | Panel samping kanan yang menampilkan 3 draf balasan berbasis RAG (Knowledge Base + konteks tiket) |
| **Human-in-the-Loop** | AI hanya menyarankan — agen tetap yang klik, edit, dan kirim. Tidak ada auto-reply |
| **Quick Chat Templates** | Template balasan cepat yang bisa dikelola admin, muncul sebagai chip di composer |
| **Ticketing & SLA** | Setiap percakapan dijadikan tiket dengan status (Open → On Progress → Closed) dan SLA deadline |
| **Knowledge Base** | Admin bisa mengelola artikel SOP yang digunakan sebagai konteks oleh AI |
| **Manajemen Kontak** | Profil pelanggan lengkap (nama, perusahaan, tag, riwayat tiket) |
| **Internal Notes** | Catatan internal antar agen yang tidak terlihat pelanggan |
| **Audit Trail** | Log lengkap semua aksi (reply, status change, user management, KB, dsb.) |
| **Admin Dashboard** | Statistik tiket, grafik 7 hari, performa agen, manajemen user & KB |
| **Role-Based Access** | Admin (akses penuh) dan Agent (akses workspace) |
| **Dark / Light Mode** | Toggle tema, disimpan di `localStorage` |

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP 8.3 · Laravel 13 |
| Frontend | Vanilla JavaScript · Tailwind CSS 4 · Vite 8 |
| Database | MySQL 8.0 |
| AI / LLM | SumoPod AI (`gpt-4o-mini`, OpenAI-compatible) |
| WhatsApp Gateway | [WAHA](https://waha.devlike.pro/) (self-hosted) |
| Queue | Laravel Database Queue |
| Container | Docker · Nginx · PHP-FPM · Supervisor |

---

## Arsitektur Singkat

```
Pelanggan (WhatsApp)
        │
        ▼
   WAHA Gateway  ──POST /webhook/waha/messages──▶  Laravel Backend
                                                         │
                                          ┌──────────────┴──────────────┐
                                          │                             │
                                     MySQL DB                    SumoPod AI API
                                    (Tickets,                   (RAG Suggestions
                                    Messages,                    via Knowledge Base)
                                    KB, Audit)
                                          │
                                          ▼
                                   Web UI (Agen)
                           ┌────────────────────────────┐
                           │  Sidebar  │   Chat Pane    │ AI Panel │
                           │  (Tiket)  │  (Percakapan)  │ (Saran)  │
                           └────────────────────────────┘
```

---

## Prasyarat

- **PHP** >= 8.3 + extensions: `pdo_mysql`, `mbstring`, `zip`, `bcmath`, `opcache`
- **Composer** >= 2
- **Node.js** >= 26 (lihat `.nvmrc`)
- **MySQL** >= 8.0
- **WAHA** instance dengan webhook yang diarahkan ke `/webhook/waha/messages` dan `/webhook/waha/events`
- **SumoPod AI** API Key (atau endpoint OpenAI-compatible lain)

---

## Instalasi Lokal

```bash
# 1. Clone repo
git clone https://github.com/fajaradd2022/haus_gateway.git
cd haus_gateway

# 2. Install dependencies & setup awal (composer install, key:generate, migrate, npm build)
composer run setup

# 3. Isi konfigurasi di .env
#    (file .env sudah dibuat otomatis oleh composer run setup)
#    Wajib diisi: DB_*, WAHA_*, SUMOPOD_API_KEY

# 4. Jalankan dev server
composer run dev
```

> `composer run dev` menjalankan `php artisan serve`, `queue:listen`, dan `npm run dev` secara bersamaan.

Buka **http://localhost:8000** — login menggunakan akun yang dibuat via `php artisan db:seed` atau langsung lewat `/admin` setelah membuat user pertama di database.

---

## Environment Variables

Salin `.env.example` dan isi nilai berikut:

```env
APP_NAME="Mini Helpdesk AI Assist"
APP_ENV=local
APP_KEY=                        # Di-generate otomatis oleh composer run setup
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=haus_gateway
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# WhatsApp Gateway (WAHA)
WAHA_URL=https://your-waha-server.example.com
WAHA_API_KEY=your_waha_api_key
WAHA_SESSION=default

# SumoPod AI (OpenAI-compatible)
SUMOPOD_BASE_URL=https://ai.sumopod.com/v1
SUMOPOD_API_KEY=your_sumopod_api_key
```

---

## Jalankan dengan Docker

### Prasyarat
- Docker Engine >= 24
- Docker Compose >= 2.20

### Langkah

```bash
# 1. Siapkan file env untuk Docker
cp .env.example .env.docker
# Edit .env.docker: isi APP_KEY, DB_PASSWORD, WAHA_*, SUMOPOD_API_KEY

# 2. Build dan jalankan
docker compose --env-file .env.docker up -d --build

# 3. Cek status
docker compose ps
docker compose logs -f app
```

Aplikasi berjalan di **http://localhost:8080** (ubah port via `APP_PORT` di env).

### Variabel Docker Compose

| Variabel | Default | Keterangan |
|----------|---------|-----------|
| `APP_PORT` | `8080` | Port host untuk web |
| `APP_KEY` | — | Wajib diisi |
| `DB_DATABASE` | `haus_gateway` | Nama database |
| `DB_USERNAME` | `helpdesk` | User database |
| `DB_PASSWORD` | `secret` | Password database (ganti di produksi!) |
| `DB_ROOT_PASSWORD` | `rootsecret` | Root password MySQL |
| `WAHA_URL` | — | Wajib diisi |
| `WAHA_API_KEY` | — | Wajib diisi |
| `SUMOPOD_API_KEY` | — | Wajib diisi |

### Arsitektur Container

```
docker compose up
├── app  (php:8.3-fpm-alpine)
│   ├── Nginx (port 80 → host:APP_PORT)
│   ├── PHP-FPM (php-fpm:9000)
│   └── Queue Worker ×2 (php artisan queue:work)
└── db   (mysql:8.0)
    └── Volume: db_data
```

---

## Struktur Direktori

```
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── HelpdeskController.php     # Controller utama (workspace + admin)
│   │   ├── ContactController.php      # Manajemen kontak
│   │   ├── QuickChatController.php    # CRUD template balasan cepat
│   │   └── WahaWebhookController.php  # Penerima webhook WhatsApp
│   ├── Models/
│   │   ├── Ticket.php, Message.php, Customer.php, Contact.php
│   │   ├── KnowledgeBase.php, QuickChat.php, AuditLog.php, Tag.php
│   └── Services/
│       ├── AiService.php              # RAG suggestion via SumoPod AI
│       └── WahaService.php            # Kirim pesan via WAHA API
├── resources/
│   ├── css/app.css                    # Semua style (CSS variables, komponen)
│   ├── js/app.js                      # SPA vanilla JS (~1800 baris)
│   └── views/
│       ├── helpdesk.blade.php         # Halaman workspace agen
│       ├── admin.blade.php            # Halaman admin dashboard
│       ├── helpdesk/partials/         # Chat pane, sidebar, modals, composer
│       └── admin/partials/            # Stats, charts, KB, quick-chat, audit, user
├── docker/
│   ├── nginx.conf                     # Konfigurasi Nginx
│   ├── supervisord.conf               # Supervisor (php-fpm + nginx + queue)
│   └── entrypoint.sh                  # Bootstrap container (migrate, cache, dll.)
├── Dockerfile                         # 3-stage multi-stage build
├── docker-compose.yml                 # Orkestrasi app + MySQL
└── .dockerignore
```

---

## API Endpoints

### Workspace
| Method | Path | Keterangan |
|--------|------|-----------|
| `GET` | `/api/workspace` | Payload awal workspace |
| `GET` | `/api/tickets/{id}` | Detail tiket + pesan + saran AI |
| `POST` | `/api/tickets/{id}/messages` | Kirim pesan / internal note |
| `PATCH` | `/api/tickets/{id}/status` | Ubah status tiket |
| `POST` | `/api/chats/new` | Buat tiket baru (proactive chat) |
| `GET` | `/api/quick-chats` | Template balasan aktif (untuk composer) |

### Admin Only
| Method | Path | Keterangan |
|--------|------|-----------|
| `GET/POST/PATCH/DELETE` | `/api/knowledge[/{id}]` | CRUD Knowledge Base |
| `GET/POST/PATCH/DELETE` | `/api/quick-chats[/{id}]` | CRUD Quick Chat Templates |
| `POST/PATCH/DELETE` | `/api/users[/{id}]` | Manajemen user |
| `GET` | `/api/contacts` | Cari kontak |

### Webhook (No Auth)
| Method | Path | Keterangan |
|--------|------|-----------|
| `POST` | `/webhook/waha/messages` | Pesan masuk dari WAHA |
| `POST` | `/webhook/waha/events` | Event dari WAHA (delivery receipt, session status) |

---

## Alur Kerja Agen

```
1. Login  →  Workspace terbuka (mirip WhatsApp Web)
2. Pilih tiket dari sidebar kiri
3. AI Suggestion Panel (kanan) memuat 3 saran balasan berbasis SOP
4. Agen klik satu saran  →  teks masuk ke kotak composer
5. Agen edit jika perlu  →  tekan Send
6. Pesan terkirim ke pelanggan via WAHA → WhatsApp
7. Agen ubah status tiket jika masalah selesai
```

---

## Lisensi

Proyek ini bersifat **private / internal**. Tidak untuk didistribusikan tanpa izin.
