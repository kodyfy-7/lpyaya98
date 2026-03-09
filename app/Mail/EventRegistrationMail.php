<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $regNumber,
        public array $eventDetails, // ['name', 'date', 'time', 'location']
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Event Registration Confirmed — {$this->eventDetails['name']}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-registration',
        );
    }
}
