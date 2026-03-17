<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Privilege extends Model
{
    use HasFactory;

    protected $primaryKey = 'kid_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'kid_id',
        'phone_locked',
        'games_locked',
        'other_locked',
        'bank_phone_min',
        'bank_games_min',
        'bank_other_min',
        'bank_cents',
        'phone_locked_until',
        'games_locked_until',
        'other_locked_until',
        'updated_at',
    ];

    protected $casts = [
        'phone_locked' => 'boolean',
        'games_locked' => 'boolean',
        'other_locked' => 'boolean',
        'phone_locked_until' => 'datetime',
        'games_locked_until' => 'datetime',
        'other_locked_until' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class, 'kid_id');
    }
}
