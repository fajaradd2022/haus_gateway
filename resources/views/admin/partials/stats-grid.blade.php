{{-- Grid kartu statistik ringkas (9 kartu) --}}
<div class="stats-grid">
    <div class="stat-card stat-card--accent">
        <div class="stat-card-icon">📋</div>
        <div class="stat-card-value">{{ $adminData['stats']['total'] }}</div>
        <div class="stat-card-label">Total Tiket</div>
        <div class="stat-card-change">Semua waktu</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(83,189,235,.12);">📬</div>
        <div class="stat-card-value" style="color:#1a7eb8;">{{ $adminData['stats']['open'] }}</div>
        <div class="stat-card-label">Open</div>
        <div class="stat-card-change">Menunggu respons</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(37,211,102,.12);">⚡</div>
        <div class="stat-card-value" style="color:#1a8c45;">{{ $adminData['stats']['on_progress'] }}</div>
        <div class="stat-card-label">On Progress</div>
        <div class="stat-card-change">Sedang ditangani</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(252,190,45,.15);">⏳</div>
        <div class="stat-card-value" style="color:#b07a00;">{{ $adminData['stats']['pending'] }}</div>
        <div class="stat-card-label">Pending</div>
        <div class="stat-card-change">Menunggu customer</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">✅</div>
        <div class="stat-card-value">{{ $adminData['stats']['closed'] }}</div>
        <div class="stat-card-label">Closed</div>
        <div class="stat-card-change">{{ $adminData['stats']['closed_today'] }} ditutup hari ini</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(0,168,132,.12);">👥</div>
        <div class="stat-card-value" style="color:var(--wa-accent);">{{ $adminData['stats']['active_agents'] }}<span style="font-size:16px;font-weight:400;color:var(--wa-text-sub);">/{{ $adminData['stats']['total_agents'] }}</span></div>
        <div class="stat-card-label">Agents Online</div>
        <div class="stat-card-change">Dari {{ $adminData['stats']['total_agents'] }} total agents</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon">💬</div>
        <div class="stat-card-value">{{ $adminData['stats']['total_messages'] }}</div>
        <div class="stat-card-label">Total Pesan</div>
        <div class="stat-card-change">Semua tiket</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon" style="background:rgba(137,87,229,.12);">📚</div>
        <div class="stat-card-value" style="color:#845ec2;">{{ $adminData['stats']['kb_articles'] }}</div>
        <div class="stat-card-label">KB Articles</div>
        <div class="stat-card-change">Knowledge base</div>
    </div>
</div>
