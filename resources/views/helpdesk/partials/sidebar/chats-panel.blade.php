{{-- Panel: Chats / Tickets --}}
<div class="side-panel" id="panelChats">

    {{-- Sidebar header --}}
    <div class="side-head">
        <span class="side-title">HAUS Gateway</span>
        <div class="side-head-actions">
            <button class="icon-btn" type="button" id="newChatBtn" title="New Chat" aria-label="New Chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    <line x1="12" y1="9" x2="12" y2="15"/><line x1="9" y1="12" x2="15" y2="12"/>
                </svg>
            </button>
            <button class="icon-btn" type="button" id="bulkSelectToggle" title="Select multiple" aria-label="Select multiple">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="5" height="5" rx="1"/><rect x="3" y="10" width="5" height="5" rx="1"/><rect x="3" y="17" width="5" height="5" rx="1"/>
                    <line x1="11" y1="5.5" x2="21" y2="5.5"/><line x1="11" y1="12.5" x2="21" y2="12.5"/><line x1="11" y1="19.5" x2="21" y2="19.5"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="side-search">
        <input type="search" placeholder="Search tiket, nama, nomor..." data-ticket-search aria-label="Search tickets">
    </div>

    {{-- Filter tabs --}}
    <div class="side-tabs" data-status-filters>
        <button class="tab-btn active" type="button" data-filter="all">All</button>
        <button class="tab-btn" type="button" data-filter="open">Open</button>
        <button class="tab-btn" type="button" data-filter="on_progress">Progress</button>
        <button class="tab-btn" type="button" data-filter="pending">Pending</button>
        <button class="tab-btn" type="button" data-filter="closed">Closed</button>
    </div>

    {{-- Stats chips --}}
    <div class="side-stats" data-stats-pills></div>

    {{-- Ticket list --}}
    <div class="side-list" data-ticket-list></div>

    {{-- Bulk action bar --}}
    <div class="bulk-action-bar" id="bulkActionBar" hidden>
        <span class="bulk-count" id="bulkCount">0 dipilih</span>
        <div class="bulk-actions">
            <button class="btn-secondary-sm" type="button" id="bulkArchiveBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                Arsip
            </button>
            <button class="btn-danger-sm" type="button" id="bulkDeleteBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                Hapus
            </button>
            <button class="btn-ghost-sm" type="button" id="bulkCancelBtn">Batal</button>
        </div>
    </div>

</div>
