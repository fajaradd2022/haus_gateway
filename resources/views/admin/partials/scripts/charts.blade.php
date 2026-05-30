{{-- Chart.js: line (tiket/hari) + doughnut (status) + theme switch --}}
const perDayData = @json($adminData['ticketsPerDay']);
const lineLabels = perDayData.map(d => d.label);
const lineCounts = perDayData.map(d => d.count);

const stats = @json($adminData['stats']);
const statusConfig = [
    { key: 'open',       label: 'Open',       color: '#53bdeb' },
    { key: 'on_progress',label: 'Progress',   color: '#25d366' },
    { key: 'pending',    label: 'Pending',    color: '#fcbe2d' },
    { key: 'closed',     label: 'Closed',     color: '#8696a0' },
];

let lineChart, doughnutChart;

function buildLineChart() {
    const c = chartColors();
    const ctx = document.getElementById('ticketsLineChart')?.getContext('2d');
    if (!ctx) return;

    lineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: lineLabels,
            datasets: [{
                label: 'Tiket Masuk',
                data: lineCounts,
                borderColor: '#00a884',
                backgroundColor: 'rgba(0,168,132,0.1)',
                borderWidth: 2.5,
                pointBackgroundColor: '#00a884',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark() ? '#202c33' : '#fff',
                    titleColor: c.text,
                    bodyColor: c.subtext,
                    borderColor: isDark() ? '#2a3942' : '#e9edef',
                    borderWidth: 1,
                    padding: 10,
                },
            },
            scales: {
                x: {
                    grid: { color: c.grid },
                    ticks: { color: c.subtext, font: { size: 12 } },
                    border: { color: c.grid },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: c.grid },
                    ticks: { color: c.subtext, font: { size: 12 }, stepSize: 1 },
                    border: { color: c.grid },
                },
            },
        },
    });
}

function buildDoughnutChart() {
    const ctx = document.getElementById('statusDoughnutChart')?.getContext('2d');
    if (!ctx) return;

    const values = statusConfig.map(s => stats[s.key] ?? 0);
    const colors = statusConfig.map(s => s.color);
    const labels = statusConfig.map(s => s.label);

    doughnutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark() ? '#202c33' : '#fff',
                    titleColor: isDark() ? '#e9edef' : '#111b21',
                    bodyColor: isDark() ? '#8696a0' : '#667781',
                    borderColor: isDark() ? '#2a3942' : '#e9edef',
                    borderWidth: 1,
                },
            },
        },
    });

    const legend = document.getElementById('statusLegend');
    if (legend) {
        legend.innerHTML = statusConfig.map((s, i) => `
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--wa-text-sub);">
                <span style="width:10px;height:10px;border-radius:50%;background:${s.color};flex-shrink:0;"></span>
                ${s.label} <strong style="color:var(--wa-text);">${values[i]}</strong>
            </div>
        `).join('');
    }
}

function updateChartTheme() {
    if (lineChart) lineChart.destroy();
    if (doughnutChart) doughnutChart.destroy();
    buildLineChart();
    buildDoughnutChart();
}

buildLineChart();
buildDoughnutChart();
