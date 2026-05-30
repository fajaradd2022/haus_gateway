<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\QuickChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickChatController extends Controller
{
    /** GET /api/quick-chats — active only, readable by all agents (used in workspace composer) */
    public function index(): JsonResponse
    {
        $quickChats = QuickChat::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->map(fn (QuickChat $qc) => $this->payload($qc));

        return response()->json(['quick_chats' => $quickChats]);
    }

    /** GET /api/quick-chats/all — all records including inactive (admin dashboard) */
    public function indexAll(): JsonResponse
    {
        $this->requireAdmin();

        $quickChats = QuickChat::query()
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->map(fn (QuickChat $qc) => $this->payload($qc));

        return response()->json(['quick_chats' => $quickChats]);
    }

    /** POST /api/quick-chats */
    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin();

        $data = $request->validate([
            'title'     => ['required', 'string', 'max:191'],
            'body'      => ['required', 'string'],
            'category'  => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $qc = QuickChat::query()->create([
            ...$data,
            'created_by' => auth()->id(),
            'is_active'  => $data['is_active'] ?? true,
        ]);

        AuditLog::query()->create([
            'user_id'     => auth()->id(),
            'action'      => 'quick_chat.created',
            'description' => "Quick chat template '{$qc->title}' dibuat.",
            'context'     => ['quick_chat_id' => $qc->id],
        ]);

        return response()->json(['quick_chat' => $this->payload($qc)], 201);
    }

    /** PATCH /api/quick-chats/{quickChat} */
    public function update(Request $request, QuickChat $quickChat): JsonResponse
    {
        $this->requireAdmin();

        $data = $request->validate([
            'title'     => ['sometimes', 'string', 'max:191'],
            'body'      => ['sometimes', 'string'],
            'category'  => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $quickChat->update($data);

        AuditLog::query()->create([
            'user_id'     => auth()->id(),
            'action'      => 'quick_chat.updated',
            'description' => "Quick chat template '{$quickChat->title}' diperbarui.",
            'context'     => ['quick_chat_id' => $quickChat->id],
        ]);

        return response()->json(['quick_chat' => $this->payload($quickChat->fresh())]);
    }

    /** DELETE /api/quick-chats/{quickChat} */
    public function destroy(QuickChat $quickChat): JsonResponse
    {
        $this->requireAdmin();

        AuditLog::query()->create([
            'user_id'     => auth()->id(),
            'action'      => 'quick_chat.deleted',
            'description' => "Quick chat template '{$quickChat->title}' dihapus.",
            'context'     => ['quick_chat_id' => $quickChat->id],
        ]);

        $quickChat->delete();

        return response()->json(['message' => 'Quick chat template dihapus.']);
    }

    private function payload(QuickChat $qc): array
    {
        return [
            'id'         => $qc->id,
            'title'      => $qc->title,
            'body'       => $qc->body,
            'category'   => $qc->category,
            'is_active'  => $qc->is_active,
            'created_by' => $qc->created_by,
            'updated_at' => $qc->updated_at?->format('d M Y H:i'),
        ];
    }

    private function requireAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }
}
