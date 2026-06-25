<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OralMemory extends Model
{
    protected $fillable = [
        'family_id', 'family_member_id', 'recorded_by', 'title',
        'narrator', 'media_type', 'file_path', 'transcription',
        'duration_seconds', 'language',
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'family_member_id');
    }
}
