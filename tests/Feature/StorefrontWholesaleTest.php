<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontWholesaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'cache.limiter' => 'array']);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
    }

    public function test_public_wholesale_enquiry_is_saved_for_the_existing_admin(): void
    {
        $message = "Type of business: Retailer\n\nPlease share product availability.";
        $this->postJson('/api/wholesale-enquiry', ['name' => 'Test buyer', 'email' => 'buyer@example.test',
            'phone' => '+919000000001', 'company_name' => 'Isolated business', 'message' => $message])
            ->assertCreated()->assertJson(['status' => 'success']);
        $row = DB::table('wholesale_enquiries')->sole();
        $this->assertSame('Test buyer', $row->name);
        $this->assertSame('buyer@example.test', $row->email);
        $this->assertSame('+919000000001', $row->phone);
        $this->assertSame('Isolated business', $row->company_name);
        $this->assertSame($message, $row->message);
        $this->assertSame(0, strcasecmp('pending', $row->status));
    }

    public function test_invalid_wholesale_enquiry_does_not_write_a_record(): void
    {
        $this->postJson('/api/wholesale-enquiry', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone', 'message']);
        $this->assertDatabaseCount('wholesale_enquiries', 0);
    }
}
