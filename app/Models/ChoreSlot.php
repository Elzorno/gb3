<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChoreSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'bool',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'slot_id');
    }
}
