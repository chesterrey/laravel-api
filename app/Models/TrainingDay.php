<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TrainingBlock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
