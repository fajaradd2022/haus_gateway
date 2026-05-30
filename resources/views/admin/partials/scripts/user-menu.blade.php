{{-- Dropdown profile di topbar: toggle, klik di luar / Esc / submit untuk menutup --}}
{
    const menu   = document.querySelector('[data-user-menu]');
    const toggle = menu?.querySelector('[data-user-menu-toggle]');

    function setMenuOpen(open) {
        document.body.classList.toggle('user-menu-open', open);
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    toggle?.addEventListener('click', (e) => {
        e.stopPropagation();
        setMenuOpen(!document.body.classList.contains('user-menu-open'));
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-user-menu]')) setMenuOpen(false);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setMenuOpen(false);
    });
}
