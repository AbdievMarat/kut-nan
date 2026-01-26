<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $bus_id
 * @property Carbon $date
 * @property int|null $old_amount
 * @property int|null $new_amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Order $order
 * @property-read Product $product
 * @property-read Bus $bus
 *
 * @mixin Builder
 */
class OrderChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'bus_id',
        'date',
        'old_amount',
        'new_amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }
}
