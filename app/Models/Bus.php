<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $license_plate
 * @property string $serial_number
 * @property int $sort
 * @property boolean $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Order|null $orders
 * @property-read Remainder|null $remainders
 *
 * @mixin Builder
 */
class Bus extends Model
{
    use HasFactory;

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

    protected $fillable = [
        'license_plate',
        'serial_number',
        'sort',
        'is_active',
    ];

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany
     */
    public function remainders(): HasMany
    {
        return $this->hasMany(Remainder::class);
    }
}