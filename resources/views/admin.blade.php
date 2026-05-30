@extends('layouts.app', ['vite' => []])

@section('title', 'Admin Dashboard — Mini Helpdesk AI Assist')
@section('body_class', 'admin-page')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Admin-only overrides */
        body { overflow-x: hidden; overflow-y: auto; height: auto; }
        .admin-page { min-height: 100vh; overflow-x: hidden; }

        /* Profile trigger button di topbar */
        .admin-user-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px 6px 8px;
            border: 1px solid transparent;
            border-radius: 999px;
            background: transparent;
            color: var(--wa-text);
            cursor: pointer;
            transition: background 140ms, border-color 140ms;
            margin-left: 8px;
            border-left: 1px solid var(--wa-border);
            border-left-width: 1px;
            border-radius: 0 999px 999px 0;
            padding-left: 14px;
        }
        .admin-user-trigger:hover,
        .user-menu-open .admin-user-trigger {
            background: var(--wa-hover);
        }
        .admin-user-trigger__text { text-align: left; line-height: 1.2; }
        .admin-user-trigger__name { font-size: 13px; font-weight: 600; color: var(--wa-text); }
        .admin-user-trigger__role { font-size: 11px; color: var(--wa-text-sub); }
        .admin-user-trigger__chevron {
            color: var(--wa-text-sub);
            transition: transform 160ms;
        }
        .user-menu-open .admin-user-trigger__chevron { transform: rotate(180deg); }
    </style>
@endpush

@section('content')

    {{-- Top bar (brand, theme, profile, logout) --}}
    @include('admin.partials.topbar')

    {{-- Modal (KB + user) — di luar grid supaya posisinya fixed bersih --}}
    @include('admin.partials.modals.index')

    <div class="admin-content">

        {{-- Statistik ringkas --}}
        @include('admin.partials.stats-grid')

        {{-- Chart (line + doughnut) --}}
        @include('admin.partials.charts')

        {{-- Performance per agent --}}
        @include('admin.partials.agent-performance')

        {{-- Panel data: KB + Quick Chat + Audit Trail (3-col grid) --}}
        <div class="panels-grid">
            @include('admin.partials.knowledge-base')
            @include('admin.partials.quick-chats')
            @include('admin.partials.audit-trail')
        </div>

        {{-- User Management: full width section --}}
        @include('admin.partials.user-management')

    </div>

@endsection

@push('scripts')
    @include('admin.partials.scripts.index')
@endpush
