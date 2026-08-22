<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class PaymentDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_tables_exist()
    {
        $this->assertTrue(Schema::hasTable('payment_methods'));
        $this->assertTrue(Schema::hasTable('payments'));
    }
}
