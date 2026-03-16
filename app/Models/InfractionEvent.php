<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfractionEvent extends Model
{
    use HasFactory;

    protected $table = 'infraction_events';
    public $timestamps = false;

    protected $fillable = [
        'kid_id',
        'infraction_def_id',
        'ts',
        'actor_type',
        'actor_id',
        'strike_before',
        'strike_after',
        'days_applied',
        'mode',
        'blocks_json',
        'computed_until_json',
        'review_on',
        'note',
        'reviewed_at',
        'reviewed_by_actor_type',
        'reviewed_by_actor_id',
        'review_note',
        'review_action',
        'review_resolved_until_json',
    ];

    protected $casts = [
        'ts' => 'datetime',
        'review_on' => 'date:Y-m-d',
        'reviewed_at' => 'datetime',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(InfractionDef::class, 'infraction_def_id');
    }
}
