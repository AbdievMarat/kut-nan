<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_batch_id
 * @property int $ingredient_id
 * @property float $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read ProductBatch $productBatch
 * @property-read Ingredient $ingredient
 */
class ProductBatchIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_batch_id',
        'ingredient_id',
        'amount',
    ];

    /**
     * @return BelongsTo
     */
    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    /**
     * @return BelongsTo
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
