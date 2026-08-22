<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'receipt_no',
        'issued_at',
        'file_url',
    ];


    protected $casts = [
        'issued_at' => 'date',
    ];


    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}