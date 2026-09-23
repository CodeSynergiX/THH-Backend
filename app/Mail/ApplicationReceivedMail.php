<?php

namespace App\Mail;

use App\Domains\Cases\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application received — '.$this->application->case_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p>Your help request has been registered.</p><p><strong>Case number:</strong> '
                .e($this->application->case_no)
                .'</p><p><strong>Title:</strong> '
                .e($this->application->title)
                .'</p><p>Status: received. We will update this email when staff review the case. Log in to track progress.</p>',
        );
    }
}
