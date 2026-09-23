<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public string $purpose = 'login') {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Your one-time code is <strong>'.e($this->code).'</strong>.</p><p>It expires in 10 minutes. Purpose: '.e($this->purpose).'.</p>',
        );
    }
}
