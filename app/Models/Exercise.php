<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_id',
        'name',
        'strength',
        'rpe',
        'muscle_group'
    ];

    protected $casts = [
        'strength' => 'boolean',
    ];

    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class);
    }

    public function sets(): HasMany
    {
        return $this->hasMany(ExerciseSet::class);
    }
}
