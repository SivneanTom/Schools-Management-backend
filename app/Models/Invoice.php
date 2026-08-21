<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'invoice_no',
        'issued_date',
        'due_date',
        'subtotal',
        'discount_total',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'issued_date'=>'date',
        'due_date'=>'date',
        'subtotal'=>'decimal:2',
        'discount_total'=>'decimal:2',
        'total_amount'=>'decimal:2'
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function academicYear(){
        return $this->belongsTo(AcademicYear::class);
    }

    public function items(){
        return $this->hasMany(InvoiceItem::class);
    }
}
