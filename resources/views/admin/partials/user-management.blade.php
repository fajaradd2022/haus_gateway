{{-- Panel User Management (list + search + CRUD) --}}
<div class="admin-card" id="userMgmtCard">
    <div class="admin-card-head">
        <div>
            <div class="admin-card-title">👥 User Management</div>
            <div class="admin-card-sub" id="userCount">{{ count($adminData['users']) }} pengguna terdaftar</div>
        </div>
        <div class="admin-card-head--actions">
            <input
                type="search"
                placeholder="Cari user..."
                id="userSearch"
                style="padding:7px 12px;border:1px solid var(--wa-border);border-radius:999px;background:var(--wa-input-bg);color:var(--wa-text);font-size:13px;outline:none;width:180px;min-width:120px;"
            >
            <button class="btn-primary" type="button" id="addUserBtn" style="padding:7px 14px;font-size:13px;white-space:nowrap;">
                + Tambah User
            </button>
        </div>
    </div>

    <div class="user-mgmt-section">
    <div class="user-grid" id="userGrid">
        <div class="user-row-head">
            <div>Nama</div>
            <div>Role</div>
            <div>Status</div>
            <div>Last Login</div>
            <div style="text-align:right;">Aksi</div>
        </div>

        @forelse($adminData['users'] as $u)
        <div class="user-row"
            data-user-row
            data-user-id="{{ $u->id }}"
            data-user-name="{{ strtolower($u->name) }}"
            data-user-email="{{ strtolower($u->email) }}"
            data-user-json="{{ htmlspecialchars(json_encode(['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role]), ENT_QUOTES) }}"
        >
            <div class="user-row-identity">
                <span class="avatar avatar-sm" style="background:{{ $u->role === 'admin' ? '#845ec2' : 'var(--wa-accent)' }}">
                    {{ strtoupper(substr($u->name, 0, 1)) }}
                </span>
                <div>
                    <div class="user-row-name">{{ $u->name }}</div>
                </div>
            </div>
            <div>
                @if($u->role === 'admin')
                <span class="badge" style="background:rgba(137,87,229,.15);color:#845ec2;">Admin</span>
                @else
                <span class="badge badge--on_progress">Agent</span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <span class="online-dot {{ $u->is_online ? 'online' : '' }}"></span>
                <span style="font-size:13px;color:var(--wa-text-sub);">{{ $u->is_online ? 'Online' : 'Offline' }}</span>
            </div>
            <div class="user-row-last-login">
                {{ $u->last_login ? $u->last_login->format('d M H:i') : '—' }}
            </div>
            <div style="display:flex;justify-content:flex-end;gap:6px;">
                <button class="btn-ghost-sm user-edit-btn" type="button" style="font-size:12px;padding:4px 10px;">
                    ✏️ Edit
                </button>
                @if($u->id !== $adminData['currentUser']['id'])
                <button class="btn-danger-sm user-delete-btn" type="button" style="font-size:12px;padding:4px 10px;">
                    🗑 Hapus
                </button>
                @endif
            </div>
        </div>
        @empty
        <div id="userEmptyRow" style="padding:24px;text-align:center;color:var(--wa-text-sub);font-size:14px;grid-column:1/-1;">
            Belum ada user
        </div>
        @endforelse
    </div>
    </div>{{-- /.user-mgmt-section --}}
</div>
