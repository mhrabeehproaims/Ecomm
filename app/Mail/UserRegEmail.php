<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserRegEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $fullname;
    // public $address;
    // public $phone;
    // public $city;
    // public $state;
    // public $country;
    // public $zip;
    public function __construct($userName)
    {
        $this->fullname = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Vantara welcomes you, {$this->fullname}!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'Mail.user_registered',
            with:[
                'full_name' => $this->fullname,
                // 'phone' => $this->phone,
                // 'city' => $this->city,
                // 'state' => $this->state,
                // 'country' => $this->country,
                // 'zip' => $this->zip,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
