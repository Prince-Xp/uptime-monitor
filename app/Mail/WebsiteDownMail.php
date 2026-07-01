<?php

namespace App\Mail;

use App\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WebsiteDownMail extends Mailable
{
    use Queueable;

    public function __construct(public Website $website)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: "{$this->website->url} is down!",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.website-down',
            with: ['url' => $this->website->url],
        );
    }
}