{{-- Contact detail drawer (di dalam contacts panel) --}}
<div class="contact-detail" id="contactDetail" hidden>

    {{-- Header --}}
    <div class="contact-detail-head">
        <button class="icon-btn" type="button" id="contactDetailBack" aria-label="Kembali">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </button>
        <div class="cd-head-info">
            <span class="avatar avatar-sm" id="cdAvatar">C</span>
            <div class="cd-head-text">
                <div class="contact-detail-name" id="cdName">-</div>
                <div class="contact-detail-phone" id="cdPhone">-</div>
            </div>
            <div id="cdVipBadge" class="badge-vip" hidden>⭐ VIP</div>
        </div>
        <button class="icon-btn" type="button" id="contactDetailEdit" aria-label="Edit kontak">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 1 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
        <button class="icon-btn icon-btn--danger" type="button" id="contactDetailDelete" aria-label="Hapus kontak">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        </button>
    </div>

    {{-- Filter tabs --}}
    <div class="cd-filter-tabs">
        <button class="cd-filter-tab active" type="button" data-cd-filter="all">All</button>
        <button class="cd-filter-tab" type="button" data-cd-filter="open">Open</button>
        <button class="cd-filter-tab" type="button" data-cd-filter="on_progress">Progress</button>
        <button class="cd-filter-tab" type="button" data-cd-filter="pending">Pending</button>
        <button class="cd-filter-tab" type="button" data-cd-filter="closed">Close</button>
    </div>

    {{-- Ticket list --}}
    <div class="contact-detail-body">
        <div id="cdTickets" class="cd-tickets-list"></div>

        <button class="cd-back-bottom" type="button" id="cdBackBottom">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Kembali ke Daftar Kontak
        </button>
    </div>

    {{-- Footer --}}
    <div class="contact-detail-footer">
        <button class="btn-primary-sm" type="button" id="cdNewChatBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Chat Baru
        </button>
    </div>

</div>
