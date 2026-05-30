<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KnowledgeBase;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AiService;
use App\Services\WahaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;

class HelpdeskController extends Controller
{
    public function index(): View
    {
        return view('helpdesk', [
            'workspace' => $this->workspacePayload(),
        ]);
    }

    public function admin(): View
    {
        $user = $this->currentAgent();

        abort_unless($user->role === 'admin', 403);

        // Tickets per day (last 7 days) — filled with zeros for missing dates
        $last7 = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->format('Y-m-d'));
        $rawPerDay = Ticket::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');
        $ticketsPerDay = $last7->map(fn ($date) => [
            'date'  => $date,
            'label' => Carbon::parse($date)->format('d M'),
            'count' => (int) ($rawPerDay[$date] ?? 0),
        ])->values();

        // Messages per agent (last 30 days)
        $messagesByAgent = Message::query()
            ->selectRaw('users.name, users.id as user_id, COUNT(messages.id) as count')
            ->join('users', 'messages.agent_id', '=', 'users.id')
            ->where('messages.sent_at', '>=', now()->subDays(30))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'count' => (int) $r->count])
            ->values();

        // Overall stats
        $allTickets = Ticket::query()->get();
        $stats = [
            'total'          => $allTickets->count(),
            'open'           => $allTickets->where('status', 'open')->count(),
            'pending'        => $allTickets->where('status', 'pending')->count(),
            'on_progress'    => $allTickets->where('status', 'on_progress')->count(),
            'closed'         => $allTickets->where('status', 'closed')->count(),
            'closed_today'   => Ticket::query()->where('status', 'closed')->whereDate('updated_at', today())->count(),
            'active_agents'  => User::query()->where('is_online', true)->count(),
            'total_agents'   => User::query()->where('role', 'agent')->count(),
            'total_messages' => Message::query()->count(),
            'kb_articles'    => KnowledgeBase::query()->count(),
        ];

        return view('admin', [
            'adminData' => [
                'currentUser'    => $this->agentPayload($user),
                'stats'          => $stats,
                'ticketsPerDay'  => $ticketsPerDay,
                'messagesByAgent'=> $messagesByAgent,
                'knowledgeBase'  => KnowledgeBase::query()->orderByDesc('updated_at')->get(),
                'auditLogs'      => AuditLog::query()->with('user')->latest()->take(80)->get(),
                'users'          => User::query()->with('assignedTickets')->orderBy('role')->orderBy('name')->get(),
            ],
        ]);
    }

    public function workspace(): JsonResponse
    {
        return response()->json($this->workspacePayload());
    }

    public function showTicket(Ticket $ticket): JsonResponse
    {
        return response()->json([
            'ticket' => $this->ticketPayload($ticket->id),
        ]);
    }

    public function storeMessage(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'is_internal_note' => ['sometimes', 'boolean'],
            'media_url' => ['nullable', 'url'],
            'media_type' => ['nullable', 'string', 'max:50'],
        ]);

        if (blank($validated['content'] ?? null) && blank($validated['media_url'] ?? null)) {
            return response()->json([
                'message' => 'Pesan kosong tidak dapat dikirim.',
            ], 422);
        }

        // Guard: require subject before agent can reply
        $isInternalNote = (bool) ($validated['is_internal_note'] ?? false);
        if (! $isInternalNote && blank($ticket->subject)) {
            return response()->json([
                'message' => 'Mohon isi nama tiket terlebih dahulu sebelum membalas.',
            ], 422);
        }

        $agent = $ticket->assignedAgent ?: $this->currentAgent();

        $message = Message::query()->create([
            'ticket_id'        => $ticket->id,
            'sender_type'      => 'agent',
            'content'          => $validated['content'] ?? null,
            'agent_id'         => $agent->id,
            'media_url'        => $validated['media_url'] ?? null,
            'media_type'       => $validated['media_type'] ?? null,
            'sent_at'          => now(),
            'is_internal_note' => $isInternalNote,
        ]);

        // Send to WhatsApp via WAHA (outbound delivery)
        $customerPhone = $ticket->customer?->phone_number;
        if (! $isInternalNote && $ticket->channel === 'whatsapp' && $customerPhone) {
            $waha   = app(WahaService::class);
            $wahaId = null;

            if (! blank($validated['media_url'] ?? null)) {
                $wahaId = $waha->sendMedia($customerPhone, $validated['media_url'], $validated['content'] ?? '');
            } elseif (! blank($validated['content'] ?? null)) {
                $wahaId = $waha->sendText($customerPhone, $validated['content']);
            }

            if ($wahaId) {
                $message->update(['waha_message_id' => $wahaId]);
            }
        }

        $ticket->forceFill([
            'assigned_agent_id' => $agent->id,
            'status'            => $ticket->status === 'open' ? 'on_progress' : $ticket->status,
            'last_message_at'   => now(),
        ])->save();

        AuditLog::query()->create([
            'user_id' => $agent->id,
            'action' => ($validated['is_internal_note'] ?? false) ? 'ticket.note' : 'ticket.reply',
            'description' => ($validated['is_internal_note'] ?? false)
                ? "Internal note ditambahkan ke tiket #{$ticket->id}."
                : "Balasan dikirim ke tiket #{$ticket->id}.",
            'context' => ['ticket_id' => $ticket->id],
        ]);

        return response()->json([
            'workspace' => $this->workspacePayload($ticket->id),
        ]);
    }

    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,on_progress,closed'],
        ]);

        $ticket->update(['status' => $validated['status']]);

        AuditLog::query()->create([
            'user_id' => optional($ticket->assignedAgent)->id ?? $this->currentAgent()->id,
            'action' => 'ticket.status_changed',
            'description' => "Status tiket #{$ticket->id} diubah menjadi {$validated['status']}.",
            'context' => ['ticket_id' => $ticket->id, 'status' => $validated['status']],
        ]);

        return response()->json([
            'workspace' => $this->workspacePayload($ticket->id),
        ]);
    }

    /** Public proxy so ContactController can call it. */
    public function workspacePayloadPublic(?int $activeTicketId = null): array
    {
        return $this->workspacePayload($activeTicketId);
    }

    /** PATCH /api/tickets/{ticket}/subject — agent sets name for an unnamed ticket */
    public function updateSubject(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
        ]);

        $ticket->update(['subject' => $validated['subject']]);

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'ticket.subject_set',
            'description' => "Nama tiket #{$ticket->id} diatur: \"{$validated['subject']}\".",
            'context'     => ['ticket_id' => $ticket->id],
        ]);

        return response()->json(['workspace' => $this->workspacePayload($ticket->id)]);
    }

    /** POST /api/tickets/{ticket}/move-messages — move selected messages to another ticket */
    public function moveMessages(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'target_ticket_id' => ['required', 'integer', 'exists:tickets,id', 'different:ticket'],
            'message_ids'      => ['required', 'array', 'min:1'],
            'message_ids.*'    => ['integer'],
        ]);

        $targetTicket = Ticket::findOrFail($validated['target_ticket_id']);

        Message::query()
            ->whereIn('id', $validated['message_ids'])
            ->where('ticket_id', $ticket->id)
            ->update(['ticket_id' => $targetTicket->id]);

        // Update last_message_at on both tickets
        foreach ([$ticket, $targetTicket] as $t) {
            $latest = Message::query()->where('ticket_id', $t->id)->max('sent_at');
            if ($latest) {
                $t->forceFill(['last_message_at' => $latest])->save();
            }
        }

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'ticket.messages_moved',
            'description' => count($validated['message_ids']) . " pesan dipindahkan dari tiket #{$ticket->id} ke tiket #{$targetTicket->id}.",
            'context'     => ['from_ticket_id' => $ticket->id, 'to_ticket_id' => $targetTicket->id, 'message_ids' => $validated['message_ids']],
        ]);

        return response()->json(['workspace' => $this->workspacePayload($targetTicket->id)]);
    }

    /** POST /api/tickets/{ticket}/split — create a new ticket from selected messages */
    public function splitTicket(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'subject'      => ['required', 'string', 'max:255'],
            'message_ids'  => ['required', 'array', 'min:1'],
            'message_ids.*'=> ['integer'],
        ]);

        $agent = $this->currentAgent();

        $newTicket = Ticket::query()->create([
            'customer_id'       => $ticket->customer_id,
            'assigned_agent_id' => $agent->id,
            'subject'           => $validated['subject'],
            'status'            => 'open',
            'priority'          => $ticket->priority ?? 'medium',
            'channel'           => $ticket->channel,
            'last_message_at'   => now(),
            'sla_deadline'      => now()->addHours(2),
        ]);

        Message::query()
            ->whereIn('id', $validated['message_ids'])
            ->where('ticket_id', $ticket->id)
            ->update(['ticket_id' => $newTicket->id]);

        // Recalculate last_message_at for source ticket
        $latest = Message::query()->where('ticket_id', $ticket->id)->max('sent_at');
        if ($latest) {
            $ticket->forceFill(['last_message_at' => $latest])->save();
        }

        AuditLog::query()->create([
            'user_id'     => $agent->id,
            'action'      => 'ticket.split',
            'description' => "Tiket #{$ticket->id} dipecah menjadi tiket baru #{$newTicket->id} \"{$validated['subject']}\".",
            'context'     => ['source_ticket_id' => $ticket->id, 'new_ticket_id' => $newTicket->id],
        ]);

        return response()->json(['workspace' => $this->workspacePayload($newTicket->id)], 201);
    }

    // ── Archive / Delete / Bulk ───────────────────────────────────

    public function archivedTickets(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString();

        $tickets = Ticket::query()
            ->with(['customer'])
            ->whereNotNull('archived_at')
            ->when($q, fn ($query) => $query->where(function ($qb) use ($q) {
                $qb->where('subject', 'like', "%{$q}%")
                   ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            }))
            ->orderByDesc('archived_at')
            ->limit(200)
            ->get()
            ->map(fn (Ticket $t) => [
                'id'            => $t->id,
                'subject'       => $t->subject,
                'status'        => $t->status,
                'customer_name' => $t->customer?->name,
                'customer_id'   => $t->customer_id,
                'archived_at'   => optional($t->archived_at)->toIso8601String(),
            ]);

        return response()->json(['tickets' => $tickets]);
    }

    public function unarchiveTicket(Ticket $ticket): JsonResponse
    {
        $ticket->archived_at = null;
        $ticket->save();

        return response()->json(['workspace' => $this->workspacePayload()]);
    }

    public function archiveTicket(Request $request, Ticket $ticket): JsonResponse
    {
        $ticket->archived_at = now();
        $ticket->save();

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'ticket.archived',
            'description' => "Tiket #{$ticket->id} diarsipkan.",
            'context'     => ['ticket_id' => $ticket->id],
        ]);

        return response()->json(['workspace' => $this->workspacePayload()]);
    }

    public function destroyTicket(Ticket $ticket): JsonResponse
    {
        $id = $ticket->id;
        $ticket->messages()->delete();
        $ticket->delete();

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'ticket.deleted',
            'description' => "Tiket #{$id} dihapus permanen.",
            'context'     => ['ticket_id' => $id],
        ]);

        return response()->json(['workspace' => $this->workspacePayload()]);
    }

    public function bulkTickets(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
            'action' => ['required', 'in:archive,delete'],
        ]);

        $tickets = Ticket::query()->whereIn('id', $data['ids'])->get();

        foreach ($tickets as $ticket) {
            if ($data['action'] === 'delete') {
                $ticket->messages()->delete();
                $ticket->delete();
            } else {
                $ticket->archived_at = now();
                $ticket->save();
            }
        }

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => "ticket.bulk_{$data['action']}",
            'description' => count($data['ids']) . " tiket di-{$data['action']} sekaligus.",
            'context'     => ['ids' => $data['ids']],
        ]);

        return response()->json(['workspace' => $this->workspacePayload()]);
    }

    // ── Knowledge Base (admin only) ──────────────────────────────────

    public function storeKnowledge(Request $request): JsonResponse
    {
        abort_unless($this->currentAgent()->role === 'admin', 403);

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:191'],
            'content' => ['required', 'string'],
            'source'  => ['nullable', 'string', 'max:191'],
        ]);

        $article = KnowledgeBase::query()->create($data);

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'knowledge_base.created',
            'description' => "Artikel KB '{$article->title}' dibuat.",
            'context'     => ['kb_id' => $article->id],
        ]);

        return response()->json(['article' => $this->kbPayload($article)], 201);
    }

    public function updateKnowledge(Request $request, KnowledgeBase $knowledgeBase): JsonResponse
    {
        abort_unless($this->currentAgent()->role === 'admin', 403);

        $data = $request->validate([
            'title'   => ['sometimes', 'string', 'max:191'],
            'content' => ['sometimes', 'string'],
            'source'  => ['nullable', 'string', 'max:191'],
        ]);

        $knowledgeBase->update($data);

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'knowledge_base.updated',
            'description' => "Artikel KB '{$knowledgeBase->title}' diperbarui.",
            'context'     => ['kb_id' => $knowledgeBase->id],
        ]);

        return response()->json(['article' => $this->kbPayload($knowledgeBase->fresh())]);
    }

    public function destroyKnowledge(KnowledgeBase $knowledgeBase): JsonResponse
    {
        abort_unless($this->currentAgent()->role === 'admin', 403);

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'knowledge_base.deleted',
            'description' => "Artikel KB '{$knowledgeBase->title}' dihapus.",
            'context'     => ['kb_id' => $knowledgeBase->id],
        ]);

        $knowledgeBase->delete();

        return response()->json(['message' => 'Artikel dihapus.']);
    }

    private function kbPayload(KnowledgeBase $article): array
    {
        return [
            'id'             => $article->id,
            'title'          => $article->title,
            'content'        => $article->content,
            'source'         => $article->source,
            'last_synced_at' => $article->last_synced_at?->format('d M Y'),
            'updated_at'     => $article->updated_at?->format('d M Y H:i'),
        ];
    }

    // ── User Management (admin only) ─────────────────────────────────

    public function storeUser(Request $request): JsonResponse
    {
        abort_unless($this->currentAgent()->role === 'admin', 403);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:191'],
            'email'    => ['required', 'email', 'max:191', 'unique:users,email'],
            'role'     => ['required', 'in:admin,agent'],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::query()->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'user.created',
            'description' => "User baru {$user->name} ({$user->role}) dibuat.",
            'context'     => ['user_id' => $user->id],
        ]);

        return response()->json(['user' => $this->userPayload($user)], 201);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        abort_unless($this->currentAgent()->role === 'admin', 403);

        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:191'],
            'email'    => ['sometimes', 'email', 'max:191', "unique:users,email,{$user->id}"],
            'role'     => ['sometimes', 'in:admin,agent'],
            'password' => ['nullable', 'string', Password::min(8)->mixedCase()->numbers()],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        AuditLog::query()->create([
            'user_id'     => $this->currentAgent()->id,
            'action'      => 'user.updated',
            'description' => "User {$user->name} diperbarui.",
            'context'     => ['user_id' => $user->id],
        ]);

        return response()->json(['user' => $this->userPayload($user->fresh())]);
    }

    public function destroyUser(User $user): JsonResponse
    {
        $me = $this->currentAgent();
        abort_unless($me->role === 'admin', 403);
        abort_if($user->id === $me->id, 422, 'Tidak dapat menghapus akun sendiri.');

        AuditLog::query()->create([
            'user_id'     => $me->id,
            'action'      => 'user.deleted',
            'description' => "User {$user->name} ({$user->email}) dihapus.",
            'context'     => ['user_id' => $user->id],
        ]);

        $user->delete();

        return response()->json(['message' => 'User dihapus.']);
    }

    private function userPayload(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'is_online'  => $user->is_online,
            'last_login' => $user->last_login?->format('d M H:i'),
        ];
    }

    private function workspacePayload(?int $activeTicketId = null): array
    {
        $tickets = Ticket::query()
            ->with([
                'customer',
                'assignedAgent',
                'messages' => fn ($query) => $query->with('agent'),
            ])
            ->whereNull('archived_at')
            ->orderByDesc('last_message_at')
            ->get();

        $activeId = $activeTicketId ?? $tickets->first()?->id;

        return [
            'currentAgent' => $this->agentPayload($this->currentAgent()),
            'stats' => [
                'open' => $tickets->where('status', 'open')->count(),
                'pending' => $tickets->where('status', 'pending')->count(),
                'on_progress' => $tickets->where('status', 'on_progress')->count(),
                'closed' => $tickets->where('status', 'closed')->count(),
                'active_agents' => User::query()->where('role', 'agent')->where('is_online', true)->count(),
            ],
            'tickets' => $tickets->map(fn (Ticket $ticket) => $this->ticketListItem($ticket))->values(),
            'activeTicket' => $activeId ? $this->ticketPayload($activeId, $tickets) : null,
            'quickDrafts' => $activeId ? $this->ticketPayload($activeId, $tickets)['suggestions'] : [],
            'availableAgents' => User::query()
                ->where('role', 'agent')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => $this->agentPayload($user))
                ->values(),
        ];
    }

    private function ticketPayload(int $ticketId, $ticketCollection = null): array
    {
        $ticket = $ticketCollection?->firstWhere('id', $ticketId) ?? Ticket::query()
            ->with([
                'customer',
                'assignedAgent',
                'messages' => fn ($query) => $query->with('agent'),
            ])
            ->findOrFail($ticketId);

        $suggestions = $this->suggestionsForTicket($ticket);
        $knowledgeMatches = KnowledgeBase::query()
            ->orderByDesc('last_synced_at')
            ->get()
            ->filter(fn (KnowledgeBase $article) => $this->matchesTicket($ticket, $article))
            ->take(3)
            ->values();

        $auditLogs = AuditLog::query()
            ->with('user')
            ->where('context->ticket_id', $ticket->id)
            ->orWhere(function ($query) use ($ticket) {
                $query->where('description', 'like', "%#{$ticket->id}%");
            })
            ->latest()
            ->take(5)
            ->get();

        return [
            'id'            => $ticket->id,
            'subject'       => $ticket->subject,
            'needs_subject' => blank($ticket->subject),
            'status'        => $ticket->status,
            'priority'      => $ticket->priority,
            'channel'       => $ticket->channel,
            'sla_deadline' => optional($ticket->sla_deadline)?->toIso8601String(),
            'last_message_at' => optional($ticket->last_message_at)?->toIso8601String(),
            'customer' => [
                'id' => $ticket->customer->id,
                'name' => $ticket->customer->name,
                'phone_number' => $ticket->customer->phone_number,
            ],
            'assigned_agent' => $ticket->assignedAgent ? $this->agentPayload($ticket->assignedAgent) : null,
            'messages' => $ticket->messages->map(fn (Message $message) => [
                'id'                      => $message->id,
                'sender_type'             => $message->sender_type,
                'content'                 => $message->content,
                'agent_name'              => $message->agent?->name,
                'media_url'               => $message->media_url,
                'media_type'              => $message->media_type,
                'is_internal_note'        => $message->is_internal_note,
                'sent_at'                 => $message->sent_at->toIso8601String(),
            ])->values(),
            'suggestions' => $suggestions,
            'knowledge' => $knowledgeMatches->map(fn (KnowledgeBase $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'source' => $article->source,
                'last_synced_at' => optional($article->last_synced_at)?->toIso8601String(),
            ])->values(),
            'audit_logs' => $auditLogs->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user_name' => $log->user?->name,
                'created_at' => $log->created_at->toIso8601String(),
            ])->values(),
        ];
    }

    private function ticketListItem(Ticket $ticket): array
    {
        $latestMessage = $ticket->messages->sortByDesc('sent_at')->first();

        return [
            'id'                   => $ticket->id,
            'customer_name'        => $ticket->customer->name,
            'customer_phone'       => $ticket->customer->phone_number,
            'subject'              => $ticket->subject,
            'needs_subject'        => blank($ticket->subject),
            'status'               => $ticket->status,
            'priority'             => $ticket->priority,
            'assigned_agent_name'  => $ticket->assignedAgent?->name,
            'last_message_preview' => $latestMessage?->content ?: 'Media attachment',
            'last_message_at'      => optional($ticket->last_message_at)->toIso8601String(),
            'is_sla_risk'          => $ticket->sla_deadline && $ticket->sla_deadline->isBefore(now()->addHour()),
        ];
    }

    private function suggestionsForTicket(Ticket $ticket): array
    {
        // Build ticket context: subject + last 5 customer messages
        $lastMessages = $ticket->messages
            ->where('sender_type', 'customer')
            ->sortByDesc('sent_at')
            ->take(5)
            ->reverse()
            ->pluck('content')
            ->filter()
            ->map(fn (string $c) => '- ' . mb_substr($c, 0, 300))
            ->implode("\n");

        $subject        = $ticket->subject ? "Ticket subject: {$ticket->subject}" : 'Ticket subject: (no subject yet)';
        $ticketContext  = $lastMessages
            ? "{$subject}\n\nRecent customer messages:\n{$lastMessages}"
            : "{$subject}\n\n(No customer messages yet)";

        // Build RAG context: matching KB articles, fallback to all KB articles (up to 5)
        $allArticles = KnowledgeBase::all();
        $matched     = $allArticles->filter(fn (KnowledgeBase $a) => $this->matchesTicket($ticket, $a));
        $sopArticles = $matched->count() > 0 ? $matched->take(5) : $allArticles->take(5);

        $sopContext = $sopArticles->map(fn (KnowledgeBase $a) =>
            "Title: {$a->title}\n{$a->content}"
        )->implode("\n\n");

        if (! $sopContext) {
            return [];
        }

        return app(AiService::class)->suggestReplies($ticketContext, $sopContext);
    }

    private function matchesTicket(Ticket $ticket, KnowledgeBase $article): bool
    {
        $context = strtolower($ticket->subject.' '.$ticket->messages->pluck('content')->implode(' '));
        $knowledge = strtolower($article->title.' '.$article->content);

        foreach (['vpn', 'password', 'akses', 'drive'] as $keyword) {
            if (str_contains($context, $keyword) && str_contains($knowledge, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function currentAgent(): User
    {
        $user = Auth::user();

        if ($user instanceof User) {
            return $user;
        }

        return User::query()
            ->where('role', 'agent')
            ->orderByDesc('is_online')
            ->orderBy('name')
            ->first()
            ?? throw new RuntimeException('No agent record found.');
    }

    private function agentPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_online' => $user->is_online,
            'last_login' => optional($user->last_login)->toIso8601String(),
        ];
    }
}
