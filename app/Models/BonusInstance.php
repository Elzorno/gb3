<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start',
        'bonus_def_id',
        'status',
        'claimed_by_kid_id',
        'claimed_at',
        'submission_id',
    ];

    protected $casts = [
        'week_start' => 'date:Y-m-d',
        'claimed_at' => 'datetime',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(BonusDef::class, 'bonus_def_id');
    }

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class, 'claimed_by_kid_id');
    }
}
