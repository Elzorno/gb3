<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RotationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kids_json',
        'slots_json',
        'anchor_monday',
    ];

    protected $casts = [
        'anchor_monday' => 'date:Y-m-d',
    ];
}
