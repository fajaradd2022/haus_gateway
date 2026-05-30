/* ═══════════════════════════════════════════════════════════
   Mini Helpdesk AI Assist — Frontend JS
   WhatsApp Web-style UI with settings, scroll, shortcuts
═══════════════════════════════════════════════════════════ */

// ── State ────────────────────────────────────────────────────────
const state = {
    workspace: window.helpdeskBoot ?? null,
    selectedTicketId: window.helpdeskBoot?.activeTicket?.id ?? null,
    search: '',
    statusFilter: 'all',
    attachMenuOpen: false,
    userMenuOpen: false,
    settingsOpen: false,
    theme: window.localStorage.getItem('helpdesk-theme') ?? 'light',
    isNearBottom: true,
    navPanel: 'chats',
    bulkMode: false,
    bulkSelected: new Set(),
    // contacts
    contacts: [],
    contactsLoaded: false,
    selectedContact: null,
    // new-chat modal
    ncSelectedContact: null,
    // delete chat
    deleteChatTargetId: null,
};

// ── DOM refs ─────────────────────────────────────────────────────
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

const dom = {
    // Sidebar
    ticketList:        $('[data-ticket-list]'),
    ticketSearch:      $('[data-ticket-search]'),
    statsPills:        $('[data-stats-pills]'),
    statusFilters:     $('[data-status-filters]'),
    waSide:            $('#waSide'),

    // Pane / chat
    paneEmpty:         $('#paneEmpty'),
    paneChat:          $('#paneChat'),
    chatAvatar:        $('#chatAvatar'),
    messages:          $('[data-messages]'),
    scrollBtn:         $('#scrollBtn'),
    aiPanel:           $('#aiSidePanel'),
    aiPanelBody:       $('#aiPanelBody'),

    // Ticket header
    subject:           $('[data-ticket-subject]'),
    customer:          $('[data-ticket-customer]'),
    status:            $('[data-ticket-status]'),

    // Composer
    composer:          $('[data-composer]'),
    noteToggle:        $('[data-note-toggle]'),
    noteLabel:         $('[data-note-label]'),
    sendButton:        $('[data-send]'),

    // User menu
    userAvatar:        $('[data-user-avatar]'),
    currentAgentName:  $('[data-current-agent-name]'),
    currentAgentRole:  $('[data-current-agent-role]'),
    userMenu:          $('[data-user-menu]'),
    userMenuDropdown:  $('[data-user-menu-dropdown]'),
    adminLink:         $('[data-admin-link]'),
    adminSettingsLink: $('[data-admin-settings-link]'),

    // Settings panel
    settingsPanel:     $('#settingsPanel'),
    settingsBackdrop:  $('#settingsBackdrop'),
    settingsAvatar:    $('[data-settings-avatar]'),
    settingsName:      $('[data-settings-name]'),
    settingsEmail:     $('[data-settings-email]'),
    settingsRole:      $('[data-settings-role]'),
    closeSettings:     $('#closeSettings'),

    // Mobile back button
    backToList:        $('#backToList'),
};

// ── Lookups ──────────────────────────────────────────────────────
const STATUS_OPTIONS = ['open', 'pending', 'on_progress', 'closed'];

let quickChats = [];

const ATTACH_TEMPLATES = {
    file:  '[File attached]',
    image: '[Image attached]',
    video: '[Video attached]',
};

// ── Utilities ────────────────────────────────────────────────────
const esc = (v = '') =>
    String(v)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');

const fmtTime = (v) =>
    v ? new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(new Date(v)) : '';

const fmtDateTime = (v) =>
    v ? new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' }).format(new Date(v)) : '-';

const fmtDate = (v) =>
    v ? new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(v)) : '';

const sameDay = (a, b) => {
    const da = new Date(a), db = new Date(b);
    return da.getFullYear() === db.getFullYear() && da.getMonth() === db.getMonth() && da.getDate() === db.getDate();
};

const statusLabel = (v) => v.replaceAll('_', ' ');
const statusShort = (v) => ({ open: 'Open', on_progress: 'Progress', pending: 'Pending', closed: 'Closed' }[v] ?? statusLabel(v));

const initials = (name = '') =>
    name.split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]?.toUpperCase() ?? '').join('') || 'U';

const avatarTone = (seed = '') => {
    const tones = ['blue', 'green', 'amber', 'purple', 'rose', 'teal'];
    let sum = 0;
    for (const ch of seed) sum += ch.charCodeAt(0);
    return tones[sum % tones.length];
};

// ── API ──────────────────────────────────────────────────────────
async function api(url, options = {}) {
    const res = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content ?? '',
        },
        ...options,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message ?? 'Request failed');
    return data;
}

// ── Quick Chats (dynamic shortcuts) ─────────────────────────────
async function loadQuickChats() {
    try {
        const data = await api('/api/quick-chats');
        quickChats = data.quick_chats ?? [];
        renderShortcutChips();
    } catch (e) { console.warn('quick-chats load failed', e); }
}

function renderShortcutChips() {
    const container = document.querySelector('[data-shortcuts]');
    if (!container) return;
    container.innerHTML = quickChats.map((qc) =>
        `<button class="shortcut-chip" type="button" data-qc-id="${qc.id}" title="${esc(qc.body)}">${esc(qc.title)}</button>`
    ).join('');
}

// ── Render helpers ───────────────────────────────────────────────
function getActiveTicket() {
    return state.workspace?.activeTicket ?? null;
}

function renderStats() {
    if (!dom.statsPills) return;
    const s = state.workspace?.stats ?? {};
    const chips = [
        s.open      ? `<span class="stat-chip" style="background:rgba(83,189,235,.12);color:#1a7eb8">Open ${s.open}</span>` : '',
        s.on_progress ? `<span class="stat-chip" style="background:rgba(37,211,102,.12);color:#1a8c45">Progress ${s.on_progress}</span>` : '',
        s.pending   ? `<span class="stat-chip" style="background:rgba(252,190,45,.15);color:#b07a00">Pending ${s.pending}</span>` : '',
        s.closed    ? `<span class="stat-chip">Closed ${s.closed}</span>` : '',
    ].filter(Boolean);
    dom.statsPills.innerHTML = chips.join('');
}

function renderFilters() {
    $$('[data-filter]').forEach((btn) =>
        btn.classList.toggle('active', btn.dataset.filter === state.statusFilter)
    );
}

function renderTicketList() {
    if (!dom.ticketList) return;
    const query = state.search.trim().toLowerCase();
    const tickets = (state.workspace?.tickets ?? []).filter((t) => {
        if (state.statusFilter !== 'all' && t.status !== state.statusFilter) return false;
        if (!query) return true;
        return [t.customer_name, t.subject, t.customer_phone].join(' ').toLowerCase().includes(query);
    });

    if (!tickets.length) {
        dom.ticketList.innerHTML = `<div style="padding:32px 16px;text-align:center;color:var(--wa-text-sub);font-size:14px;">Tidak ada tiket ditemukan</div>`;
        return;
    }

    dom.ticketList.innerHTML = tickets.map((t) => {
        const tone = avatarTone(t.customer_name);
        const ini  = initials(t.customer_name);
        const isActive  = t.id === state.selectedTicketId;
        const isChecked = state.bulkSelected.has(t.id);
        const slaHtml     = t.is_sla_risk    ? `<span class="badge badge--risk">⚠ SLA</span>` : '';
        const noNameHtml  = t.needs_subject  ? `<span class="badge badge--noname">Beri Nama</span>` : '';
        const checkboxHtml = state.bulkMode
            ? `<div class="ticket-checkbox-wrap">
                <input type="checkbox" class="ticket-checkbox" data-bulk-id="${t.id}" ${isChecked ? 'checked' : ''} aria-label="Select ticket">
               </div>`
            : '';
        const subjectText = t.needs_subject
            ? `<em style="color:var(--wa-text-sub);font-style:italic;">Belum ada nama tiket</em>`
            : esc(t.subject);
        return `
        <div class="ticket-row-wrap${state.bulkMode ? ' bulk-mode' : ''}">
            ${checkboxHtml}
            <button class="ticket-row${isActive ? ' active' : ''}${t.needs_subject ? ' ticket-row--noname' : ''}" type="button" data-ticket-id="${t.id}">
                <div class="ticket-row__avatar">
                    <span class="avatar avatar--${tone}">${esc(ini)}</span>
                </div>
                <div class="ticket-row__body">
                    <div class="ticket-row__top">
                        <span class="ticket-row__name">${esc(t.customer_name)}</span>
                        <span class="ticket-row__time">${fmtTime(t.last_message_at)}</span>
                    </div>
                    <div class="ticket-row__subject">${subjectText}</div>
                    <div class="ticket-row__preview">${esc(t.last_message_preview ?? '')}</div>
                    <div class="ticket-row__badges">
                        <span class="badge badge--${t.status}">${esc(statusShort(t.status))}</span>
                        ${t.priority ? `<span class="badge badge--${t.priority}">${esc(t.priority)}</span>` : ''}
                        ${slaHtml}${noNameHtml}
                    </div>
                </div>
            </button>
        </div>`;
    }).join('');
}

