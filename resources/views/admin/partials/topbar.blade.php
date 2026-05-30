{{-- Top bar admin (brand, theme toggle, link workspace, profile, logout) --}}
<header class="admin-topbar">
    <div class="admin-topbar-brand">
        <div>
            <div class="admin-topbar-title">Admin Dashboard</div>
        </div>
    </div>

    <div class="admin-topbar-actions">
        {{-- Theme toggle --}}
        <button class="icon-btn" type="button" id="themeToggle" title="Toggle theme" aria-label="Toggle theme">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        </button>

        <a href="{{ route('workspace') }}" class="btn-ghost">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Workspace
        </a>

        {{-- Profile button + dropdown logout --}}
        <div class="user-menu admin-user-menu" data-user-menu>
            <button class="admin-user-trigger" type="button" data-user-menu-toggle aria-haspopup="menu" aria-expanded="false">
                <span class="avatar avatar-sm" style="background:var(--wa-accent);">{{ strtoupper(substr($adminData['currentUser']['name'], 0, 1)) }}</span>
                <div class="admin-user-trigger__text">
                    <div class="admin-user-trigger__name">{{ $adminData['currentUser']['name'] }}</div>
                    <div class="admin-user-trigger__role">{{ ucfirst($adminData['currentUser']['role']) }}</div>
                </div>
                <svg class="admin-user-trigger__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            <div class="user-menu__dropdown" data-user-menu-dropdown role="menu">
                <div class="user-menu__profile">
                    <div class="user-menu__name">{{ $adminData['currentUser']['name'] }}</div>
                    <div class="user-menu__email">{{ $adminData['currentUser']['email'] ?? ucfirst($adminData['currentUser']['role']) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="user-menu__item user-menu__item--danger" type="submit" role="menuitem">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
