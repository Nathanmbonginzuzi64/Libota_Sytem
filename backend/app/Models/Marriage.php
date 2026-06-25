<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marriage extends Model
{
    protected $fillable = ['family_id', 'spouse_one_id', 'spouse_two_id', 'marriage_date', 'divorce_date'];

    protected function casts(): array
    {
        return [
            'marriage_date' => 'date',
            'divorce_date' => 'date',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
