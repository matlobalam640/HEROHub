<?php

namespace App\Mail\Membership;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserMembershipEventMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $detailLines
     */
    public function __construct(
        public User $user,
        public string $subjectLine,
        public string $headline,
        public ?string $membershipNumber = null,
        public ?string $planName = null,
        public array $detailLines = [],
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
        public ?string $footerNote = null,
        public ?int $daysUntilRenewal = null,
        public ?string $renewalDate = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.membership.user-event',
            text: 'mail.membership.user-event-plain',
            with: [
                'user' => $this->user,
                'subject' => $this->subjectLine,
                'headline' => $this->headline,
                'membershipNumber' => $this->membershipNumber,
                'planName' => $this->planName,
                'detailLines' => $this->detailLines,
                'actionUrl' => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
                'footerNote' => $this->footerNote,
                'daysUntilRenewal' => $this->daysUntilRenewal,
                'renewalDate' => $this->renewalDate,
            ],
        );
    }
}
