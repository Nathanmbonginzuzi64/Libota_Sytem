<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clan extends Model
{
    protected $fillable = ['name', 'region', 'description'];

    public function families(): HasMany
    {
        return $this->hasMany(Family::class);
    }
}
