<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $bus_id
 * @property int $product_id
 * @property integer $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Bus $bus
 * @property-read Product $product
 *
 * @mixin Builder
 */
class BusProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'product_id',
        'price',
    ];

    /**
     * @return BelongsTo
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
