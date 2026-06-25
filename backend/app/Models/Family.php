<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = [
        'name',
        'clan_id',
        'created_by',
        'visibility',
        'description',
        'origin_province',
    ];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'status')->withTimestamps();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function oralMemories(): HasMany
    {
        return $this->hasMany(OralMemory::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
