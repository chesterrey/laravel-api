<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TrainingBlock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingDay extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'training_block_id',
        'day',
        'name',
    ];

    public function trainingBlock()
    {
        return $this->belongsTo(TrainingBlock::class);
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(Week::class);
    }
}
