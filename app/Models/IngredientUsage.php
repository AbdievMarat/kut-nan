<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ingredient_id
 * @property string $date
 * @property float $income Приход
 * @property float $usage Расход с произведенных продуктов
 * @property float $usage_missing Расход "не хватает"
 * @property float $usage_taken_from_stock Расход "забрали со склада"
 * @property float $usage_kitchen Расход "кухня"
 * @property float $stock Остаток после движения, формула Приход + Расход - остаток прежнего дня
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class IngredientUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'date',
        'income',
        'usage',
        'usage_missing',
        'usage_taken_from_stock',
        'usage_kitchen',
        'stock',
    ];

    /**
     * @return BelongsTo
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
