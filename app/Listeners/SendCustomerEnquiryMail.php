<?php

namespace App\Listeners;

use App\Events\NewEnquiryEvent;
use App\Mail\SendEnquiryMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendCustomerEnquiryMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewEnquiryEvent $event): void
    {
        Mail::to('vantharawayanad@gmail.com')->send(new SendEnquiryMail($event->message_data));
    }
}
