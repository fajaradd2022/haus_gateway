{{-- Chat header (avatar, subject, status select, search toggle, 3-dot menu) --}}
<div class="pane-head">
    <div class="pane-head-info">
        <button class="icon-btn mobile-back-btn" type="button" id="backToList" title="Back to list" aria-label="Back to list">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        </button>
        <span class="avatar" data-chat-avatar id="chatAvatar">C</span>
        <div class="pane-head-text">
            <div class="pane-head-name" data-ticket-subject>-</div>
            <div class="pane-head-sub" data-ticket-customer>-</div>
        </div>
    </div>
    <div class="pane-head-actions">
        <div class="status-select-wrap">
            <select data-ticket-status aria-label="Ticket status"></select>
        </div>

        <button class="icon-btn" type="button" id="chatSearchToggle" title="Search in chat" aria-label="Search in chat">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
        </button>

        @include('helpdesk.partials.chat-pane.menu')
    </div>
</div>
