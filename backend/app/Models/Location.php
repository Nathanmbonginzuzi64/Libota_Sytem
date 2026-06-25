<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $fillable = [
        'family_id', 'family_member_id', 'name', 'type',
        'province', 'country', 'latitude', 'longitude', 'year', 'description',
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
