<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'product_id',
        'carts',
    ];

    protected $casts = [
        'date' => 'date',
        'carts' => 'decimal:2',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
