<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property string $date
 * @property float $quantity_cart Количество тележек в партии, кратное 0.5
 * @property int $quantity_total Количество штук в партии
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Product $product
 * @property-read ProductBatchIngredient $productBatchIngredients
 */
class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'date',
        'quantity_cart',
        'quantity_total',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany
     */
    public function productBatchIngredients(): HasMany
    {
        return $this->hasMany(ProductBatchIngredient::class);
    }
}
