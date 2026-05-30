{{-- Composer bar (attach, shortcuts, internal note, textarea, send) --}}
<div class="composer-bar">
    <div class="composer-left">
        {{-- Attach menu --}}
        <div class="attach-menu" data-attach-menu>
            <button class="icon-btn" type="button" data-attach-toggle title="Attach" aria-label="Attach file">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </button>
            <div class="attach-dropdown" data-attach-dropdown>
                <button class="attach-item" type="button" data-attach-type="file">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Attach file
                </button>
                <button class="attach-item" type="button" data-attach-type="image">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Attach image
                </button>
                <button class="attach-item" type="button" data-attach-type="video">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                    Attach video
                </button>
            </div>
        </div>
    </div>

    <div class="composer-center">
        {{-- Shortcuts (populated dynamically by loadQuickChats() in app.js) --}}
        <div class="composer-tools" data-shortcuts></div>

        {{-- Internal note toggle + textarea --}}
        <label class="note-toggle-wrap" for="noteCheckbox" data-note-label>
            <input type="checkbox" id="noteCheckbox" data-note-toggle>
            <span>Internal note</span>
        </label>

        <div class="textarea-wrap">
            <textarea
                class="composer-textarea"
                placeholder="Tulis balasan ke pelanggan..."
                rows="1"
                data-composer
                aria-label="Message input"
            ></textarea>
        </div>
    </div>

    <div class="composer-right">
        <button class="icon-btn icon-btn--send" type="button" data-send aria-label="Send message">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</div>
