<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kid extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
        'pin_hash',
        'sort_order',
    ];

    /**
     * Accessor for name (alias of display_name).
     */
    public function getNameAttribute(): string
    {
        return $this->display_name;
    }

    /**
     * Whether the kid currently has any active privilege lock.
     */
    public function getIsGroundedAttribute(): bool
    {
        $priv = $this->privileges;
        if (!$priv) {
            return false;
        }

        return $priv->phone_locked || $priv->games_locked || $priv->other_locked;
    }

    public function privileges(): HasOne
    {
        return $this->hasOne(Privilege::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
