<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TrainingCycle;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingBlock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'training_cycle_id',
        'weeks',
        'order',
    ];

    public function trainingCycle()
    {
        return $this->belongsTo(TrainingCycle::class);
    }

    public function trainingDays(): HasMany
    {
        return $this->hasMany(TrainingDay::class);
    }
}
