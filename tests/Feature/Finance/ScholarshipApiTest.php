<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarshipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_percentage_scholarship(): void
    {
        $response = $this->postJson('/api/scholarships', [
            'name_km' => 'អាហារូបករណ៍សិស្សឆ្នើម',
            'name_en' => 'Merit Scholarship',
            'description_km' => 'បញ្ចុះតម្លៃសម្រាប់សិស្សឆ្នើម',
            'description_en' => 'Discount for high-performing students',
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 50,
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.discount_type', 'PERCENTAGE')
            ->assertJsonPath('data.discount_value', '50.00');

        $this->assertDatabaseHas('scholarships', [
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 50,
        ]);
    }

    public function test_percentage_discount_cannot_exceed_100(): void
    {
        $response = $this->postJson('/api/scholarships', [
            'name_km' => 'Test',
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 150,
        ]);

        $response->assertUnprocessable();
    }
}