function renderActiveTicket() {
    const ticket = getActiveTicket();

    if (!ticket) {
        if (dom.paneEmpty) dom.paneEmpty.hidden = false;
        if (dom.paneChat)  dom.paneChat.hidden  = true;
        if (dom.aiPanel)   dom.aiPanel.hidden   = true;
        updateMobileTopbar();
        return;
    }

    if (dom.paneEmpty) dom.paneEmpty.hidden = true;
    if (dom.paneChat)  dom.paneChat.hidden  = false;
    updateMobileTopbar();

    // Header
    const tone = avatarTone(ticket.customer?.name ?? '');
    const ini  = initials(ticket.customer?.name ?? '');
    if (dom.chatAvatar) {
        dom.chatAvatar.textContent = ini;
        dom.chatAvatar.className = `avatar avatar--${tone}`;
    }

    if (ticket.needs_subject) {
        if (dom.subject) {
            dom.subject.innerHTML = `<span class="subject-placeholder">Ketuk untuk beri nama tiket...</span>`;
        }
    } else {
        if (dom.subject) dom.subject.textContent = ticket.subject ?? '-';
    }

    if (dom.customer) dom.customer.textContent = ticket.customer
        ? `${ticket.customer.name} · ${ticket.customer.phone_number}`
        : '-';

    // Status select
    if (dom.status) {
        dom.status.innerHTML = STATUS_OPTIONS.map((opt) =>
            `<option value="${opt}" ${ticket.status === opt ? 'selected' : ''}>${statusLabel(opt)}</option>`
        ).join('');
    }

    // Needs-subject banner inside chat pane
    renderNeedsSubjectBanner(ticket);

    // Messages
    renderMessages(ticket);

    // AI suggestions
    renderSuggestions(ticket.suggestions ?? []);
}

function renderNeedsSubjectBanner(ticket) {
    const existing = document.getElementById('needsSubjectBanner');
    if (existing) existing.remove();
    if (!ticket?.needs_subject) return;

    const banner = document.createElement('div');
    banner.id = 'needsSubjectBanner';
    banner.className = 'needs-subject-banner';
    banner.innerHTML = `
        <div class="needs-subject-banner__inner">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Pesan masuk dari pelanggan baru. Beri nama tiket sebelum membalas.</span>
            <button class="btn-accent-sm" type="button" id="setSubjectBtn">Beri Nama Tiket</button>
        </div>`;

    // Insert after header, before messages
    const paneChat = document.getElementById('paneChat');
    const messages = paneChat?.querySelector('[data-messages]')?.parentElement ?? paneChat?.querySelector('[data-messages]');
    if (paneChat && dom.messages) {
        paneChat.insertBefore(banner, dom.messages);
    }

    document.getElementById('setSubjectBtn')?.addEventListener('click', () => openSubjectModal(ticket));
}

function renderMessages(ticket) {
    if (!dom.messages) return;
    const messages = ticket.messages ?? [];

    if (!messages.length) {
        dom.messages.innerHTML = `
            <div style="text-align:center;padding:32px 0;color:var(--wa-text-sub);font-size:13px;">
                Belum ada pesan dalam tiket ini
            </div>`;
        scrollToBottom(false);
        return;
    }

    let html = '';
    let lastDate = null;

    messages.forEach((msg) => {
        const msgDate = msg.sent_at;

        // Date separator
        if (!lastDate || !sameDay(lastDate, msgDate)) {
            html += `<div class="date-sep"><span>${fmtDate(msgDate)}</span></div>`;
            lastDate = msgDate;
        }

        const isCustomer = msg.sender_type === 'customer';
        const authorName = isCustomer
            ? (ticket.customer?.name ?? 'Customer')
            : (msg.agent_name ?? 'Agent');
        const groupClass = isCustomer ? 'msg-group--in' : 'msg-group--out';
        const msgClass   = isCustomer ? 'message--customer' : 'message--agent';
        const tone       = avatarTone(authorName);
        const ini        = initials(authorName);

        const mediaHtml = msg.media_url
            ? msg.media_type === 'image'
                ? `<img src="${esc(msg.media_url)}" alt="Media" class="message-media" loading="lazy">`
                : `<a href="${esc(msg.media_url)}" target="_blank" rel="noreferrer noopener" style="font-size:13px;color:var(--wa-accent);">📎 Open attachment</a>`
            : '';

        const noteTag = msg.is_internal_note
            ? `<div class="msg-note-tag">🔒 Internal note</div>`
            : '';

        const internalClass = msg.is_internal_note ? ' message--internal' : '';

        html += `
        <div class="msg-group ${groupClass}">
            <div class="msg-wrap">
                <span class="avatar avatar-sm avatar--${tone} msg-avatar">${esc(ini)}</span>
                <div style="position:relative;">
                    <article class="message ${msgClass}${internalClass}">
                        <span class="msg-sender">${esc(authorName)}</span>
                        ${msg.content ? `<p>${esc(msg.content).replaceAll('\n', '<br>')}</p>` : ''}
                        ${mediaHtml}
                        ${noteTag}
                        <div class="msg-meta">
                            <span class="msg-time">${fmtTime(msg.sent_at)}</span>
                        </div>
                    </article>
                </div>
            </div>
        </div>`;
    });

    dom.messages.innerHTML = html;

    // Scroll images load
    $$('img', dom.messages).forEach((img) =>
        img.addEventListener('load', () => { if (state.isNearBottom) scrollToBottom(false); }, { once: true })
    );

    scrollToBottom(false);
}

function renderSuggestions(suggestions) {
    if (!dom.aiPanel) return;

    if (!suggestions.length) {
        dom.aiPanel.hidden = true;
        return;
    }

    dom.aiPanel.hidden = false;
    dom.aiPanelBody.innerHTML = suggestions.map((s) => `
        <button class="suggestion-card" type="button" data-suggestion="${esc(s.text)}">
            <strong>✨ ${esc(s.label)}</strong>
            <p>${esc(s.text)}</p>
            <span class="suggestion-source">${esc(s.source)}</span>
        </button>
    `).join('');
}

function renderCurrentAgent() {
    const agent = state.workspace?.currentAgent;
    if (!agent) return;

    if (dom.currentAgentName) dom.currentAgentName.textContent = agent.name;
    if (dom.currentAgentRole) dom.currentAgentRole.textContent = ucfirst(agent.role);

    const tone = avatarTone(agent.name);
    const ini  = initials(agent.name);
    if (dom.userAvatar) {
        dom.userAvatar.textContent = ini;
        dom.userAvatar.className = `avatar avatar--${tone}`;
    }

    // Settings panel
    if (dom.settingsAvatar) { dom.settingsAvatar.textContent = ini; dom.settingsAvatar.className = `avatar avatar-xl avatar--${tone}`; }
    if (dom.settingsName)   dom.settingsName.textContent  = agent.name;
    if (dom.settingsEmail)  dom.settingsEmail.textContent = agent.email ?? '';
    if (dom.settingsRole)   dom.settingsRole.textContent  = ucfirst(agent.role);

    // Hide admin link for non-admins
    if (dom.adminLink)         dom.adminLink.hidden         = agent.role !== 'admin';
    if (dom.adminSettingsLink) dom.adminSettingsLink.hidden = agent.role !== 'admin';
}

function ucfirst(str = '') { return str.charAt(0).toUpperCase() + str.slice(1); }

function render() {
    renderStats();
    renderFilters();
    renderTicketList();
    renderActiveTicket();
    renderCurrentAgent();
}

