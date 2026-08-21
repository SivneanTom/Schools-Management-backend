<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'student_fee_id',
        'description_km',
        'description_en',
        'quantity',
        'unit_amount',
        'discount_amount',
        'line_total'
    ];

    protected $casts = [
        'quantity'=>'decimal:2',
        'unit_amount'=>'decimal:2',
        'discount_amount'=>'decimal:2',
        'line_total'=>'decimal:2'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function studentFee()
    {
        return $this->belongsTo(StudentFee::class);
    }
}
