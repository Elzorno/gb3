<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'kid_id',
        'type',
        'source',
        'source_id',
        'cents',
        'phone_min',
        'games_min',
        'other_min',
        'note',
        'actor_type',
        'actor_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }
}
