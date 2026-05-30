{{-- Panel: Contacts --}}
<div class="side-panel" id="panelContacts">

    {{-- Header --}}
    <div class="side-head">
        <span class="side-title">Kontak</span>
        <div class="side-head-actions">
            <button class="icon-btn" type="button" id="addContactBtn" title="Add contact" aria-label="Add contact">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="side-search">
        <input type="search" id="contactSearchInput" placeholder="Cari nama, nomor, perusahaan..." aria-label="Search contacts">
    </div>

    {{-- List --}}
    <div class="side-list contacts-list" id="contactsList">
        <div class="side-list-placeholder" style="padding:64px 16px;text-align:center;color:var(--wa-text-sub);font-size:13px;">
            Memuat kontak...
        </div>
    </div>

    @include('helpdesk.partials.sidebar.contact-detail')

</div>
