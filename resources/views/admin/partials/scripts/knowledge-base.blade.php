{{-- CRUD Knowledge Base: open modal, save, render card, delete --}}
function openKbModal(article = null) {
    document.getElementById('kbId').value      = article?.id ?? '';
    document.getElementById('kbTitle').value   = article?.title ?? '';
    document.getElementById('kbContent').value = article?.content ?? '';
    document.getElementById('kbSource').value  = article?.source ?? '';
    document.getElementById('kbError').style.display = 'none';
    document.getElementById('kbModalTitle').textContent = article ? '✏️ Edit Artikel' : '📝 Tambah Artikel';
    document.getElementById('kbModal').style.display = 'grid';
    document.getElementById('kbTitle').focus();
}

window.closeKbModal = function () {
    document.getElementById('kbModal').style.display = 'none';
};

window.saveKb = async function () {
    const id      = document.getElementById('kbId').value;
    const title   = document.getElementById('kbTitle').value.trim();
    const content = document.getElementById('kbContent').value.trim();
    const source  = document.getElementById('kbSource').value.trim();
    const errEl   = document.getElementById('kbError');
    const saveBtn = document.getElementById('kbSaveBtn');

    errEl.style.display = 'none';
    if (!title || !content) {
        errEl.textContent = 'Judul dan konten wajib diisi.';
        errEl.style.display = '';
        return;
    }

    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';
    try {
        const data = id
            ? await adminApi(`/api/knowledge/${id}`, { method: 'PATCH', body: JSON.stringify({ title, content, source: source || null }) })
            : await adminApi('/api/knowledge', { method: 'POST', body: JSON.stringify({ title, content, source: source || null }) });

        closeKbModal();
        renderKbCard(data.article, id || null);
        updateKbCount();
    } catch (err) {
        errEl.textContent = err?.message ?? (err?.errors ? Object.values(err.errors).flat().join(' ') : 'Terjadi kesalahan.');
        errEl.style.display = '';
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Simpan';
    }
};

function renderKbCard(article, existingId) {
    document.getElementById('kbEmptyRow')?.remove();

    const html = `
        <div class="kb-card-header">
            <div class="kb-card-title">${esc(article.title)}</div>
            <div class="kb-card-actions">
                <button class="btn-ghost-sm kb-edit-btn" type="button" style="font-size:12px;padding:3px 10px;">✏️ Edit</button>
                <button class="btn-danger-sm kb-delete-btn" type="button" style="font-size:12px;padding:3px 10px;">🗑</button>
            </div>
        </div>
        <div class="kb-card-content">${esc(article.content)}</div>
        <div class="kb-card-footer">
            ${article.source ? `<span class="kb-card-source">${esc(article.source)}</span>` : ''}
            <span style="font-size:11px;color:var(--wa-text-sub);margin-left:auto;">Diperbarui: ${esc(article.updated_at ?? '-')}</span>
        </div>`;

    if (existingId) {
        const existing = document.querySelector(`[data-kb-id="${existingId}"]`);
        if (existing) {
            existing.dataset.kbJson = JSON.stringify(article);
            existing.innerHTML = html;
            return;
        }
    }

    const div = document.createElement('div');
    div.className = 'kb-card';
    div.dataset.kbId   = article.id;
    div.dataset.kbJson = JSON.stringify(article);
    div.innerHTML = html;
    document.getElementById('kbList').appendChild(div);
}

function updateKbCount() {
    const count = document.querySelectorAll('[data-kb-id]').length;
    document.getElementById('kbCount').textContent = `${count} artikel`;
}

document.getElementById('addKbBtn')?.addEventListener('click', () => openKbModal());

document.getElementById('kbList')?.addEventListener('click', async (e) => {
    const card = e.target.closest('[data-kb-id]');
    if (!card) return;
    const article = JSON.parse(card.dataset.kbJson ?? '{}');
    const id      = card.dataset.kbId;

    if (e.target.closest('.kb-edit-btn')) {
        openKbModal(article);
        return;
    }

    if (e.target.closest('.kb-delete-btn')) {
        if (!confirm(`Hapus artikel "${article.title}"? Tindakan ini tidak dapat dibatalkan.`)) return;
        try {
            await adminApi(`/api/knowledge/${id}`, { method: 'DELETE' });
            card.remove();
            updateKbCount();
        } catch (err) {
            alert(err?.message ?? 'Gagal menghapus artikel.');
        }
    }
});
