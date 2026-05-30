{{-- ══════════════════════════════════════════════════════════
     SETTINGS PANEL (slides in from left)
══════════════════════════════════════════════════════════ --}}
<div class="settings-backdrop" id="settingsBackdrop"></div>
<aside class="settings-panel" id="settingsPanel" aria-label="Settings">
    <div class="settings-panel-head">
        <button class="icon-btn" type="button" id="closeSettings" aria-label="Close settings">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
        </button>
        <h2>Settings</h2>
    </div>

    <div class="settings-body">
        {{-- Profile --}}
        <div class="settings-profile">
            <span class="avatar avatar-xl" data-settings-avatar>AG</span>
            <div class="settings-profile-info">
                <div class="settings-profile-name" data-settings-name>Agent</div>
                <div class="settings-profile-email" data-settings-email>-</div>
                <span class="settings-profile-role" data-settings-role>agent</span>
            </div>
        </div>

        {{-- Appearance --}}
        <div class="settings-section">
            <div class="settings-section-title">Appearance</div>
            <div class="theme-options">
                <button class="theme-btn active" type="button" data-theme-set="light">
                    <div class="theme-preview">
                        <div class="theme-preview-left"></div>
                        <div class="theme-preview-right"></div>
                    </div>
                    ☀ Light
                </button>
                <button class="theme-btn dark" type="button" data-theme-set="dark">
                    <div class="theme-preview">
                        <div class="theme-preview-left"></div>
                        <div class="theme-preview-right"></div>
                    </div>
                    🌙 Dark
                </button>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="settings-section">
            <div class="settings-section-title">Notifications</div>
            <label class="settings-row">
                <div>
                    <div class="settings-row-label">Browser notifications</div>
                    <div class="settings-row-sub">Alert saat ada pesan masuk baru</div>
                </div>
                <input type="checkbox" id="notifToggle" style="accent-color:var(--wa-accent);width:18px;height:18px;cursor:pointer;">
            </label>
            <label class="settings-row">
                <div>
                    <div class="settings-row-label">Sound alerts</div>
                    <div class="settings-row-sub">Suara notifikasi pesan baru</div>
                </div>
                <input type="checkbox" id="soundToggle" style="accent-color:var(--wa-accent);width:18px;height:18px;cursor:pointer;">
            </label>
        </div>

        {{-- Keyboard Shortcuts --}}
        <div class="settings-section">
            <div class="settings-section-title">Keyboard Shortcuts</div>
            <div class="shortcut-list">
                <div class="shortcut-item">
                    <span>Send message</span>
                    <kbd>Enter</kbd>
                </div>
                <div class="shortcut-item">
                    <span>New line</span>
                    <kbd>Shift + Enter</kbd>
                </div>
                <div class="shortcut-item">
                    <span>Internal note mode</span>
                    <kbd>Alt + N</kbd>
                </div>
                <div class="shortcut-item">
                    <span>Focus search</span>
                    <kbd>Ctrl + /</kbd>
                </div>
                <div class="shortcut-item">
                    <span>Close ticket</span>
                    <kbd>Alt + C</kbd>
                </div>
            </div>
        </div>

        {{-- About --}}
        <div class="settings-section">
            <div class="settings-section-title">About</div>
            <div class="settings-row" style="cursor:default;">
                <div>
                    <div class="settings-row-label">HAUS Gateway</div>
                    <div class="settings-row-sub">Version 1.0.0 · Laravel 11 + AI</div>
                </div>
            </div>
            @auth
            <div class="settings-row" style="cursor:default;">
                <div>
                    <div class="settings-row-label">Admin panel</div>
                    <div class="settings-row-sub">Kelola users, knowledge base, audit log</div>
                </div>
                <a href="{{ route('admin') }}" class="btn-ghost" style="font-size:13px;padding:6px 12px;" data-admin-settings-link>
                    Buka →
                </a>
            </div>
            @endauth
        </div>
    </div>
</aside>
