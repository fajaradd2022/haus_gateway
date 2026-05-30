<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'content', 'source', 'last_synced_at'])]
class KnowledgeBase extends Model
{
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }
}
