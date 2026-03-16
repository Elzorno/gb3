<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfractionDef extends Model
{
    use HasFactory;

    protected $table = 'infraction_defs';

    protected $fillable = [
        'code',
        'label',
        'active',
        'mode',
        'days',
        'ladder_json',
        'blocks_json',
        'repairs_json',
        'review_days',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
