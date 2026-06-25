<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'family_id', 'created_by', 'title', 'type',
        'event_date', 'location', 'description', 'notify_members',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'notify_members' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
