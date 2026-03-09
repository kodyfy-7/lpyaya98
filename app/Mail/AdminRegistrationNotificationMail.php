<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRegistrationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $user, // ['name', 'email']
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New User Registration — LP98YAYA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-registration-notification',
        );
    }
}
