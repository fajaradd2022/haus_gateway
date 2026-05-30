{{-- Modal: Delete / Archive Chat --}}
<div class="modal-backdrop" id="deleteChatModal">
    <div class="modal" style="max-width:460px;">
        <div class="modal-head">
            <h3>⚠️ Hapus / Arsip Chat</h3>
            <button class="icon-btn" type="button" data-modal-close="deleteChatModal" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--wa-text-sub);line-height:1.55;" id="deleteChatDesc">Tiket ini akan diproses sesuai pilihan di bawah.</p>
            <input type="hidden" id="deleteChatId">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <label class="delete-option-card selected" id="dcArchive">
                    <input type="radio" name="deleteChat" value="archive" checked style="display:none;">
                    <h4>📦 Arsip (Disarankan)</h4>
                    <p>Chat disembunyikan dari daftar aktif, tapi data tetap ada. Jika pelanggan chat lagi, riwayat tidak hilang.</p>
                </label>
                <label class="delete-option-card" id="dcDelete">
                    <input type="radio" name="deleteChat" value="delete" style="display:none;">
                    <h4>🗑 Hapus Permanen</h4>
                    <p>Menghapus tiket beserta seluruh riwayat pesan secara permanen. Tidak dapat dibatalkan.</p>
                </label>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-secondary-sm" type="button" data-modal-close="deleteChatModal">Batal</button>
            <button class="btn-danger-sm" type="button" id="deleteChatConfirm">Lanjutkan</button>
        </div>
    </div>
</div>
