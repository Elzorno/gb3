<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'kid_id',
        'status',
        'requested_cents',
        'requested_phone_min',
        'requested_games_min',
        'requested_other_min',
        'requested_at',
        'reviewed_at',
        'reviewed_by_actor_type',
        'reviewed_by_actor_id',
        'review_note',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }

    /**
     * Check if any payout amount was requested.
     */
    public function hasAmount(): bool
    {
        return $this->requested_cents > 0
            || $this->requested_phone_min > 0
            || $this->requested_games_min > 0
            || $this->requested_other_min > 0;
    }
}
