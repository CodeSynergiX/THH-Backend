<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Tribal Helping Hand login',
        );
    }

    public function content(): Content
    {
        $loginUrl = url('/login');

        return new Content(
            htmlString: '<p>An account was created so you can track your applications.</p><p><strong>Email:</strong> '
                .e($this->user->email ?? '')
                .'</p><p><strong>Temporary password:</strong> '
                .e($this->plainPassword)
                .'</p><p>Sign in at <a href="'.e($loginUrl).'">'.$loginUrl.'</a>. If you forget the password, use Forgot password or request an email OTP.</p>',
        );
    }
}
