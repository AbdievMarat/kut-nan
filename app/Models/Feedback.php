<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'is_anonymous',
        'full_name',
        'phone',
        'message',
        'is_send',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_send' => 'boolean',
    ];
}
