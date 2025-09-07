<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property string $unit
 * @property int $price
 * @property int $sort
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read BelongsToMany $products
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
        'short_name',
        'unit',
        'price',
        'sort',
        'is_active',
    ];

    /**
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_ingredients', 'ingredient_id', 'product_id')
            ->withPivot('formula')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function usages(): HasMany
    {
        return $this->hasMany(IngredientUsage::class);
    }
}
