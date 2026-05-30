{{-- Grid 2 chart: line (tiket per hari) + doughnut (status) --}}
<div class="charts-grid">
    {{-- Line chart: tickets per day --}}
    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <div class="admin-card-title">Tiket Masuk (7 Hari Terakhir)</div>
                <div class="admin-card-sub">Jumlah tiket baru per hari</div>
            </div>
            <span class="badge badge--on_progress">7 hari</span>
        </div>
        <div class="admin-card-body">
            <div class="chart-wrap">
                <canvas id="ticketsLineChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Doughnut chart: by status --}}
    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <div class="admin-card-title">Distribusi Status</div>
                <div class="admin-card-sub">Proporsi tiket saat ini</div>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="chart-wrap" style="height:200px;">
                <canvas id="statusDoughnutChart"></canvas>
            </div>
            <div id="statusLegend" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;"></div>
        </div>
    </div>
</div>
