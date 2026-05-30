{{-- Panel Knowledge Base (CRUD artikel) --}}
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <div class="admin-card-title">📚 Knowledge Base</div>
            <div class="admin-card-sub" id="kbCount">{{ count($adminData['knowledgeBase']) }} artikel</div>
        </div>
        <button class="btn-primary" type="button" id="addKbBtn" style="padding:7px 14px;font-size:13px;">
            + Tambah Artikel
        </button>
    </div>
    <div class="panel-scroll" id="kbList">
        @forelse($adminData['knowledgeBase'] as $article)
        <div class="kb-card" data-kb-id="{{ $article->id }}"
            data-kb-json="{{ htmlspecialchars(json_encode(['id'=>$article->id,'title'=>$article->title,'content'=>$article->content,'source'=>$article->source ?? '']), ENT_QUOTES) }}">
            <div class="kb-card-header">
                <div class="kb-card-title">{{ $article->title }}</div>
                <div class="kb-card-actions">
                    <button class="btn-ghost-sm kb-edit-btn" type="button" style="font-size:12px;padding:3px 10px;">✏️ Edit</button>
                    <button class="btn-danger-sm kb-delete-btn" type="button" style="font-size:12px;padding:3px 10px;">🗑</button>
                </div>
            </div>
            <div class="kb-card-content">{{ $article->content }}</div>
            <div class="kb-card-footer">
                @if($article->source)
                <span class="kb-card-source">{{ $article->source }}</span>
                @endif
                @if($article->updated_at)
                <span style="font-size:11px;color:var(--wa-text-sub);margin-left:auto;">
                    Diperbarui: {{ $article->updated_at->format('d M Y H:i') }}
                </span>
                @endif
            </div>
        </div>
        @empty
        <div id="kbEmptyRow" style="text-align:center;padding:32px;color:var(--wa-text-sub);font-size:14px;">
            Belum ada artikel Knowledge Base
        </div>
        @endforelse
    </div>
</div>