function syncWorkspace(workspace) {
    state.workspace  = workspace;
    state.selectedTicketId = workspace?.activeTicket?.id ?? state.selectedTicketId;
    render();
    // Refresh contact ticket list if contact detail is open
    if (state.selectedContact && !document.getElementById('contactDetail')?.hidden) {
        refreshContactTickets();
    }
}

async function refreshContactTickets() {
    if (!state.selectedContact) return;
    try {
        const data = await api(`/api/contacts/${state.selectedContact.id}`);
        state.cdAllTickets = data.tickets ?? [];
        renderCdTickets(state.cdAllTickets);
    } catch (err) { console.error(err); }
}

function closeContactDetail() {
    const detail = document.getElementById('contactDetail');
    const list = document.getElementById('contactsList');
    if (detail) detail.hidden = true;
    if (list) list.hidden = false;
    state.selectedContact = null;
}

async function openTicketById(id, options = {}) {
    const { switchToChats = false, closeContact = false } = options;
    const data = await api(`/api/tickets/${id}`);
    state.workspace.activeTicket = data.ticket;
    state.selectedTicketId = data.ticket.id;

    if (closeContact) closeContactDetail();
    if (switchToChats) switchPanel('chats');

    closeSidebar();
    render();
}

// ── Scroll ───────────────────────────────────────────────────────
function scrollToBottom(smooth = true) {
    if (!dom.messages) return;
    requestAnimationFrame(() => {
        dom.messages.scrollTo({
            top: dom.messages.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant',
        });
    });
}

function updateScrollBtn() {
    if (!dom.messages || !dom.scrollBtn) return;
    const { scrollTop, scrollHeight, clientHeight } = dom.messages;
    const nearBottom = scrollHeight - scrollTop - clientHeight < 120;
    state.isNearBottom = nearBottom;
    dom.scrollBtn.classList.toggle('visible', !nearBottom);
}

// ── Theme ────────────────────────────────────────────────────────
function applyTheme(theme) {
    state.theme = theme;
    document.documentElement.dataset.theme = theme;
    window.localStorage.setItem('helpdesk-theme', theme);

    // Sync theme buttons in settings
    $$('[data-theme-set]').forEach((btn) =>
        btn.classList.toggle('active', btn.dataset.themeSet === theme)
    );
}

// ── Settings panel ───────────────────────────────────────────────
function openSettings() {
    state.settingsOpen = true;
    dom.settingsPanel?.classList.add('open');
    dom.settingsBackdrop?.classList.add('visible');
    setUserMenuOpen(false);
}

function closeSettings() {
    state.settingsOpen = false;
    dom.settingsPanel?.classList.remove('open');
    dom.settingsBackdrop?.classList.remove('visible');
}

// ── User / attach menus ──────────────────────────────────────────
function setUserMenuOpen(open) {
    state.userMenuOpen = open;
    document.body.classList.toggle('user-menu-open', open);
}

function setAttachMenuOpen(open) {
    state.attachMenuOpen = open;
    document.body.classList.toggle('attach-open', open);
}

// ── Composer ─────────────────────────────────────────────────────
function insertText(text) {
    if (!dom.composer) return;
    const cur = dom.composer.value.trim();
    dom.composer.value = cur ? `${cur} ${text}` : text;
    dom.composer.focus();
    autoResizeTextarea();
}

function autoResizeTextarea() {
    if (!dom.composer) return;
    dom.composer.style.height = 'auto';
    dom.composer.style.height = Math.min(dom.composer.scrollHeight, 120) + 'px';
}

// ── Mobile sidebar ───────────────────────────────────────────────
function openSidebar() {
    dom.waSide?.classList.add('open');
    document.getElementById('mobileTopbar')?.style.setProperty('display', 'none');
}
function closeSidebar() {
    dom.waSide?.classList.remove('open');
    updateMobileTopbar();
}

function updateMobileTopbar() {
    const topbar   = document.getElementById('mobileTopbar');
    const isMobile = window.innerWidth <= 900;
    const chatOpen = !document.getElementById('paneChat')?.hidden;
    if (topbar) topbar.style.display = (isMobile && !chatOpen) ? 'flex' : 'none';
}

document.getElementById('openSidebarBtn')?.addEventListener('click', openSidebar);

// ── Click delegation ─────────────────────────────────────────────
document.addEventListener('click', async (e) => {

    // Ticket row
    const ticketBtn = e.target.closest('[data-ticket-id]');
    if (ticketBtn) {
        const id = Number(ticketBtn.dataset.ticketId);
        try {
            const fromContactDetail = Boolean(ticketBtn.closest('#contactDetail'));
            await openTicketById(id, {
                switchToChats: fromContactDetail,
                closeContact: fromContactDetail,
            });
        } catch (err) { console.error(err); }
        return;
    }

    // AI suggestion
    const suggBtn = e.target.closest('[data-suggestion]');
    if (suggBtn) {
        if (dom.composer) { dom.composer.value = suggBtn.dataset.suggestion; dom.composer.focus(); autoResizeTextarea(); }
        return;
    }

    // Status filter
    const filterBtn = e.target.closest('[data-filter]');
    if (filterBtn) {
        state.statusFilter = filterBtn.dataset.filter;
        renderTicketList();
        renderFilters();
        return;
    }

    // Shortcut chip (dynamic quick chats or legacy static)
    const shortcutBtn = e.target.closest('[data-shortcut],[data-qc-id]');
    if (shortcutBtn) {
        if (shortcutBtn.dataset.qcId) {
            const qc = quickChats.find((q) => String(q.id) === shortcutBtn.dataset.qcId);
            if (qc) insertText(qc.body);
        } else {
            insertText(shortcutBtn.dataset.shortcut);
        }
        return;
    }

    // Attach type
    const attachTypeBtn = e.target.closest('[data-attach-type]');
    if (attachTypeBtn) {
        insertText(ATTACH_TEMPLATES[attachTypeBtn.dataset.attachType] ?? '[Attachment]');
        setAttachMenuOpen(false);
        return;
    }

    // Attach toggle
    if (e.target.closest('[data-attach-toggle]')) {
        setAttachMenuOpen(!state.attachMenuOpen);
        return;
    }

    // User menu toggle
    if (e.target.closest('[data-user-menu-toggle]')) {
        setUserMenuOpen(!state.userMenuOpen);
        return;
    }

    // Settings open
    if (e.target.closest('[data-settings-action]')) {
        openSettings();
        return;
    }

    // Close settings via backdrop
    if (e.target === dom.settingsBackdrop) {
        closeSettings();
        return;
    }

    // Theme buttons in settings
    const themeBtn = e.target.closest('[data-theme-set]');
    if (themeBtn) {
        applyTheme(themeBtn.dataset.themeSet);
        return;
    }

    // Theme toggle button (sidebar)
    if (e.target.closest('[data-theme-toggle]')) {
        applyTheme(state.theme === 'light' ? 'dark' : 'light');
        return;
    }

    // Scroll to bottom button
    if (e.target.closest('#scrollBtn')) {
        scrollToBottom(true);
        return;
    }

    // Back to list (mobile)
    if (e.target.closest('#backToList')) {
        openSidebar();
        return;
    }

    // Pane 3-dot menu toggle
    if (e.target.closest('#paneMenuToggle')) {
        const menu = document.getElementById('paneMenu');
        menu?.classList.toggle('pane-menu-open');
        return;
    }

    // Pane 3-dot menu actions
    const paneActionBtn = e.target.closest('[data-pane-action]');
    if (paneActionBtn) {
        document.getElementById('paneMenu')?.classList.remove('pane-menu-open');
        handlePaneAction(paneActionBtn.dataset.paneAction);
        return;
    }

    // Chat search toggle
    if (e.target.closest('#chatSearchToggle')) {
        toggleChatSearch();
        return;
    }

    // Close chat search
    if (e.target.closest('#chatSearchClose')) {
        closeChatSearch();
        return;
    }

    // Close menus when clicking outside
    if (!e.target.closest('[data-attach-menu]')) setAttachMenuOpen(false);
    if (!e.target.closest('[data-user-menu]'))   setUserMenuOpen(false);
    if (!e.target.closest('#paneMenu'))          document.getElementById('paneMenu')?.classList.remove('pane-menu-open');
});

// Close settings button
dom.closeSettings?.addEventListener('click', closeSettings);

// ── Search ───────────────────────────────────────────────────────
dom.ticketSearch?.addEventListener('input', (e) => {
    state.search = e.target.value;
    renderTicketList();
});

