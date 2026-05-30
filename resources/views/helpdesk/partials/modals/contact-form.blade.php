{{-- Modal: Add/Edit Contact --}}
<div class="modal-backdrop" id="contactModal">
    <div class="modal">
        <div class="modal-head">
            <h3 id="contactModalTitle">👤 Tambah Kontak</h3>
            <button class="icon-btn" type="button" data-modal-close="contactModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="contactModalId">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-field" style="grid-column:1/-1;">
                    <label>Nama Lengkap *</label>
                    <input type="text" id="cmName" placeholder="Nama pelanggan">
                </div>
                <div class="form-field">
                    <label>Nomor WhatsApp *</label>
                    <input type="text" id="cmPhone" placeholder="+628123456789">
                </div>
                <div class="form-field">
                    <label>Email</label>
                    <input type="email" id="cmEmail" placeholder="email@domain.com">
                </div>
                <div class="form-field">
                    <label>Perusahaan</label>
                    <input type="text" id="cmCompany" placeholder="Nama perusahaan">
                </div>
                <div class="form-field">
                    <label>Departemen</label>
                    <input type="text" id="cmDept" placeholder="Departemen">
                </div>
                <div class="form-field" style="grid-column:1/-1;">
                    <label>Catatan Internal</label>
                    <textarea id="cmNotes" rows="2" placeholder="Catatan tentang kontak ini..."></textarea>
                </div>
                <div class="form-field">
                    <label style="display:flex;align-items:center;gap:6px;text-transform:none;letter-spacing:0;">
                        <input type="checkbox" id="cmVip" style="accent-color:var(--wa-accent);">
                        Tandai sebagai VIP
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="contactModal">Batal</button>
            <button class="btn-primary-sm" type="button" id="contactModalSave">Simpan</button>
        </div>
    </div>
</div>
