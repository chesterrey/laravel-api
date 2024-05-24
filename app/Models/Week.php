<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Week extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_number',
        'training_day_id',
        'deload',
    ];

    public function trainingDay(): BelongsTo
    {
        return $this->belongsTo(TrainingDay::class);
    }
}
