{{-- Modal: Move Messages to Another Ticket --}}
<div class="modal-backdrop" id="moveTicketModal">
    <div class="modal" style="max-width:500px;">
        <div class="modal-head">
            <h3>↗️ Pindahkan ke Tiket Lain</h3>
            <button class="icon-btn" type="button" data-modal-close="moveTicketModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="mtSourceId">

            <div class="form-group">
                <label class="form-label">Tiket Tujuan</label>
                <select id="mtTargetTicket" class="form-input"></select>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Pesan yang Dipindahkan</label>
                <div id="mtMessageList" class="mt-msg-list"></div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="moveTicketModal">Batal</button>
            <button class="btn-accent-sm" type="button" id="mtConfirmBtn">Pindahkan</button>
        </div>
    </div>
</div>
