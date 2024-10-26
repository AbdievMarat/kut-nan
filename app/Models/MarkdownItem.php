<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $markdown_id
 * @property int $product_id
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Markdown $markdown
 * @property-read Product $product
 *
 * @mixin Builder
 */
class MarkdownItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'markdown_id',
        'product_id',
        'amount',
    ];

    /**
     * @return BelongsTo
     */
    public function markdown(): BelongsTo
    {
        return $this->belongsTo(Markdown::class);
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
