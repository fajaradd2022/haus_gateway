{{-- Modal: Tambah/Edit Artikel Knowledge Base --}}
<div id="kbModal" style="display:none;position:fixed;inset:0;background:var(--wa-overlay);z-index:200;place-items:center;align-items:flex-start;padding-top:40px;" onclick="if(event.target===this)closeKbModal()">
    <div style="background:var(--wa-side-bg);border-radius:12px;width:min(600px,95vw);max-height:85vh;display:flex;flex-direction:column;box-shadow:0 8px 32px rgba(0,0,0,.25);overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--wa-border);flex-shrink:0;">
            <h3 id="kbModalTitle" style="font-size:16px;font-weight:700;color:var(--wa-text);">Tambah Artikel</h3>
            <button onclick="closeKbModal()" style="background:none;border:none;cursor:pointer;color:var(--wa-text-sub);font-size:18px;line-height:1;">✕</button>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:14px;overflow-y:auto;flex:1;">
            <input type="hidden" id="kbId">
            <div class="admin-form-field">
                <label>Judul Artikel *</label>
                <input type="text" id="kbTitle" placeholder="cth: SOP Reset Password">
            </div>
            <div class="admin-form-field">
                <label>Konten / Isi *</label>
                <textarea id="kbContent" rows="8" placeholder="Tulis konten artikel di sini..." style="padding:10px 12px;border:1px solid var(--wa-border);border-radius:var(--radius-md);background:var(--wa-input-bg);color:var(--wa-text);font-size:14px;outline:none;resize:vertical;width:100%;font-family:inherit;transition:border-color 150ms;"></textarea>
            </div>
            <div class="admin-form-field">
                <label>Sumber / Source</label>
                <input type="text" id="kbSource" placeholder="cth: SOP Internal v2, Link URL, dsb.">
            </div>
            <div id="kbError" style="display:none;background:rgba(241,92,92,.1);border:1px solid rgba(241,92,92,.3);color:#c0392b;padding:8px 12px;border-radius:8px;font-size:13px;"></div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid var(--wa-border);flex-shrink:0;">
            <button onclick="closeKbModal()" style="padding:8px 16px;border:1px solid var(--wa-border);border-radius:8px;background:transparent;color:var(--wa-text);cursor:pointer;font-size:13px;">Batal</button>
            <button id="kbSaveBtn" onclick="saveKb()" style="padding:8px 20px;background:var(--wa-accent);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;">Simpan</button>
        </div>
    </div>
</div>
