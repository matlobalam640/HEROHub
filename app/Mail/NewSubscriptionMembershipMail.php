<?php

namespace App\Mail;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSubscriptionMembershipMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Membership $membership,
        public bool $needsPasswordSetup,
        public ?string $passwordResetUrl = null,
        public ?string $planName = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your HERO membership is ready — sign in and next steps',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.subscription.new-membership',
            text: 'mail.subscription.new-membership-plain',
        );
    }
}
