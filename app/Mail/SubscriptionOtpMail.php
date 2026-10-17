<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $displayName,
        public ?string $planName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Votre code de validation CIOMAG Premium : {$this->code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-otp',
        );
    }
}