// Ctrl+/ focus search
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
        e.preventDefault();
        dom.ticketSearch?.focus();
    }
    // Alt+N toggle internal note
    if (e.altKey && e.key === 'n') {
        e.preventDefault();
        if (dom.noteToggle) {
            dom.noteToggle.checked = !dom.noteToggle.checked;
            dom.noteLabel?.classList.toggle('active', dom.noteToggle.checked);
            dom.composer?.setAttribute('placeholder', dom.noteToggle.checked
                ? 'Tulis catatan internal...'
                : 'Tulis balasan ke pelanggan...');
        }
    }
    // Escape: close settings or menus
    if (e.key === 'Escape') {
        if (state.settingsOpen) { closeSettings(); return; }
        setUserMenuOpen(false);
        setAttachMenuOpen(false);
    }
    // Alt+C close ticket
    if (e.altKey && e.key === 'c') {
        e.preventDefault();
        const ticket = getActiveTicket();
        if (ticket && ticket.status !== 'closed') {
            dom.status?.dispatchEvent(Object.assign(new Event('change'), { target: Object.assign(dom.status, { value: 'closed' }) }));
        }
    }
});

// ── Status change ────────────────────────────────────────────────
dom.status?.addEventListener('change', async (e) => {
    const ticket = getActiveTicket();
    if (!ticket) return;
    try {
        const data = await api(`/api/tickets/${ticket.id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status: e.target.value }),
        });
        syncWorkspace(data.workspace);
    } catch (err) { console.error(err); }
});

// ── Send message ─────────────────────────────────────────────────
async function sendMessage() {
    const ticket  = getActiveTicket();
    const content = dom.composer?.value?.trim() ?? '';
    if (!ticket || !content) return;

    dom.sendButton.disabled = true;
    dom.sendButton.style.opacity = '0.6';

    try {
        const data = await api(`/api/tickets/${ticket.id}/messages`, {
            method: 'POST',
            body: JSON.stringify({
                content,
                is_internal_note: Boolean(dom.noteToggle?.checked),
            }),
        });
        dom.composer.value = '';
        autoResizeTextarea();
        if (dom.noteToggle) { dom.noteToggle.checked = false; dom.noteLabel?.classList.remove('active'); }
        dom.composer.setAttribute('placeholder', 'Tulis balasan ke pelanggan...');
        syncWorkspace(data.workspace);
        dom.composer.focus();
    } catch (err) {
        alert(err.message);
    } finally {
        dom.sendButton.disabled = false;
        dom.sendButton.style.opacity = '';
    }
}

dom.sendButton?.addEventListener('click', sendMessage);

// Enter to send (Shift+Enter = new line)
dom.composer?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Note toggle
dom.noteToggle?.addEventListener('change', () => {
    const checked = dom.noteToggle.checked;
    dom.noteLabel?.classList.toggle('active', checked);
    dom.composer?.setAttribute('placeholder', checked ? 'Tulis catatan internal...' : 'Tulis balasan ke pelanggan...');
    dom.composer?.focus();
});

// ── Composer auto-resize ─────────────────────────────────────────
dom.composer?.addEventListener('input', autoResizeTextarea);

// Emoji (simple)
$('[data-insert-emoji]')?.addEventListener('click', () => insertText('😊'));

// ── Scroll monitoring ─────────────────────────────────────────────
dom.messages?.addEventListener('scroll', updateScrollBtn, { passive: true });

// ── Mobile: show back button when chat is open ────────────────────
function checkMobile() {
    updateMobileTopbar();
}

window.addEventListener('resize', checkMobile);
checkMobile();

// ── In-chat search ────────────────────────────────────────────────
const chatSearchInput = document.getElementById('chatSearchInput');
const chatSearchCount = document.getElementById('chatSearchCount');
const paneSearch      = document.getElementById('paneSearch');

let chatSearchActive = false;

function toggleChatSearch() {
    chatSearchActive = !chatSearchActive;
    paneSearch?.classList.toggle('visible', chatSearchActive);
    if (chatSearchActive) {
        chatSearchInput?.focus();
    } else {
        clearChatHighlights();
        if (chatSearchInput) chatSearchInput.value = '';
        if (chatSearchCount) chatSearchCount.textContent = '';
    }
}

function closeChatSearch() {
    chatSearchActive = false;
    paneSearch?.classList.remove('visible');
    clearChatHighlights();
    if (chatSearchInput) chatSearchInput.value = '';
    if (chatSearchCount) chatSearchCount.textContent = '';
}

function clearChatHighlights() {
    dom.messages?.querySelectorAll('.msg-highlight').forEach((el) => {
        el.outerHTML = el.textContent;
    });
}

function highlightChatSearch(query) {
    if (!dom.messages) return;

    // Rebuild messages from current ticket to reset highlights
    const ticket = getActiveTicket();
    if (ticket) renderMessages(ticket);

    if (!query.trim()) {
        if (chatSearchCount) chatSearchCount.textContent = '';
        return;
    }

    const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const re = new RegExp(`(${escaped})`, 'gi');
    let count = 0;

    dom.messages?.querySelectorAll('.message p, .message .msg-sender').forEach((el) => {
        if (el.innerHTML.match(re)) {
            el.innerHTML = el.innerHTML.replace(re, '<mark class="msg-highlight">$1</mark>');
            count += (el.innerHTML.match(re) || []).length;
        }
    });

    if (chatSearchCount) {
        chatSearchCount.textContent = count > 0 ? `${count} hasil` : 'Tidak ditemukan';
    }

    // Scroll to first match
    const first = dom.messages?.querySelector('.msg-highlight');
    first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

chatSearchInput?.addEventListener('input', (e) => {
    highlightChatSearch(e.target.value);
});

chatSearchInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeChatSearch();
});

// ── Subject modal ─────────────────────────────────────────────────
function openSubjectModal(ticket) {
    document.getElementById('smTicketId').value  = ticket.id;
    document.getElementById('smSubjectInput').value = '';
    openModal('subjectModal');
    setTimeout(() => document.getElementById('smSubjectInput')?.focus(), 80);
}

document.getElementById('smSaveBtn')?.addEventListener('click', async () => {
    const ticketId = document.getElementById('smTicketId').value;
    const subject  = document.getElementById('smSubjectInput').value.trim();
    if (!subject) { showToast('Nama tiket tidak boleh kosong', 'error'); return; }

    const btn = document.getElementById('smSaveBtn');
    btn.disabled = true;
    try {
        const data = await api(`/api/tickets/${ticketId}/subject`, {
            method: 'PATCH',
            body: JSON.stringify({ subject }),
        });
        closeModal('subjectModal');
        syncWorkspace(data.workspace);
        showToast('Nama tiket berhasil disimpan');
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; }
});

// Also allow clicking subject placeholder in header to open modal
document.addEventListener('click', (e) => {
    if (e.target.closest('.subject-placeholder')) {
        const ticket = getActiveTicket();
        if (ticket?.needs_subject) openSubjectModal(ticket);
    }
});

// ── Move ticket modal ─────────────────────────────────────────────
let moveMessageIds = [];

function openMoveTicketModal() {
    const ticket = getActiveTicket();
    if (!ticket) return;

    moveMessageIds = [];
    document.getElementById('mtSourceId').value = ticket.id;

    // Populate checkboxes for messages
    const msgs = ticket.messages ?? [];
    const list  = document.getElementById('mtMessageList');
    if (list) {
        list.innerHTML = msgs.length
            ? msgs.map((m) => {
                const label = m.sender_type === 'customer'
                    ? `[Pelanggan] ${m.content ? m.content.slice(0, 60) : '📎 Media'}`
                    : `[${m.agent_name ?? 'Agent'}] ${m.content ? m.content.slice(0, 60) : '📎 Media'}`;
                return `<label class="mt-msg-item">
                    <input type="checkbox" class="mt-msg-cb" value="${m.id}">
                    <span class="mt-msg-label">${esc(label)}</span>
                    <span class="mt-msg-time">${fmtTime(m.sent_at)}</span>
                </label>`;
            }).join('')
            : '<p style="color:var(--wa-text-sub);font-size:13px;padding:8px 0;">Tidak ada pesan</p>';
    }

    // Populate target ticket dropdown
    const select = document.getElementById('mtTargetTicket');
    if (select) {
        const allTickets = (state.workspace?.tickets ?? []).filter((t) => t.id !== ticket.id);
        select.innerHTML = `<option value="">-- Pilih tiket tujuan --</option>` +
            allTickets.map((t) => `<option value="${t.id}">${esc(t.subject || `Tiket #${t.id}`)} (${t.customer_name})</option>`).join('');
    }

    openModal('moveTicketModal');
}

document.getElementById('mtConfirmBtn')?.addEventListener('click', async () => {
    const sourceId  = document.getElementById('mtSourceId').value;
    const targetId  = document.getElementById('mtTargetTicket').value;
    const checked   = [...document.querySelectorAll('.mt-msg-cb:checked')].map((cb) => Number(cb.value));

    if (!targetId)      { showToast('Pilih tiket tujuan', 'error'); return; }
    if (!checked.length){ showToast('Pilih minimal satu pesan', 'error'); return; }

    const btn = document.getElementById('mtConfirmBtn');
    btn.disabled = true;
    try {
        const data = await api(`/api/tickets/${sourceId}/move-messages`, {
            method: 'POST',
            body: JSON.stringify({ target_ticket_id: Number(targetId), message_ids: checked }),
        });
        closeModal('moveTicketModal');
        syncWorkspace(data.workspace);
        showToast('Pesan berhasil dipindahkan');
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; }
});

// ── Split ticket modal ────────────────────────────────────────────
function openSplitTicketModal() {
    const ticket = getActiveTicket();
    if (!ticket) return;

    document.getElementById('stSourceId').value    = ticket.id;
    document.getElementById('stSubjectInput').value = '';

    const msgs = ticket.messages ?? [];
    const list  = document.getElementById('stMessageList');
    if (list) {
        list.innerHTML = msgs.length
            ? msgs.map((m) => {
                const label = m.sender_type === 'customer'
                    ? `[Pelanggan] ${m.content ? m.content.slice(0, 60) : '📎 Media'}`
                    : `[${m.agent_name ?? 'Agent'}] ${m.content ? m.content.slice(0, 60) : '📎 Media'}`;
                return `<label class="mt-msg-item">
                    <input type="checkbox" class="st-msg-cb" value="${m.id}">
                    <span class="mt-msg-label">${esc(label)}</span>
                    <span class="mt-msg-time">${fmtTime(m.sent_at)}</span>
                </label>`;
            }).join('')
            : '<p style="color:var(--wa-text-sub);font-size:13px;padding:8px 0;">Tidak ada pesan</p>';
    }

    openModal('splitTicketModal');
    setTimeout(() => document.getElementById('stSubjectInput')?.focus(), 80);
}

document.getElementById('stConfirmBtn')?.addEventListener('click', async () => {
    const sourceId = document.getElementById('stSourceId').value;
    const subject  = document.getElementById('stSubjectInput').value.trim();
    const checked  = [...document.querySelectorAll('.st-msg-cb:checked')].map((cb) => Number(cb.value));

    if (!subject)        { showToast('Nama tiket baru tidak boleh kosong', 'error'); return; }
    if (!checked.length) { showToast('Pilih minimal satu pesan', 'error'); return; }

    const btn = document.getElementById('stConfirmBtn');
    btn.disabled = true;
    try {
        const data = await api(`/api/tickets/${sourceId}/split`, {
            method: 'POST',
            body: JSON.stringify({ subject, message_ids: checked }),
        });
        closeModal('splitTicketModal');
        syncWorkspace(data.workspace);
        showToast('Tiket baru berhasil dibuat');
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; }
});

// ── Pane 3-dot actions ────────────────────────────────────────────
async function handlePaneAction(action) {
    const ticket = getActiveTicket();

    switch (action) {
        case 'assign-me': {
            if (!ticket) return;
            try {
                const data = await api(`/api/tickets/${ticket.id}/status`, {
                    method: 'PATCH',
                    body: JSON.stringify({ status: ticket.status }),
                });
                syncWorkspace(data.workspace);
                showToast('Tiket berhasil di-assign ke Anda');
            } catch (err) { showToast(err.message, 'error'); }
            break;
        }
        case 'mark-urgent': {
            if (!ticket) return;
            showToast('Tandai urgent — fitur segera tersedia');
            break;
        }
        case 'add-note': {
            if (dom.noteToggle) {
                dom.noteToggle.checked = true;
                dom.noteLabel?.classList.add('active');
                dom.composer?.setAttribute('placeholder', 'Tulis catatan internal...');
            }
            dom.composer?.focus();
            showToast('Mode internal note aktif');
            break;
        }
        case 'copy-id': {
            if (!ticket) return;
            navigator.clipboard?.writeText(`#${ticket.id}`).then(() => showToast(`ID Tiket #${ticket.id} disalin`));
            break;
        }
        case 'view-history': {
            showToast('Riwayat tiket — fitur segera tersedia');
            break;
        }
        case 'close-ticket': {
            if (!ticket) return;
            if (!confirm(`Tutup tiket #${ticket.id} "${ticket.subject}"?`)) return;
            try {
                const data = await api(`/api/tickets/${ticket.id}/status`, {
                    method: 'PATCH',
                    body: JSON.stringify({ status: 'closed' }),
                });
                syncWorkspace(data.workspace);
                showToast('Tiket ditutup');
            } catch (err) { showToast(err.message, 'error'); }
            break;
        }
        case 'move-ticket': {
            if (!ticket) return;
            openMoveTicketModal();
            break;
        }
        case 'split-to-new-ticket': {
            if (!ticket) return;
            openSplitTicketModal();
            break;
        }
        case 'archive-chat': {
            if (!ticket) return;
            openDeleteChatModal(ticket.id, ticket.subject, 'archive');
            break;
        }
        case 'delete-chat': {
            if (!ticket) return;
            openDeleteChatModal(ticket.id, ticket.subject, 'delete');
            break;
        }
    }
}

// ── Toast notification ─────────────────────────────────────────────
let toastTimer = null;

function showToast(message, type = 'info') {
    let toast = document.getElementById('wa-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'wa-toast';
        toast.style.cssText = `
            position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(8px);
            background:${type === 'error' ? '#f15c5c' : 'var(--wa-text)'};
            color:#fff;padding:10px 20px;border-radius:999px;font-size:13px;font-weight:500;
            z-index:9999;opacity:0;transition:opacity 200ms,transform 200ms;
            pointer-events:none;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,0.25);
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.background = type === 'error' ? '#f15c5c' : 'var(--wa-text)';
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(8px)';
    }, 2800);
}

// ── Nav rail ──────────────────────────────────────────────────────
function switchPanel(panel) {
    state.navPanel = panel;

    const chatsEl    = document.getElementById('panelChats');
    const contactsEl = document.getElementById('panelContacts');
    const archiveEl  = document.getElementById('panelArchive');

    // Hide all first, then show the active one
    chatsEl.hidden    = true;
    contactsEl.hidden = true;
    if (archiveEl) archiveEl.hidden = true;

    chatsEl.classList.remove('slide-out', 'slide-in');
    contactsEl.classList.remove('slide-out', 'slide-in');

    if (panel === 'contacts') {
        chatsEl.hidden = false;
        contactsEl.hidden = false;
        chatsEl.classList.add('slide-out');
        contactsEl.classList.add('slide-in');
    } else if (panel === 'archive') {
        if (archiveEl) archiveEl.hidden = false;
    } else {
        chatsEl.hidden = false;
    }

    $$('[data-nav]').forEach((btn) => btn.classList.toggle('active', btn.dataset.nav === panel));

    if (panel === 'contacts' && !state.contactsLoaded) loadContacts();
    if (panel === 'archive') loadArchive();
}

document.addEventListener('click', (e) => {
    const navBtn = e.target.closest('[data-nav]');
    if (navBtn) { switchPanel(navBtn.dataset.nav); return; }
});

// ── Contacts panel ────────────────────────────────────────────────
async function loadContacts(q = '') {
    const list = document.getElementById('contactsList');
    if (!list) return;
    try {
        const data = await api(`/api/contacts?q=${encodeURIComponent(q)}`);
        state.contacts = data.contacts ?? [];
        state.contactsLoaded = true;
        renderContactsList(state.contacts);
    } catch (err) { console.error(err); }
}

function renderContactsList(contacts) {
    const list = document.getElementById('contactsList');
    if (!list) return;
    if (!contacts.length) {
        list.innerHTML = `<div style="padding:32px 16px;text-align:center;color:var(--wa-text-sub);font-size:13px;">Tidak ada kontak ditemukan</div>`;
        return;
    }
    list.innerHTML = contacts.map((c) => {
        const tone = avatarTone(c.name);
        const ini  = initials(c.name);
        const vipBadge = c.is_vip ? ' <span class="badge-vip-sm">VIP</span>' : '';
        const company = c.company ? ` · ${esc(c.company)}` : '';

        return `
        <div class="contact-row-wrap">
            <button class="contact-row" type="button" data-contact-id="${c.id}">
                <span class="avatar avatar-sm avatar--${tone}">${esc(ini)}</span>
                <div class="contact-row__body">
                    <div class="contact-row__name">${esc(c.name)}${vipBadge}</div>
                    <div class="contact-row__sub">${esc(c.phone_number)}${company}</div>
                </div>
            </button>
        </div>`;
    }).join('');
}

document.getElementById('contactSearchInput')?.addEventListener('input', (e) => {
    loadContacts(e.target.value);
});

// Contact row click → show detail
document.addEventListener('click', async (e) => {
    const contactBtn = e.target.closest('[data-contact-id]');
    if (!contactBtn) return;
    const id = contactBtn.dataset.contactId;
    try {
        const data = await api(`/api/contacts/${id}`);
        showContactDetail(data.contact, data.tickets ?? []);
    } catch (err) { console.error(err); }
});

function showContactDetail(contact, tickets) {
    state.selectedContact = contact;
    state.cdAllTickets    = tickets;
    state.cdActiveFilter  = 'all';

    const detail = document.getElementById('contactDetail');
    const list   = document.getElementById('contactsList');
    if (!detail || !list) return;

    const tone = avatarTone(contact.name);
    const ini  = initials(contact.name);
    const cdAvatar = document.getElementById('cdAvatar');
    cdAvatar.textContent = ini;
    cdAvatar.className   = `avatar avatar-sm avatar--${tone}`;
    document.getElementById('cdName').textContent  = contact.name;
    document.getElementById('cdPhone').textContent = contact.phone_number;
    const vipBadge = document.getElementById('cdVipBadge');
    if (vipBadge) vipBadge.hidden = !contact.is_vip;

    // Reset filter tabs
    document.querySelectorAll('[data-cd-filter]').forEach((btn) => {
        btn.classList.toggle('active', btn.dataset.cdFilter === 'all');
    });

    renderCdTickets(tickets);

    list.hidden   = true;
    detail.hidden = false;
}

function renderCdTickets(tickets) {
    const filter = state.cdActiveFilter ?? 'all';
    const filtered = filter === 'all' ? tickets : tickets.filter((t) => (t.status ?? '') === filter);
    const cdTickets = document.getElementById('cdTickets');
    if (!cdTickets) return;
    cdTickets.innerHTML = filtered.length
        ? filtered.map((t) => {
            const isActive = t.id === state.selectedTicketId;
            return `
            <div class="cd-ticket-item-wrap${isActive ? ' cd-ticket-item-wrap--active' : ''}">
                <button class="cd-ticket-item" type="button" data-cd-ticket-id="${t.id}">
                    <div class="cd-ticket-item-top">
                        <span class="cd-ticket-item-subject">${esc(t.subject)}</span>
                        <span class="badge badge--${t.status}">${esc(statusShort(t.status))}</span>
                    </div>
                    <div class="cd-ticket-item-sub">
                        <span class="cd-ticket-item-channel">${esc(t.channel ?? '')}</span>
                        <span class="cd-ticket-item-time">${fmtDateTime(t.last_message_at)}</span>
                    </div>
                </button>
                <button class="cd-ticket-delete-btn" type="button" data-cd-delete-ticket="${t.id}" data-cd-delete-subject="${esc(t.subject)}" title="Hapus tiket">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
            </div>`;
        }).join('')
        : `<div class="cd-tickets-empty">Tidak ada tiket</div>`;
}

document.addEventListener('click', async (e) => {
    const filterBtn = e.target.closest('[data-cd-filter]');
    if (filterBtn) {
        document.querySelectorAll('[data-cd-filter]').forEach((b) => b.classList.remove('active'));
        filterBtn.classList.add('active');
        state.cdActiveFilter = filterBtn.dataset.cdFilter;
        renderCdTickets(state.cdAllTickets ?? []);
        return;
    }

    const ticketBtn = e.target.closest('[data-cd-ticket-id]');
    if (ticketBtn) {
        try {
            const id = Number(ticketBtn.dataset.cdTicketId);
            const data = await api(`/api/tickets/${id}`);
            state.workspace.activeTicket = data.ticket;
            state.selectedTicketId = data.ticket.id;
            renderCdTickets(state.cdAllTickets ?? []);
            if (dom.paneEmpty) dom.paneEmpty.hidden = true;
            if (dom.paneChat)  dom.paneChat.hidden  = false;
            renderActiveTicket();
            if (window.innerWidth <= 900) closeSidebar();
        } catch (err) { showToast('Gagal membuka tiket', 'error'); console.error(err); }
        return;
    }

    // Delete ticket from contact detail
    const cdDeleteTicketBtn = e.target.closest('[data-cd-delete-ticket]');
    if (cdDeleteTicketBtn) {
        const id      = Number(cdDeleteTicketBtn.dataset.cdDeleteTicket);
        const subject = cdDeleteTicketBtn.dataset.cdDeleteSubject;
        openDeleteChatModal(id, subject, 'delete');
        // After deletion, refresh contact tickets via patching deleteChatConfirm callback
        state._cdDeleteSource = 'contact';
        return;
    }
});

document.getElementById('contactDetailBack')?.addEventListener('click', () => {
    closeContactDetail();
});

document.getElementById('contactDetailEdit')?.addEventListener('click', () => {
    if (state.selectedContact) openContactModal(state.selectedContact);
});

document.getElementById('contactDetailDelete')?.addEventListener('click', () => {
    const c = state.selectedContact;
    if (!c) return;
    document.getElementById('deleteContactDesc').textContent =
        `Kontak "${c.name}" beserta seluruh tiket dan riwayat percakapannya akan dihapus permanen.`;
    openModal('deleteContactModal');
});

document.getElementById('deleteContactConfirm')?.addEventListener('click', async () => {
    const c = state.selectedContact;
    if (!c) return;
    const btn = document.getElementById('deleteContactConfirm');
    btn.disabled = true;
    try {
        await api(`/api/contacts/${c.id}`, { method: 'DELETE' });
        closeModal('deleteContactModal');
        closeContactDetail();
        state.contactsLoaded = false;
        loadContacts();
        // Clear active ticket if it belonged to this contact
        if (state.workspace?.activeTicket?.customer_id === c.id) {
            state.workspace.activeTicket = null;
            state.selectedTicketId = null;
            render();
        }
        showToast(`Kontak "${c.name}" dihapus`);
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; }
});

document.getElementById('cdBackBottom')?.addEventListener('click', () => {
    closeContactDetail();
});

document.getElementById('cdNewChatBtn')?.addEventListener('click', () => {
    if (state.selectedContact) openNewChatModalWithContact(state.selectedContact);
});

// ── Archive panel ─────────────────────────────────────────────────
let archiveSearchTimer = null;

async function loadArchive(q = '') {
    const list = document.getElementById('archiveList');
    if (!list) return;
    try {
        const data = await api(`/api/tickets/archived?q=${encodeURIComponent(q)}`);
        renderArchiveList(data.tickets ?? []);
    } catch (err) { console.error(err); }
}

function renderArchiveList(tickets) {
    const list = document.getElementById('archiveList');
    if (!list) return;
    if (!tickets.length) {
        list.innerHTML = `<div style="padding:48px 16px;text-align:center;color:var(--wa-text-sub);font-size:13px;">Tidak ada tiket diarsip</div>`;
        return;
    }
    list.innerHTML = tickets.map((t) => {
        const tone = avatarTone(t.customer_name ?? '');
        const ini  = initials(t.customer_name ?? '?');
        return `
        <div class="archive-row">
            <span class="avatar avatar-sm avatar--${tone}">${esc(ini)}</span>
            <div class="archive-row__body">
                <div class="archive-row__name">${esc(t.customer_name ?? '-')}</div>
                <div class="archive-row__subject">${esc(t.subject)}</div>
                <div class="archive-row__meta">
                    <span class="badge badge--${t.status}">${esc(statusShort(t.status))}</span>
                    <span class="archive-row__date">${fmtDateTime(t.archived_at)}</span>
                </div>
            </div>
            <div class="archive-row__actions">
                <button class="archive-action-btn" type="button" data-unarchive="${t.id}" title="Kembalikan ke aktif">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                </button>
                <button class="archive-action-btn archive-action-btn--danger" type="button" data-archive-delete="${t.id}" data-archive-subject="${esc(t.subject)}" title="Hapus permanen">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
            </div>
        </div>`;
    }).join('');
}

document.getElementById('archiveSearchInput')?.addEventListener('input', (e) => {
    clearTimeout(archiveSearchTimer);
    archiveSearchTimer = setTimeout(() => loadArchive(e.target.value), 300);
});

document.addEventListener('click', async (e) => {
    // Unarchive — use workspace from response directly, then open the ticket in chats
    const unarchiveBtn = e.target.closest('[data-unarchive]');
    if (unarchiveBtn) {
        const id = unarchiveBtn.dataset.unarchive;
        unarchiveBtn.disabled = true;
        try {
            const res = await api(`/api/tickets/${id}/unarchive`, { method: 'PATCH' });
            syncWorkspace(res.workspace);
            loadArchive(document.getElementById('archiveSearchInput')?.value ?? '');
            // Open the restored ticket and switch to chats panel
            await openTicketById(Number(id), { switchToChats: true });
            showToast('Tiket dikembalikan ke aktif');
        } catch (err) { showToast('Gagal mengembalikan tiket', 'error'); }
        finally { unarchiveBtn.disabled = false; }
        return;
    }

    // Delete from archive
    const archiveDeleteBtn = e.target.closest('[data-archive-delete]');
    if (archiveDeleteBtn) {
        const id      = archiveDeleteBtn.dataset.archiveDelete;
        const subject = archiveDeleteBtn.dataset.archiveSubject;
        state.deleteChatTargetId = Number(id);
        state._archiveDeleteSource = true;
        document.getElementById('deleteChatDesc').textContent = `Tiket "${subject}" akan dihapus permanen.`;
        document.getElementById('dcArchive')?.classList.remove('selected');
        document.getElementById('dcDelete')?.classList.add('selected');
        document.getElementById('dcDelete')?.querySelector('input')?.click();
        openModal('deleteChatModal');
        return;
    }
});

// ── New Chat modal ────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) { closeModal(closeBtn.dataset.modalClose); return; }

    // Click outside modal closes it
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.remove('open');
        return;
    }
});

// New Chat button (sidebar header)
document.getElementById('newChatBtn')?.addEventListener('click', () => {
    resetNewChatModal();
    openModal('newChatModal');
});

document.getElementById('addContactBtn')?.addEventListener('click', () => {
    openContactModal(null);
});

function resetNewChatModal() {
    state.ncSelectedContact = null;
    document.getElementById('ncContactSearch').value = '';
    document.getElementById('ncContactResults').innerHTML = `<div style="padding:14px;text-align:center;color:var(--wa-text-sub);font-size:13px;">Ketik untuk mencari kontak...</div>`;
    document.getElementById('ncStep1').hidden = false;
    document.getElementById('ncStep2').hidden = true;
    document.getElementById('ncNextBtn').hidden   = false;
    document.getElementById('ncNextBtn').disabled = true;
    document.getElementById('ncSubmitBtn').hidden = true;
    document.getElementById('ncBackBtn').hidden   = true;
    document.getElementById('ncStep1Tab').classList.add('active');
    document.getElementById('ncStep2Tab').classList.remove('active');
    document.getElementById('ncStep2Tab').disabled = true;
    document.getElementById('ncSubject').value  = '';
    document.getElementById('ncCategory').value = '';
    document.getElementById('ncPriority').value = 'medium';
}

function openNewChatModalWithContact(contact) {
    resetNewChatModal();
    selectNewChatContact(contact);
    openModal('newChatModal');
}

function selectNewChatContact(contact) {
    state.ncSelectedContact = contact;
    const tone = avatarTone(contact.name);
    const ini  = initials(contact.name);
    const res  = document.getElementById('ncContactResults');
    res.innerHTML = `
        <div class="contact-result-item selected" style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--wa-active);border-radius:var(--radius-md);">
            <span class="avatar avatar-sm avatar--${tone}">${esc(ini)}</span>
            <div>
                <div style="font-size:14px;font-weight:600;">${esc(contact.name)}</div>
                <div style="font-size:12px;color:var(--wa-text-sub);">${esc(contact.phone_number)}</div>
            </div>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--wa-accent)" stroke-width="2.5" style="margin-left:auto;"><polyline points="20 6 9 17 4 12"/></svg>
        </div>`;
    document.getElementById('ncNextBtn').disabled = false;
}

// Contact search in new chat modal
let ncSearchTimer = null;
document.getElementById('ncContactSearch')?.addEventListener('input', (e) => {
    clearTimeout(ncSearchTimer);
    const q = e.target.value.trim();
    if (!q) {
        document.getElementById('ncContactResults').innerHTML = `<div style="padding:14px;text-align:center;color:var(--wa-text-sub);font-size:13px;">Ketik untuk mencari kontak...</div>`;
        document.getElementById('ncCreateNewBtn').style.display = 'none';
        document.getElementById('ncNextBtn').disabled = true;
        state.ncSelectedContact = null;
        return;
    }
    ncSearchTimer = setTimeout(async () => {
        const data = await api(`/api/contacts?q=${encodeURIComponent(q)}`);
        const results = data.contacts ?? [];
        const res = document.getElementById('ncContactResults');
        if (!results.length) {
            res.innerHTML = `<div style="padding:14px;text-align:center;color:var(--wa-text-sub);font-size:13px;">Tidak ada kontak ditemukan</div>`;
            document.getElementById('ncCreateNewBtn').style.display = '';
        } else {
            document.getElementById('ncCreateNewBtn').style.display = 'none';
            res.innerHTML = results.map((c) => {
                const tone = avatarTone(c.name);
                const ini  = initials(c.name);
                return `<button class="contact-result-item" type="button" data-nc-contact='${JSON.stringify(c)}'>
                    <span class="avatar avatar-sm avatar--${tone}">${esc(ini)}</span>
                    <div>
                        <div style="font-size:14px;font-weight:500;">${esc(c.name)}</div>
                        <div style="font-size:12px;color:var(--wa-text-sub);">${esc(c.phone_number)}${c.company ? ` · ${esc(c.company)}` : ''}</div>
                    </div>
                </button>`;
            }).join('');
        }
    }, 280);
});

document.addEventListener('click', (e) => {
    const ncItem = e.target.closest('[data-nc-contact]');
    if (!ncItem) return;
    try { selectNewChatContact(JSON.parse(ncItem.dataset.ncContact)); } catch {}
});

document.getElementById('ncNextBtn')?.addEventListener('click', () => {
    if (!state.ncSelectedContact) return;
    const c = state.ncSelectedContact;
    document.getElementById('ncSelAvatar').textContent = initials(c.name);
    document.getElementById('ncSelName').textContent   = c.name;
    document.getElementById('ncSelPhone').textContent  = c.phone_number;
    document.getElementById('ncStep1').hidden = true;
    document.getElementById('ncStep2').hidden = false;
    document.getElementById('ncNextBtn').hidden   = true;
    document.getElementById('ncSubmitBtn').hidden = false;
    document.getElementById('ncBackBtn').hidden   = false;
    document.getElementById('ncStep2Tab').disabled = false;
    document.getElementById('ncStep2Tab').classList.add('active');
    document.getElementById('ncStep1Tab').classList.remove('active');
});

document.getElementById('ncBackBtn')?.addEventListener('click', () => {
    document.getElementById('ncStep1').hidden = false;
    document.getElementById('ncStep2').hidden = true;
    document.getElementById('ncNextBtn').hidden   = false;
    document.getElementById('ncSubmitBtn').hidden = true;
    document.getElementById('ncBackBtn').hidden   = true;
    document.getElementById('ncStep1Tab').classList.add('active');
    document.getElementById('ncStep2Tab').classList.remove('active');
});

document.getElementById('ncCreateNewBtn')?.addEventListener('click', () => {
    closeModal('newChatModal');
    openContactModal(null, document.getElementById('ncContactSearch').value.trim());
});

document.getElementById('ncSubmitBtn')?.addEventListener('click', async () => {
    const contact = state.ncSelectedContact;
    const subject  = document.getElementById('ncSubject').value.trim();
    if (!contact || !subject) { showToast('Subjek tidak boleh kosong', 'error'); return; }

    const btn = document.getElementById('ncSubmitBtn');
    btn.disabled = true;
    try {
        const data = await api('/api/chats/new', {
            method: 'POST',
            body: JSON.stringify({
                contact_id: contact.id,
                subject,
                category: document.getElementById('ncCategory').value || null,
                priority: document.getElementById('ncPriority').value,
            }),
        });
        closeModal('newChatModal');
        syncWorkspace(data.workspace);
        switchPanel('chats');
        showToast('Chat baru dibuka!');
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; }
});

// ── Contact add/edit modal ────────────────────────────────────────
function openContactModal(contact = null, prefillPhone = '') {
    const isEdit = Boolean(contact?.id);
    document.getElementById('contactModalTitle').textContent = isEdit ? '✏️ Edit Kontak' : '👤 Tambah Kontak';
    document.getElementById('contactModalId').value   = contact?.id ?? '';
    document.getElementById('cmName').value    = contact?.name ?? '';
    document.getElementById('cmPhone').value   = contact?.phone_number ?? prefillPhone;
    document.getElementById('cmEmail').value   = contact?.email ?? '';
    document.getElementById('cmCompany').value = contact?.company ?? '';
    document.getElementById('cmNotes').value   = contact?.notes ?? '';
    document.getElementById('cmVip').checked   = Boolean(contact?.is_vip);
    openModal('contactModal');
}

document.getElementById('contactModalSave')?.addEventListener('click', async () => {
    const id = document.getElementById('contactModalId').value;
    const payload = {
        name:         document.getElementById('cmName').value.trim(),
        phone_number: document.getElementById('cmPhone').value.trim(),
        email:        document.getElementById('cmEmail').value.trim() || null,
        company:      document.getElementById('cmCompany').value.trim() || null,
        notes:        document.getElementById('cmNotes').value.trim() || null,
        is_vip:       document.getElementById('cmVip').checked,
    };
    if (!payload.name || !payload.phone_number) {
        showToast('Nama dan nomor WA wajib diisi', 'error');
        return;
    }
    const btn = document.getElementById('contactModalSave');
    btn.disabled = true;
    try {
        let data;
        if (id) {
            data = await api(`/api/contacts/${id}`, { method: 'PATCH', body: JSON.stringify(payload) });
        } else {
            data = await api('/api/contacts', { method: 'POST', body: JSON.stringify(payload) });
        }
        closeModal('contactModal');
        state.contactsLoaded = false;
        loadContacts();
        showToast(id ? 'Kontak diperbarui' : 'Kontak ditambahkan');
        if (id && state.selectedContact?.id === Number(id)) {
            const detail = await api(`/api/contacts/${id}`);
            showContactDetail(detail.contact, detail.tickets ?? []);
        }
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; }
});

// Radio card selection visual (delete-chat modal)
document.querySelectorAll('#deleteChatModal .delete-option-card').forEach((card) => {
    card.addEventListener('click', () => {
        document.querySelectorAll('#deleteChatModal .delete-option-card').forEach((c) => c.classList.remove('selected'));
        card.classList.add('selected');
    });
});

// ── Delete/Archive chat modal ─────────────────────────────────────
function openDeleteChatModal(ticketId, subject, defaultMode = 'archive') {
    state.deleteChatTargetId = ticketId;
    document.getElementById('deleteChatId').value = ticketId;
    document.getElementById('deleteChatDesc').textContent = `Tiket "${subject}" akan diproses.`;

    const archiveCard = document.getElementById('dcArchive');
    const deleteCard  = document.getElementById('dcDelete');
    if (defaultMode === 'delete') {
        deleteCard?.classList.add('selected');
        archiveCard?.classList.remove('selected');
        deleteCard?.querySelector('input')?.click();
    } else {
        archiveCard?.classList.add('selected');
        deleteCard?.classList.remove('selected');
        archiveCard?.querySelector('input')?.click();
    }
    openModal('deleteChatModal');
}

document.getElementById('deleteChatConfirm')?.addEventListener('click', async () => {
    const ticketId = state.deleteChatTargetId;
    const action   = document.querySelector('#deleteChatModal input[name="deleteChat"]:checked')?.value;
    if (!ticketId || !action) return;

    const btn = document.getElementById('deleteChatConfirm');
    btn.disabled = true;
    try {
        // Both archive and delete return { workspace: ... } — use directly, no extra roundtrip
        const res = action === 'archive'
            ? await api(`/api/tickets/${ticketId}/archive`, { method: 'PATCH' })
            : await api(`/api/tickets/${ticketId}`, { method: 'DELETE' });

        closeModal('deleteChatModal');

        if (state.selectedTicketId === ticketId) {
            state.selectedTicketId = null;
            if (state.workspace) state.workspace.activeTicket = null;
        }

        const fromArchive = state._archiveDeleteSource;
        state._archiveDeleteSource = false;
        state._cdDeleteSource      = null;

        syncWorkspace(res.workspace);

        if (fromArchive) {
            loadArchive(document.getElementById('archiveSearchInput')?.value ?? '');
        }

        showToast(action === 'archive' ? 'Chat diarsip' : 'Chat dihapus permanen');
    } catch (err) { showToast(err.message, 'error'); }
    finally { btn.disabled = false; state._cdDeleteSource = null; state._archiveDeleteSource = false; }
});

// ── Bulk select ───────────────────────────────────────────────────
function enterBulkMode() {
    state.bulkMode = true;
    state.bulkSelected.clear();
    renderTicketList();
    updateBulkBar();
    document.getElementById('bulkSelectToggle')?.classList.add('active');
}

function exitBulkMode() {
    state.bulkMode = false;
    state.bulkSelected.clear();
    renderTicketList();
    updateBulkBar();
    document.getElementById('bulkSelectToggle')?.classList.remove('active');
}

function updateBulkBar() {
    const bar   = document.getElementById('bulkActionBar');
    const count = document.getElementById('bulkCount');
    if (!bar) return;
    bar.hidden = !state.bulkMode;
    if (count) count.textContent = `${state.bulkSelected.size} dipilih`;
}

document.getElementById('bulkSelectToggle')?.addEventListener('click', () => {
    state.bulkMode ? exitBulkMode() : enterBulkMode();
});

document.getElementById('bulkCancelBtn')?.addEventListener('click', exitBulkMode);

// Checkbox change in ticket list
document.addEventListener('change', (e) => {
    const cb = e.target.closest('.ticket-checkbox');
    if (!cb) return;
    const id = Number(cb.dataset.bulkId);
    cb.checked ? state.bulkSelected.add(id) : state.bulkSelected.delete(id);
    updateBulkBar();
});

document.getElementById('bulkArchiveBtn')?.addEventListener('click', async () => {
    if (!state.bulkSelected.size) return;
    if (!confirm(`Arsip ${state.bulkSelected.size} tiket?`)) return;
    try {
        await api('/api/tickets/bulk', {
            method: 'POST',
            body: JSON.stringify({ ids: [...state.bulkSelected], action: 'archive' }),
        });
        exitBulkMode();
        const data = await api('/api/workspace');
        syncWorkspace(data.workspace);
        showToast('Tiket diarsip');
    } catch (err) { showToast(err.message, 'error'); }
});

document.getElementById('bulkDeleteBtn')?.addEventListener('click', async () => {
    if (!state.bulkSelected.size) return;
    if (!confirm(`Hapus permanen ${state.bulkSelected.size} tiket? Tindakan ini tidak dapat dibatalkan.`)) return;
    try {
        await api('/api/tickets/bulk', {
            method: 'POST',
            body: JSON.stringify({ ids: [...state.bulkSelected], action: 'delete' }),
        });
        exitBulkMode();
        const data = await api('/api/workspace');
        syncWorkspace(data.workspace);
        showToast('Tiket dihapus permanen');
    } catch (err) { showToast(err.message, 'error'); }
});

// ── Init ─────────────────────────────────────────────────────────
applyTheme(state.theme);
render();
loadQuickChats();
