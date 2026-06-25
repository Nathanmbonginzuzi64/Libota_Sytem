<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyMember extends Model
{
    protected $fillable = [
        'family_id',
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'death_date',
        'father_id',
        'mother_id',
        'is_adopted',
        'biography',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
            'is_adopted' => 'boolean',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function father(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'father_id');
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'mother_id');
    }

    public function childrenAsFather(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'father_id');
    }

    public function childrenAsMother(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'mother_id');
    }
}
