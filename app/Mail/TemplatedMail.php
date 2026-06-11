<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyContent,
        public string $templateKey = '',
        public bool $isHtml = false
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        // HTML şablonlarda içerik olduğu gibi gönderilir;
        // düz metin şablonlarda kaçışlanıp satır sonları <br>'a çevrilir.
        return new Content(
            htmlString: $this->isHtml ? $this->bodyContent : nl2br(e($this->bodyContent)),
        );
    }
}
