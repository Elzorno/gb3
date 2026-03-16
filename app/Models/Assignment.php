<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'day',
        'kid_id',
        'slot_id',
        'status',
        'submission_id',
    ];

    protected $casts = [
        'day' => 'date:Y-m-d',
    ];

    public function kid(): BelongsTo
    {
        return $this->belongsTo(Kid::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ChoreSlot::class, 'slot_id');
    }
}
