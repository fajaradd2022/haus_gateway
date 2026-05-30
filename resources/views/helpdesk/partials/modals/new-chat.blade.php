{{-- Modal: New Chat (cari kontak → isi detail tiket) --}}
<div class="modal-backdrop" id="newChatModal">
    <div class="modal">
        <div class="modal-head">
            <h3>💬 New Chat</h3>
            <button class="icon-btn" type="button" data-modal-close="newChatModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            {{-- Step tabs --}}
            <div class="step-tabs">
                <button class="step-tab active" id="ncStep1Tab">1. Pilih Kontak</button>
                <button class="step-tab" id="ncStep2Tab" disabled>2. Detail Tiket</button>
            </div>

            {{-- Step 1: Contact search --}}
            <div id="ncStep1">
                <div class="form-field">
                    <label>Cari kontak (nama / nomor WA)</label>
                    <input type="search" id="ncContactSearch" placeholder="Ketik nama atau +628...">
                </div>
                <div class="contact-search-results" id="ncContactResults">
                    <div style="padding:14px;text-align:center;color:var(--wa-text-sub);font-size:13px;">Ketik untuk mencari kontak...</div>
                </div>
                <div class="new-contact-link" id="ncCreateNewBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    Buat kontak baru dengan nomor ini
                </div>
            </div>

            {{-- Step 2: Ticket details --}}
            <div id="ncStep2" hidden>
                <div id="ncSelectedContact" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--wa-active);border-radius:var(--radius-md);margin-bottom:4px;">
                    <span class="avatar avatar-sm" id="ncSelAvatar">C</span>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--wa-accent);" id="ncSelName">-</div>
                        <div style="font-size:12px;color:var(--wa-text-sub);" id="ncSelPhone">-</div>
                    </div>
                </div>
                <div class="form-field">
                    <label>Subjek / Topik</label>
                    <input type="text" id="ncSubject" placeholder="cth: Reset password akun email">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-field">
                        <label>Kategori</label>
                        <select id="ncCategory">
                            <option value="">— Pilih —</option>
                            <option value="Masalah Teknis">Masalah Teknis</option>
                            <option value="Penagihan">Penagihan</option>
                            <option value="Informasi">Informasi Umum</option>
                            <option value="Akses">Akses / Permission</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Prioritas</label>
                        <select id="ncPriority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="newChatModal">Batal</button>
            <button class="btn-secondary-sm" type="button" id="ncBackBtn" hidden>← Kembali</button>
            <button class="btn-primary-sm" type="button" id="ncNextBtn" disabled>Lanjut →</button>
            <button class="btn-primary-sm" type="button" id="ncSubmitBtn" hidden>Buka Chat ✓</button>
        </div>
    </div>
</div>
