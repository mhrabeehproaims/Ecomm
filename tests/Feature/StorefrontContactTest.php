<?php

namespace Tests\Feature;

use App\Mail\SendEnquiryMail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StorefrontContactTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'cache.default' => 'array', 'cache.limiter' => 'array']);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--force' => true]);
        Mail::fake();
    }

    public function test_contact_endpoint_delivers_all_fields_using_the_existing_mail_workflow(): void
    {
        $this->postJson('/api/customer-enquiry', ['name' => 'Contact test', 'email' => 'contact@example.test',
            'phone' => '+919000000001', 'message' => 'An isolated sales enquiry.'])->assertOk()->assertJson(['status' => 'success']);
        Mail::assertSent(SendEnquiryMail::class, fn ($mail) => $mail->name === 'Contact test'
            && $mail->email === 'contact@example.test' && $mail->phone === '+919000000001'
            && $mail->message === 'An isolated sales enquiry.');
        Mail::assertSentCount(1);
    }

    public function test_invalid_contact_payload_does_not_send_a_message(): void
    {
        $this->postJson('/api/customer-enquiry', ['name' => 'Test', 'email' => 'invalid', 'phone' => 'bad', 'message' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'phone', 'message']);
        Mail::assertNothingSent();
    }
}
