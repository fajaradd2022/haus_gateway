{{-- Modal: Tambah/Edit User --}}
<div id="userModal" style="display:none;position:fixed;inset:0;background:var(--wa-overlay);z-index:200;place-items:center;" onclick="if(event.target===this)closeUserModal()">
    <div style="background:var(--wa-side-bg);border-radius:12px;width:min(480px,94vw);box-shadow:0 8px 32px rgba(0,0,0,.25);overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--wa-border);">
            <h3 id="userModalTitle" style="font-size:16px;font-weight:700;color:var(--wa-text);">Tambah User</h3>
            <button onclick="closeUserModal()" style="background:none;border:none;cursor:pointer;color:var(--wa-text-sub);line-height:1;">✕</button>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
            <input type="hidden" id="umId">
            <div class="admin-form-field">
                <label>Nama Lengkap *</label>
                <input type="text" id="umName" placeholder="cth: Budi Santoso" autocomplete="off">
            </div>
            <div class="admin-form-field">
                <label>Alamat Email *</label>
                <input type="email" id="umEmail" placeholder="budi@perusahaan.com" autocomplete="off">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="admin-form-field">
                    <label>Role *</label>
                    <select id="umRole">
                        <option value="agent">Agent</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label id="umPasswordLabel">Password *</label>
                    <div style="position:relative;">
                        <input type="password" id="umPassword" placeholder="Min. 8 karakter" autocomplete="new-password" style="padding-right:38px;">
                        <button type="button" onclick="togglePassVis()" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--wa-text-sub);font-size:14px;" id="passVisBtn">👁</button>
                    </div>
                    <div id="umPasswordHint" style="font-size:11px;color:var(--wa-text-sub);margin-top:3px;">Kosongkan untuk tidak mengubah password</div>
                </div>
            </div>
            <div id="umError" style="display:none;background:rgba(241,92,92,.1);border:1px solid rgba(241,92,92,.3);color:#c0392b;padding:8px 12px;border-radius:8px;font-size:13px;"></div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid var(--wa-border);">
            <button onclick="closeUserModal()" style="padding:8px 16px;border:1px solid var(--wa-border);border-radius:8px;background:transparent;color:var(--wa-text);cursor:pointer;font-size:13px;">Batal</button>
            <button id="umSaveBtn" onclick="saveUser()" style="padding:8px 20px;background:var(--wa-accent);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;">Simpan</button>
        </div>
    </div>
</div>
