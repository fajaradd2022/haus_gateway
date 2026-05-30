{{-- Panel Audit Trail (riwayat aktivitas) --}}
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <div class="admin-card-title">🔍 Audit Trail</div>
            <div class="admin-card-sub">{{ count($adminData['auditLogs']) }} aktivitas terakhir</div>
        </div>
        <div style="display:flex;gap:6px;">
            <button class="btn-ghost audit-filter-btn active" style="font-size:12px;padding:4px 10px;" data-audit-filter="all">All</button>
            <button class="btn-ghost audit-filter-btn" style="font-size:12px;padding:4px 10px;" data-audit-filter="ticket.reply">Reply</button>
            <button class="btn-ghost audit-filter-btn" style="font-size:12px;padding:4px 10px;" data-audit-filter="ticket.note">Note</button>
        </div>
    </div>
    <div class="panel-scroll" id="auditList">
        @forelse($adminData['auditLogs'] as $log)
        <div class="audit-item" data-audit-action="{{ $log->action }}">
            <div class="audit-action">{{ $log->action }}</div>
            <div class="audit-desc">{{ $log->description }}</div>
            <div class="audit-meta">
                <span class="avatar avatar-sm" style="background:var(--wa-accent);width:18px;height:18px;font-size:9px;flex-shrink:0;">{{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}</span>
                {{ $log->user?->name ?? 'System' }} ·
                {{ $log->created_at?->format('d M H:i') }}
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:32px;color:var(--wa-text-sub);font-size:14px;">
            Belum ada audit log
        </div>
        @endforelse
    </div>
</div>
