{{-- Modal: Delete Contact (permanen) --}}
<div class="modal-backdrop" id="deleteContactModal">
    <div class="modal" style="max-width:400px;">
        <div class="modal-head">
            <h3>🗑 Hapus Kontak</h3>
            <button class="icon-btn" type="button" data-modal-close="deleteContactModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--wa-text-sub);line-height:1.6;" id="deleteContactDesc"></p>
            <p style="font-size:13px;color:#c5221f;margin-top:8px;font-weight:500;">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="deleteContactModal">Batal</button>
            <button class="btn-danger-sm" type="button" id="deleteContactConfirm">Hapus Permanen</button>
        </div>
    </div>
</div>
