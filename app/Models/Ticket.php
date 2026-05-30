<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'contact_id',
    'assigned_agent_id',
    'subject',
    'status',
    'priority',
    'channel',
    'channel_ref',
    'category',
    'sla_deadline',
    'last_message_at',
    'archived_at',
    'resolved_at',
    'first_response_at',
    'response_time_seconds',
    'resolution_time_seconds',
])]
class Ticket extends Model
{
    protected function casts(): array
    {
        return [
            'sla_deadline'        => 'datetime',
            'last_message_at'     => 'datetime',
            'archived_at'         => 'datetime',
            'resolved_at'         => 'datetime',
            'first_response_at'   => 'datetime',
        ];
    }

    /** Legacy customer relationship (customers table). */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Enriched contact relationship (contacts table). */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('sent_at');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isSlaBreached(): bool
    {
        return $this->sla_deadline && $this->sla_deadline->isPast() && $this->status !== 'closed';
    }

    public function isSlaAtRisk(): bool
    {
        return $this->sla_deadline
            && $this->sla_deadline->isFuture()
            && $this->sla_deadline->isBefore(now()->addHour())
            && $this->status !== 'closed';
    }
}
