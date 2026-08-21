<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class InvoiceDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_table_exists()
    {
        $this->assertTrue(
            Schema::hasTable('invoices')
        );
    }
}
