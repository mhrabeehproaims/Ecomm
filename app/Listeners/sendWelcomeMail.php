<?php

namespace App\Listeners;

use App\Events\newCustomerCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\UserRegEmail;
use Illuminate\Support\Facades\Mail;

class sendWelcomeMail
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
    public function handle(newCustomerCreatedEvent $event): void
    {
        Mail::to($event->user->email)->send(new UserRegEmail($event->user->fullname));
    }
}
