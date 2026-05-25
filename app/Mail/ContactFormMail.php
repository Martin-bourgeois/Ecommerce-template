<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $contactSubject,
        public string $contactMessage,
        public bool $wantsNewsletter = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau message de contact: ' . $this->contactSubject,
            from: $this->email,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'subject' => $this->contactSubject,
                'contactMessage' => $this->contactMessage,
                'wantsNewsletter' => $this->wantsNewsletter,
            ],
        );
    }
}
