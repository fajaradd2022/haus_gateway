{{-- 3-dot dropdown menu untuk aksi tiket --}}
<div class="pane-menu" id="paneMenu">
    <button class="icon-btn" type="button" id="paneMenuToggle" title="More options" aria-label="More options">
        <svg viewBox="0 0 24 24" fill="currentColor">
            <circle cx="12" cy="5"  r="1.5"/>
            <circle cx="12" cy="12" r="1.5"/>
            <circle cx="12" cy="19" r="1.5"/>
        </svg>
    </button>
    <div class="pane-menu__dropdown" id="paneMenuDropdown">
        <button class="pane-menu__item" type="button" data-pane-action="assign-me">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Assign ke saya
        </button>
        <button class="pane-menu__item" type="button" data-pane-action="mark-urgent">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Tandai Urgent
        </button>
        <button class="pane-menu__item" type="button" data-pane-action="add-note">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            Tambah Internal Note
        </button>
        <div class="pane-menu__sep"></div>
        <button class="pane-menu__item" type="button" data-pane-action="move-ticket">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            Pindahkan ke Tiket Lain
        </button>
        <button class="pane-menu__item" type="button" data-pane-action="split-to-new-ticket">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M21 3l-7 7"/><path d="M3 3l7 7"/><path d="M16 21h5v-5"/><path d="M21 21l-7-7"/><path d="M3 21l7-7"/><path d="M8 21H3v-5"/></svg>
            Buat Tiket Baru dari Chat Ini
        </button>
        <div class="pane-menu__sep"></div>
        <button class="pane-menu__item" type="button" data-pane-action="copy-id">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Salin ID Tiket
        </button>
        <button class="pane-menu__item" type="button" data-pane-action="view-history">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Riwayat Tiket
        </button>
        <div class="pane-menu__sep"></div>
        <button class="pane-menu__item pane-menu__item--danger" type="button" data-pane-action="close-ticket">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Tutup Tiket
        </button>
        <button class="pane-menu__item" type="button" data-pane-action="archive-chat">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
            Arsip Chat
        </button>
        <button class="pane-menu__item pane-menu__item--danger" type="button" data-pane-action="delete-chat">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
            Hapus Chat
        </button>
    </div>
</div>
