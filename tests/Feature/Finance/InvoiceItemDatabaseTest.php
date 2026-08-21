<?php

namespace Tests\Feature\Finance;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class InvoiceItemDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_items_table_exists()
    {
        $this->assertTrue(
            Schema::hasTable('invoice_items')
        );
    }
}
