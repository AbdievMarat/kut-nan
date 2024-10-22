<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $price
 * @property int $sort
 * @property boolean $is_active
 * @property boolean $is_in_report
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Product extends Model
{
    use HasFactory;

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

    const IS_IN_REPORT = 1;
    const IS_NOT_IN_REPORT = 0;

    protected $fillable = [
        'name',
        'price',
        'sort',
        'is_active',
        'is_in_report',
    ];
}
