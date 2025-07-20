<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $unit
 * @property int $sort
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Ingredient extends Model
{
    use HasFactory;

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

    protected $fillable = [
        'name',
        'unit',
        'sort',
        'is_active',
    ];
}
