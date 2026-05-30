<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /** GET /api/contacts — search/list contacts (customers) */
    public function index(Request $request): JsonResponse
    {
        $q = $request->string('q')->trim()->toString();

        $contacts = Customer::query()
            ->when($q, fn ($query) => $query->search($q))
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn (Customer $c) => $this->contactPayload($c));

        return response()->json(['contacts' => $contacts]);
    }

    /** POST /api/contacts — create or find contact */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:191'],
            'phone_number' => ['required', 'string', 'max:30'],
            'company'      => ['nullable', 'string', 'max:191'],
            'email'        => ['nullable', 'email', 'max:191'],
            'notes'        => ['nullable', 'string'],
            'is_vip'       => ['sometimes', 'boolean'],
        ]);

        $contact = Customer::firstOrCreate(
            ['phone_number' => $data['phone_number']],
            array_filter([
                'name'    => $data['name'],
                'company' => $data['company'] ?? null,
                'email'   => $data['email'] ?? null,
                'notes'   => $data['notes'] ?? null,
                'is_vip'  => $data['is_vip'] ?? false,
            ])
        );

        return response()->json(['contact' => $this->contactPayload($contact)], 201);
    }

    /** GET /api/contacts/{customer} — detail + ticket history */
    public function show(Customer $customer): JsonResponse
    {
        $tickets = $customer->tickets()
            ->with(['assignedAgent', 'messages' => fn ($q) => $q->latest('sent_at')->limit(1)])
            ->whereNull('archived_at')
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (Ticket $t) => [
                'id'               => $t->id,
                'subject'          => $t->subject,
                'status'           => $t->status,
                'priority'         => $t->priority,
                'category'         => $t->category,
                'last_message_at'  => optional($t->last_message_at)->toIso8601String(),
                'assigned_agent'   => $t->assignedAgent?->name,
                'last_message'     => $t->messages->first()?->content,
                'archived_at'      => optional($t->archived_at)->toIso8601String(),
            ]);

        return response()->json([
            'contact' => $this->contactPayload($customer),
            'tickets' => $tickets,
        ]);
    }

    /** DELETE /api/contacts/{customer} — permanently delete contact and its tickets */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->tickets()->each(fn (Ticket $t) => $t->messages()->delete());
        $customer->tickets()->delete();
        $customer->delete();

        return response()->json(['ok' => true]);
    }

    /** PATCH /api/contacts/{customer} — update contact details */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name'    => ['sometimes', 'string', 'max:191'],
            'company' => ['nullable', 'string', 'max:191'],
            'email'   => ['nullable', 'email', 'max:191'],
            'notes'   => ['nullable', 'string'],
            'is_vip'  => ['sometimes', 'boolean'],
            'is_blocked' => ['sometimes', 'boolean'],
        ]);

        $customer->update($data);

        return response()->json(['contact' => $this->contactPayload($customer->fresh())]);
    }

    /** POST /api/chats/new — create ticket from contact (New Chat) */
    public function newChat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'contact_id' => ['required', 'exists:customers,id'],
            'subject'    => ['required', 'string', 'max:255'],
            'category'   => ['nullable', 'string', 'max:100'],
            'priority'   => ['nullable', 'in:low,medium,high,urgent'],
        ]);

        $agent = Auth::user();

        $ticket = Ticket::query()->create([
            'customer_id'       => $data['contact_id'],
            'assigned_agent_id' => $agent->id,
            'subject'           => $data['subject'],
            'category'          => $data['category'] ?? null,
            'status'            => 'open',
            'priority'          => $data['priority'] ?? 'medium',
            'channel'           => 'whatsapp',
            'last_message_at'   => now(),
            'sla_deadline'      => now()->addHours(2),
        ]);

        // Update customer last_contact_at
        Customer::query()->where('id', $data['contact_id'])
            ->update(['last_contact_at' => now()]);

        AuditLog::query()->create([
            'user_id'     => $agent->id,
            'action'      => 'ticket.created',
            'description' => "Tiket #{$ticket->id} dibuat manual oleh {$agent->name}.",
            'context'     => ['ticket_id' => $ticket->id],
        ]);

        // Return workspace with new ticket as active
        $helpdeskController = app(HelpdeskController::class);
        $workspace = $helpdeskController->workspacePayloadPublic($ticket->id);

        return response()->json(['workspace' => $workspace], 201);
    }

    private function contactPayload(Customer $c): array
    {
        return [
            'id'              => $c->id,
            'name'            => $c->name,
            'phone_number'    => $c->phone_number,
            'company'         => $c->company,
            'email'           => $c->email,
            'notes'           => $c->notes,
            'avatar_url'      => $c->avatar_url,
            'is_vip'          => $c->is_vip,
            'is_blocked'      => $c->is_blocked,
            'last_contact_at' => optional($c->last_contact_at)->toIso8601String(),
            'initials'        => $c->initials,
        ];
    }
}
