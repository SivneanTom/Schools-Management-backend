<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;


    public const STATUS_PENDING = 'PENDING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'invoice_id',
        'payment_method_id',
        'received_by_staff_id',
        'payment_no',
        'amount',
        'paid_at',
        'reference_no',
        'status',
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
    public function invoice()
    {
        return $this->belongsTo(
            Invoice::class
        );
    }
    public function paymentMethod()
    {
        return $this->belongsTo(
            PaymentMethod::class
        );
    }
    public function receivedBy()
    {
        return $this->belongsTo(
            Staff::class,
            'received_by_staff_id'
        );
    }
}