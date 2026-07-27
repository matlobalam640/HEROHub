<?php

namespace App\Mail\Membership;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminMembershipEventMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $detailLines
     */
    public function __construct(
        public string $subjectLine,
        public string $headline,
        public array $detailLines = [],
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.membership.admin-event',
            text: 'mail.membership.admin-event-plain',
            with: [
                'subject' => $this->subjectLine,
                'headline' => $this->headline,
                'detailLines' => $this->detailLines,
                'actionUrl' => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
            ],
        );
    }
}
