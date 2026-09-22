<?php

namespace App\Support;

use App\Mail\Membership\AdminMembershipEventMail;
use App\Mail\Membership\UserMembershipEventMail;
use App\Models\User;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PortalInvite
{
    public static function passwordSetupUrl(User $user): ?string
    {
        $passwordResetUrl = null;

        Password::broker()->sendResetLink(
            ['email' => $user->email],
            function (CanResetPassword $resetUser, string $token) use (&$passwordResetUrl): string {
                $passwordResetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $resetUser->getEmailForPasswordReset(),
                ], false));

                return Password::RESET_LINK_SENT;
            }
        );

        return $passwordResetUrl;
    }

    /**
     * @param  list<string>  $detailLines
     */
    public static function sendUserInvite(
        User $user,
        string $subject,
        string $headline,
        array $detailLines,
        ?string $actionUrl,
        string $actionLabel = 'Create your portal password',
        ?string $footerNote = null,
    ): void {
        Mail::to($user->email)->queue(new UserMembershipEventMail(
            user: $user,
            subjectLine: $subject,
            headline: $headline,
            detailLines: $detailLines,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            footerNote: $footerNote ?? 'If you did not expect this invitation, contact HERO support.',
        ));
    }

    /**
     * @param  list<string>  $detailLines
     */
    public static function notifyAdmins(
        string $subject,
        string $headline,
        array $detailLines,
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        $adminEmails = User::role('admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn ($email) => is_string($email) && $email !== '')
            ->unique()
            ->values()
            ->all();

        foreach ($adminEmails as $adminEmail) {
            Mail::to($adminEmail)->queue(new AdminMembershipEventMail(
                subjectLine: $subject,
                headline: $headline,
                detailLines: $detailLines,
                actionUrl: $actionUrl,
                actionLabel: $actionLabel,
            ));
        }
    }
}
