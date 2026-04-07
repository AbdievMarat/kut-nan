<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property string $shop
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Invoice $invoice
 *
 * @mixin Builder
 */
class InvoiceShop extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
