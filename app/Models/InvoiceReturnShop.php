<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_return_id
 * @property string $shop
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read InvoiceReturn $invoiceReturn
 *
 * @mixin Builder
 */
class InvoiceReturnShop extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo
     */
    public function invoiceReturn(): BelongsTo
    {
        return $this->belongsTo(InvoiceReturn::class);
    }
}
