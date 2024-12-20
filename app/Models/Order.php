<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $bus_id
 * @property Carbon $date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Bus $bus
 * @property-read OrderItem|null $items
 *
 * @mixin Builder
 */
class Order extends Model
{
    const TYPE_OPERATION_ORDER = 1;
    const TYPE_OPERATION_REALIZATION = 2;
    const TYPE_OPERATION_REMAINDER = 3;
    const TYPE_OPERATION_MARKDOWN = 4;

    use HasFactory;

    /**
     * @return BelongsTo
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
