<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $remainder_id
 * @property int $product_id
 * @property int $price
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Remainder $remainder
 * @property-read Product $product
 *
 * @mixin Builder
 */
class RemainderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'remainder_id',
        'product_id',
        'price',
        'amount',
    ];

    /**
     * @return BelongsTo
     */
    public function remainder(): BelongsTo
    {
        return $this->belongsTo(Remainder::class);
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}