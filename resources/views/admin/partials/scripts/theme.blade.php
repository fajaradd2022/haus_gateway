{{-- Theme toggle (light/dark) — refresh chart agar warna sinkron --}}
{
    const theme = localStorage.getItem('helpdesk-theme') ?? 'light';
    document.documentElement.dataset.theme = theme;

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('helpdesk-theme', next);
        updateChartTheme();
    });
}
