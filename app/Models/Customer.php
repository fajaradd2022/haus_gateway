<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'phone_number',
    'company',
    'email',
    'notes',
    'avatar_url',
    'is_vip',
    'is_blocked',
    'last_contact_at',
])]
class Customer extends Model
{
    protected function casts(): array
    {
        return [
            'is_vip'           => 'boolean',
            'is_blocked'       => 'boolean',
            'last_contact_at'  => 'datetime',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->filter()->take(2)
            ->map(fn ($w) => strtoupper($w[0]))
            ->implode('');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone_number', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%");
        });
    }
}
