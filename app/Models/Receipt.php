<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = [
        'payment_id',
        'receipt_no',
        'issued_at',
        'file_url'
    ];

    protected $casts = [
        'issued_at' => 'datetime'
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(
            Payment::class
        );
    }
}