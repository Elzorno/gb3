<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusDef extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'active',
        'reward_cents',
        'reward_phone_min',
        'reward_games_min',
        'max_per_week',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'bool',
    ];

    public function instances(): HasMany
    {
        return $this->hasMany(BonusInstance::class, 'bonus_def_id');
    }
}
