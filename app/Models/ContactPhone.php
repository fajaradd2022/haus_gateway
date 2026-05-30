<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPhone extends Model
{
    protected $fillable = ['contact_id', 'phone_number', 'label', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
