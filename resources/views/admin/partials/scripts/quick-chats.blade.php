{{-- CRUD Quick Chat Templates: load, modal open/close, save, render card, delete --}}

(async function loadQcList() {
    try {
        const data = await adminApi('/api/quick-chats/all');
        const list = document.getElementById('qcList');
        document.getElementById('qcEmptyRow')?.remove();

        if (!data.quick_chats?.length) {
            list.innerHTML = '<div id="qcEmptyRow" style="text-align:center;padding:32px;color:var(--wa-text-sub);font-size:14px;">Belum ada template quick chat</div>';
            updateQcCount();
            return;
        }

        data.quick_chats.forEach(qc => renderQcCard(qc, null));
        updateQcCount();
    } catch (err) {
        console.error('Failed to load quick chats', err);
    }
})();

function openQcModal(qc = null) {
    document.getElementById('qcId').value         = qc?.id ?? '';
    document.getElementById('qcTitle').value      = qc?.title ?? '';
    document.getElementById('qcBody').value       = qc?.body ?? '';
    document.getElementById('qcCategory').value   = qc?.category ?? '';
    document.getElementById('qcIsActive').checked = qc ? Boolean(qc.is_active) : true;
    document.getElementById('qcError').style.display = 'none';
    document.getElementById('qcModalTitle').textContent = qc ? '✏️ Edit Template' : '💬 Tambah Template';
    document.getElementById('qcModal').style.display = 'grid';
    document.getElementById('qcTitle').focus();
}

window.closeQcModal = function () {
    document.getElementById('qcModal').style.display = 'none';
};

window.saveQc = async function () {
    const id       = document.getElementById('qcId').value;
    const title    = document.getElementById('qcTitle').value.trim();
    const body     = document.getElementById('qcBody').value.trim();
    const category = document.getElementById('qcCategory').value.trim();
    const isActive = document.getElementById('qcIsActive').checked;
    const errEl    = document.getElementById('qcError');
    const saveBtn  = document.getElementById('qcSaveBtn');

    errEl.style.display = 'none';
    if (!title || !body) {
        errEl.textContent = 'Judul dan isi pesan wajib diisi.';
        errEl.style.display = '';
        return;
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    try {
        const payload = { title, body, category: category || null, is_active: isActive };
        const data = id
            ? await adminApi(`/api/quick-chats/${id}`, { method: 'PATCH', body: JSON.stringify(payload) })
            : await adminApi('/api/quick-chats',        { method: 'POST',  body: JSON.stringify(payload) });

        closeQcModal();
        renderQcCard(data.quick_chat, id || null);
        updateQcCount();
    } catch (err) {
        errEl.textContent = err?.message ?? (err?.errors ? Object.values(err.errors).flat().join(' ') : 'Terjadi kesalahan.');
        errEl.style.display = '';
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Simpan';
    }
};

function renderQcCard(qc, existingId) {
    document.getElementById('qcEmptyRow')?.remove();

    const statusBadge = qc.is_active
        ? `<span class="badge badge--on_progress" style="font-size:11px;">Aktif</span>`
        : `<span class="badge" style="font-size:11px;color:var(--wa-text-sub);background:var(--wa-hover);">Nonaktif</span>`;

    const html = `
        <div class="kb-card-header">
            <div class="kb-card-title">${esc(qc.title)}${qc.category ? ` <span style="font-size:11px;font-weight:400;color:var(--wa-text-sub);">#${esc(qc.category)}</span>` : ''}</div>
            <div class="kb-card-actions" style="display:flex;align-items:center;gap:6px;">
                ${statusBadge}
                <button class="btn-ghost-sm qc-edit-btn" type="button" style="font-size:12px;padding:3px 10px;">✏️ Edit</button>
                <button class="btn-danger-sm qc-delete-btn" type="button" style="font-size:12px;padding:3px 10px;">🗑</button>
            </div>
        </div>
        <div class="kb-card-content">${esc(qc.body)}</div>
        <div class="kb-card-footer">
            <span style="font-size:11px;color:var(--wa-text-sub);margin-left:auto;">Diperbarui: ${esc(qc.updated_at ?? '-')}</span>
        </div>`;

    if (existingId) {
        const existing = document.querySelector(`[data-qc-id="${existingId}"]`);
        if (existing) {
            existing.dataset.qcJson = JSON.stringify(qc);
            existing.innerHTML = html;
            return;
        }
    }

    const div = document.createElement('div');
    div.className = 'kb-card';
    div.dataset.qcId   = qc.id;
    div.dataset.qcJson = JSON.stringify(qc);
    div.innerHTML = html;
    document.getElementById('qcList').appendChild(div);
}

function updateQcCount() {
    const count = document.querySelectorAll('[data-qc-id]').length;
    document.getElementById('qcCount').textContent = `${count} template`;
}

document.getElementById('addQcBtn')?.addEventListener('click', () => openQcModal());

document.getElementById('qcList')?.addEventListener('click', async (e) => {
    const card = e.target.closest('[data-qc-id]');
    if (!card) return;
    const qc = JSON.parse(card.dataset.qcJson ?? '{}');
    const id = card.dataset.qcId;

    if (e.target.closest('.qc-edit-btn')) {
        openQcModal(qc);
        return;
    }

    if (e.target.closest('.qc-delete-btn')) {
        if (!confirm(`Hapus template "${qc.title}"? Tindakan ini tidak dapat dibatalkan.`)) return;
        try {
            await adminApi(`/api/quick-chats/${id}`, { method: 'DELETE' });
            card.remove();
            updateQcCount();
        } catch (err) {
            alert(err?.message ?? 'Gagal menghapus template.');
        }
    }
});
