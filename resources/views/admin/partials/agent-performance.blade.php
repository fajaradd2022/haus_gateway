{{-- Performance bar tiap agent (30 hari terakhir) --}}
@if(count($adminData['messagesByAgent']) > 0)
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <div class="admin-card-title">Agent Performance</div>
            <div class="admin-card-sub">Jumlah pesan terkirim per agent (30 hari terakhir)</div>
        </div>
        <span class="badge">30 hari</span>
    </div>
    @php $maxMsg = $adminData['messagesByAgent']->max('count') ?: 1; @endphp
    @foreach($adminData['messagesByAgent'] as $agent)
    <div class="agent-perf-row">
        <span class="avatar avatar-sm" style="background:var(--wa-accent);">{{ strtoupper(substr($agent['name'], 0, 1)) }}</span>
        <span style="font-size:14px;font-weight:500;color:var(--wa-text);width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $agent['name'] }}</span>
        <div class="agent-perf-bar-wrap">
            <div class="agent-perf-bar" style="width:{{ round(($agent['count'] / $maxMsg) * 100) }}%"></div>
        </div>
        <span class="agent-perf-count">{{ $agent['count'] }}</span>
    </div>
    @endforeach
</div>
@endif
