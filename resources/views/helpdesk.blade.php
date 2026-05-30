@extends('layouts.app', ['vite' => ['resources/js/app.js']])

@section('title', 'HAUS Gateway')

@section('content')

    {{-- Settings panel (off-canvas) --}}
    @include('helpdesk.partials.settings-panel')

    {{-- Semua modal --}}
    @include('helpdesk.partials.modals.index')

    {{-- Main app shell --}}
    <div class="wa-app">

        {{-- Navigasi sisi kiri --}}
        @include('helpdesk.partials.nav-rail')

        {{-- Sidebar (chats / contacts / archive) --}}
        <aside class="wa-side" id="waSide">
            @include('helpdesk.partials.sidebar.chats-panel')
            @include('helpdesk.partials.sidebar.contacts-panel')
            @include('helpdesk.partials.sidebar.archive-panel')

            {{-- Bottom nav khusus mobile --}}
            @include('helpdesk.partials.mobile-bottom-nav')
        </aside>

        {{-- Pane utama --}}
        <main class="wa-pane">
            @include('helpdesk.partials.mobile-topbar')
            @include('helpdesk.partials.chat-pane.empty-state')

            {{-- Row: chat pane + AI suggestion side panel --}}
            <div class="pane-row">
                @include('helpdesk.partials.chat-pane.chat-view')
                @include('helpdesk.partials.chat-pane.ai-panel')
            </div>
        </main>

    </div>

@endsection

@push('scripts')
    <script>
        window.helpdeskBoot = @json($workspace);
    </script>
@endpush
