<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket_id',
    'sender_type',
    'content',
    'agent_id',
    'media_url',
    'media_type',
    'sent_at',
    'is_internal_note',
    'waha_message_id',
])]
class Message extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'sent_at'          => 'datetime',
            'is_internal_note' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
