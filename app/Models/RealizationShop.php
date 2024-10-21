<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $realization_id
 * @property string $shop Данные о магазине (название, адрес, контактный номер)
 * @property integer $amount Сумма оставленная на реализацию
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Realization $realization
 *
 * @mixin Builder
 */
class RealizationShop extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo
     */
    public function realization(): BelongsTo
    {
        return $this->belongsTo(Realization::class);
    }
}
