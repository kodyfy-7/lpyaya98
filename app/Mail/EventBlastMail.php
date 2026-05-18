<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventBlastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $blastSubject,
        public readonly string $htmlContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->blastSubject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event_blast',
            with: ['htmlContent' => $this->htmlContent],
        );
    }
}