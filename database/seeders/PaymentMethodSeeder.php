<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            [
                'code' => 'ABA',
                'name_km' => 'ធនាគារ ABA',
                'name_en' => 'ABA Bank',
                'is_active' => true,
            ],
            [
                'code' => 'ACLEDA',
                'name_km' => 'ធនាគារ អេស៊ីលីដា',
                'name_en' => 'ACLEDA Bank',
                'is_active' => true,
            ],
            [
                'code' => 'WING',
                'name_km' => 'វីង',
                'name_en' => 'Wing Bank',
                'is_active' => true,
            ],
            [
                'code' => 'BAKONG',
                'name_km' => 'បាគង',
                'name_en' => 'Bakong',
                'is_active' => true,
            ],
            [
                'code' => 'KHQR',
                'name_km' => 'ខេអេចឃ្យូអារ',
                'name_en' => 'KHQR',
                'is_active' => true,
            ],
            [
                'code' => 'CANADIA',
                'name_km' => 'ធនាគារ កាណាឌីយ៉ា',
                'name_en' => 'Canadia Bank',
                'is_active' => true,
            ],
            [
                'code' => 'CHIP_MONG',
                'name_km' => 'ធនាគារ ជីប ម៉ុង',
                'name_en' => 'Chip Mong Bank',
                'is_active' => true,
            ],
            [
                'code' => 'PRINCE',
                'name_km' => 'ធនាគារ ព្រីនស៍',
                'name_en' => 'Prince Bank',
                'is_active' => true,
            ],
            [
                'code' => 'SATHAPANA',
                'name_km' => 'ធនាគារ ស្ថាបនា',
                'name_en' => 'Sathapana Bank',
                'is_active' => true,
            ],
            [
                'code' => 'PPCBANK',
                'name_km' => 'ធនាគារ ភ្នំពេញពាណិជ្ជ',
                'name_en' => 'PPCBank',
                'is_active' => true,
            ],
            [
                'code' => 'CASH',
                'name_km' => 'សាច់ប្រាក់',
                'name_en' => 'Cash',
                'is_active' => true,
            ],
            [
                'code' => 'BANK_TRANSFER',
                'name_km' => 'ផ្ទេរប្រាក់តាមធនាគារ',
                'name_en' => 'Bank Transfer',
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}