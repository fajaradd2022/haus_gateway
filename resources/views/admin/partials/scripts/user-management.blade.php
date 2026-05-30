{{-- CRUD User: search, modal, save, render row, delete --}}
document.getElementById('userSearch')?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('[data-user-row]').forEach(row => {
        const name  = row.dataset.userName ?? '';
        const email = row.dataset.userEmail ?? '';
        row.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
    });
});

function openUserModal(user = null) {
    document.getElementById('umId').value       = user?.id ?? '';
    document.getElementById('umName').value     = user?.name ?? '';
    document.getElementById('umEmail').value    = user?.email ?? '';
    document.getElementById('umRole').value     = user?.role ?? 'agent';
    document.getElementById('umPassword').value = '';
    document.getElementById('umError').style.display = 'none';
    document.getElementById('userModalTitle').textContent = user ? '✏️ Edit User' : '➕ Tambah User';
    document.getElementById('umPasswordLabel').textContent = user ? 'Password baru' : 'Password *';
    document.getElementById('umPasswordHint').style.display = user ? '' : 'none';
    document.getElementById('userModal').style.display = 'grid';
    document.getElementById('umName').focus();
}

window.closeUserModal = function () {
    document.getElementById('userModal').style.display = 'none';
};

window.togglePassVis = function () {
    const inp = document.getElementById('umPassword');
    inp.type = inp.type === 'password' ? 'text' : 'password';
};

window.saveUser = async function () {
    const id       = document.getElementById('umId').value;
    const name     = document.getElementById('umName').value.trim();
    const email    = document.getElementById('umEmail').value.trim();
    const role     = document.getElementById('umRole').value;
    const password = document.getElementById('umPassword').value;
    const errEl    = document.getElementById('umError');
    const saveBtn  = document.getElementById('umSaveBtn');

    errEl.style.display = 'none';

    if (!name || !email || (!id && !password)) {
        errEl.textContent = 'Nama, email, dan password wajib diisi.';
        errEl.style.display = '';
        return;
    }

    const body = { name, email, role };
    if (password) body.password = password;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    try {
        const data = id
            ? await adminApi(`/api/users/${id}`, { method: 'PATCH', body: JSON.stringify(body) })
            : await adminApi('/api/users', { method: 'POST', body: JSON.stringify(body) });

        closeUserModal();
        renderUserRow(data.user, id ? id : null);
        updateUserCount();
    } catch (err) {
        const msg = err?.message ?? (err?.errors ? Object.values(err.errors).flat().join(' ') : 'Terjadi kesalahan.');
        errEl.textContent = msg;
        errEl.style.display = '';
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Simpan';
    }
};

function renderUserRow(user, existingId) {
    const grid = document.getElementById('userGrid');
    document.getElementById('userEmptyRow')?.remove();

    const selfId = {{ $adminData['currentUser']['id'] }};
    const badgeHtml = user.role === 'admin'
        ? `<span class="badge" style="background:rgba(137,87,229,.15);color:#845ec2;">Admin</span>`
        : `<span class="badge badge--on_progress">Agent</span>`;
    const deleteBtn = user.id !== selfId
        ? `<button class="btn-danger-sm user-delete-btn" type="button" style="font-size:12px;padding:4px 10px;">🗑 Hapus</button>`
        : '';
    const initial = (user.name ?? 'U')[0].toUpperCase();
    const avatarBg = user.role === 'admin' ? '#845ec2' : 'var(--wa-accent)';

    const html = `
        <div class="user-row-identity">
            <span class="avatar avatar-sm" style="background:${avatarBg}">${initial}</span>
            <div><div class="user-row-name">${esc(user.name)}</div></div>
        </div>
        <div>${badgeHtml}</div>
        <div style="display:flex;align-items:center;gap:6px;">
            <span class="online-dot"></span>
            <span style="font-size:13px;color:var(--wa-text-sub);">Offline</span>
        </div>
        <div class="user-row-last-login">—</div>
        <div style="display:flex;justify-content:flex-end;gap:6px;">
            <button class="btn-ghost-sm user-edit-btn" type="button" style="font-size:12px;padding:4px 10px;">✏️ Edit</button>
            ${deleteBtn}
        </div>`;

    if (existingId) {
        const existing = document.querySelector(`[data-user-id="${existingId}"]`);
        if (existing) {
            existing.dataset.userJson  = JSON.stringify({ id: user.id, name: user.name, email: user.email, role: user.role });
            existing.dataset.userName  = user.name.toLowerCase();
            existing.dataset.userEmail = user.email.toLowerCase();
            existing.innerHTML = html;
            return;
        }
    }

    const div = document.createElement('div');
    div.className = 'user-row';
    div.dataset.userRow   = '';
    div.dataset.userId    = user.id;
    div.dataset.userName  = user.name.toLowerCase();
    div.dataset.userEmail = user.email.toLowerCase();
    div.dataset.userJson  = JSON.stringify({ id: user.id, name: user.name, email: user.email, role: user.role });
    div.innerHTML = html;
    grid.appendChild(div);
}

function updateUserCount() {
    const count = document.querySelectorAll('[data-user-row]').length;
    document.getElementById('userCount').textContent = `${count} pengguna terdaftar`;
}

document.getElementById('addUserBtn')?.addEventListener('click', () => openUserModal());

document.getElementById('userGrid')?.addEventListener('click', async (e) => {
    const row = e.target.closest('[data-user-row]');
    if (!row) return;
    const user = JSON.parse(row.dataset.userJson ?? '{}');
    const id   = row.dataset.userId;

    if (e.target.closest('.user-edit-btn')) {
        openUserModal(user);
        return;
    }

    if (e.target.closest('.user-delete-btn')) {
        if (!confirm(`Hapus user "${user.name}"? Tindakan ini tidak dapat dibatalkan.`)) return;
        try {
            await adminApi(`/api/users/${id}`, { method: 'DELETE' });
            row.remove();
            updateUserCount();
        } catch (err) {
            alert(err?.message ?? 'Gagal menghapus user.');
        }
    }
});
