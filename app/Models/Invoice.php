<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 'UNPAID';
    public const STATUS_PARTIAL = 'PARTIAL';
    public const STATUS_PAID = 'PAID';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'student_id','academic_year_id','invoice_no','issued_date','due_date',
        'subtotal','discount_total','total_amount','status'
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
}
