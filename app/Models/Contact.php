<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'avatar_url',
        'company',
        'department',
        'job_title',
        'wa_id',
        'wa_push_name',
        'source',
        'last_seen_at',
        'is_wa_verified',
        'is_vip',
        'is_blocked',
        'sla_override_minutes',
        'notes',
        'total_tickets',
        'open_tickets',
        'first_contact_at',
        'last_contact_at',
    ];

    protected $casts = [
        'last_seen_at'      => 'datetime',
        'first_contact_at'  => 'datetime',
        'last_contact_at'   => 'datetime',
        'is_wa_verified'    => 'boolean',
        'is_vip'            => 'boolean',
        'is_blocked'        => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────

    /** All tickets linked to this contact (new FK). */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'contact_id');
    }

    /** Additional phone numbers for this contact. */
    public function phones(): HasMany
    {
        return $this->hasMany(ContactPhone::class);
    }

    /** Tags applied to this contact (many-to-many). */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'contact_tag');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    public function scopeNotBlocked($query)
    {
        return $query->where('is_blocked', false);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone_number', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%");
        });
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->filter()
            ->take(2)
            ->map(fn ($w) => strtoupper($w[0]))
            ->implode('');
    }
}
