{{-- Modal: Set Ticket Subject (for unnamed inbound tickets) --}}
<div class="modal-backdrop" id="subjectModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-head">
            <h3>📝 Beri Nama Tiket</h3>
            <button class="icon-btn" type="button" data-modal-close="subjectModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--wa-text-sub);margin-bottom:12px;line-height:1.5;">
                Tiket ini berasal dari pesan WhatsApp masuk. Beri nama agar mudah diidentifikasi.
            </p>
            <input type="hidden" id="smTicketId">
            <div class="form-group">
                <label class="form-label">Nama Tiket</label>
                <input type="text" id="smSubjectInput" class="form-input" placeholder="cth: Kendala Login App, Request Reset Password..." maxlength="255">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="subjectModal">Batal</button>
            <button class="btn-accent-sm" type="button" id="smSaveBtn">Simpan Nama</button>
        </div>
    </div>
</div>
