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
 * @property-read MarkdownItem|null $items
 *
 * @mixin Builder
 */
class Markdown extends Model
{
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
        return $this->hasMany(MarkdownItem::class);
    }
}
