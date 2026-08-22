<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'name_km', 'name_en', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
