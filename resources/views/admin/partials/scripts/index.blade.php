{{-- Entry script admin: satu IIFE, sub-script saling berbagi scope (helpers, charts, state) --}}
<script>
(function () {
    @include('admin.partials.scripts.shared')

    @include('admin.partials.scripts.theme')

    @include('admin.partials.scripts.user-menu')

    @include('admin.partials.scripts.charts')

    @include('admin.partials.scripts.audit-filter')

    @include('admin.partials.scripts.knowledge-base')

    @include('admin.partials.scripts.user-management')

    @include('admin.partials.scripts.quick-chats')
})();
</script>
