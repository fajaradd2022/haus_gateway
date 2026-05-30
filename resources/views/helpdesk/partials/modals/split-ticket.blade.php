{{-- Modal: Split Chat into New Ticket --}}
<div class="modal-backdrop" id="splitTicketModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-head">
            <h3>✂️ Buat Tiket Baru dari Chat Ini</h3>
            <button class="icon-btn" type="button" data-modal-close="splitTicketModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="stSourceId">

            <div class="form-group">
                <label class="form-label">Nama Tiket Baru</label>
                <input type="text" id="stSubjectInput" class="form-input" placeholder="cth: Follow-up Kendala Login, Eskalasi Permintaan..." maxlength="255">
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Pesan yang Masuk ke Tiket Baru</label>
                <div id="stMessageList" class="mt-msg-list"></div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="splitTicketModal">Batal</button>
            <button class="btn-accent-sm" type="button" id="stConfirmBtn">Buat Tiket Baru</button>
        </div>
    </div>
</div>
