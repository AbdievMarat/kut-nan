<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreadRemain extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'product_id',
        'amount',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
