{{-- Filter audit trail (All / Reply / Note) --}}
document.querySelectorAll('[data-audit-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-audit-filter]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.dataset.auditFilter;
        document.querySelectorAll('[data-audit-action]').forEach(item => {
            item.style.display = (filter === 'all' || item.dataset.auditAction.startsWith(filter))
                ? '' : 'none';
        });
    });
});
