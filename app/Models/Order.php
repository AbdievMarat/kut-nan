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
 * @property int|null $product_1 Москва
 * @property int|null $product_2 Москва уп
 * @property int|null $product_3 Солдат
 * @property int|null $product_4 Отруб
 * @property int|null $product_5 Налив
 * @property int|null $product_6 Тостер
 * @property int|null $product_7 Тостер кара
 * @property int|null $product_8 Мини тостер
 * @property int|null $product_9 Гречневый
 * @property int|null $product_10 Зерновой
 * @property int|null $product_11 Багет
 * @property int|null $product_12 Без дрожж
 * @property int|null $product_13 Чемпион
 * @property int|null $product_14 Абсолют
 * @property int|null $product_15 Кукурузный
 * @property int|null $product_16 Уп. Бород
 * @property int|null $product_17 Уп. Батон отруб
 * @property int|null $product_18 Уп. Батон серый
 * @property int|null $product_19 Уп. Батон белый
 * @property int|null $product_20 Баатыр
 * @property int|null $product_21 Обама отруб
 * @property int|null $product_22 Обама ржан
 * @property int|null $product_23 Обама серый
 * @property int|null $product_24 Уп. Моск
 * @property int|null $product_25 Гамбургер
 * @property int|null $product_26 Тартин
 * @property int|null $product_27 Тартин зерновой
 * @property int|null $product_28 Тартин ржаной
 * @property int|null $product_29 Тартин с луком
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

    use HasFactory;

    protected $fillable = [
        'product_1',
        'product_2',
        'product_3',
        'product_4',
        'product_5',
        'product_6',
        'product_7',
        'product_8',
        'product_9',
        'product_10',
        'product_11',
        'product_12',
        'product_13',
        'product_14',
        'product_15',
        'product_16',
        'product_17',
        'product_18',
        'product_19',
        'product_20',
        'product_21',
        'product_22',
        'product_23',
        'product_24',
        'product_25',
        'product_26',
        'product_27',
        'product_28',
        'product_29',
    ];

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
