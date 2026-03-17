<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'kind',
        'day',
        'week_start',
        'kid_id',
        'slot_id',
        'bonus_instance_id',
        'proof_path',
        'status',
        'submitted_at',
        'reviewed_at',
        'reviewed_by_admin_id',
        'review_note',
    ];

    protected $casts = [
        'day' => 'date:Y-m-d',
        'week_start' => 'date:Y-m-d',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ChoreSlot::class, 'slot_id');
    }

    public function bonusInstance(): BelongsTo
    {
        return $this->belongsTo(BonusInstance::class, 'bonus_instance_id');
    }
}
