<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

/**
 * Sends messages through PHP's built-in {@see mail()} function.
 *
 * Requires a working local MTA / sendmail shim (often missing on Windows unless
 * XAMPP Mercury or similar is configured). Use {@see MAIL_PHPMAIL_PARAMS} for a
 * typical {@code -f} envelope sender on Linux hosts.
 */
class PhpMailTransport extends AbstractTransport
{
    public function __construct(
        protected string $params = ''
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'phpmail://default';
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        if (! $original instanceof Email) {
            throw new \InvalidArgumentException('PhpMailTransport only supports Symfony Email messages.');
        }

        $toAddresses = array_map(static fn ($a) => $a->getAddress(), $original->getTo());
        if ($toAddresses === []) {
            return;
        }

        $subject = (string) ($original->getSubject() ?? '');
        $html = $original->getHtmlBody();
        $text = $original->getTextBody();
        if ($html !== null && $html !== '') {
            $body = $html;
            $contentType = 'text/html; charset=UTF-8';
        } else {
            $body = (string) $text;
            $contentType = 'text/plain; charset=UTF-8';
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: '.$contentType,
            'Content-Transfer-Encoding: 8bit',
        ];

        foreach ($original->getFrom() as $address) {
            $headers[] = 'From: '.$address->toString();

            break;
        }
        foreach ($original->getReplyTo() as $address) {
            $headers[] = 'Reply-To: '.$address->toString();
        }
        foreach ($original->getCc() as $address) {
            $headers[] = 'Cc: '.$address->toString();
        }
        foreach ($original->getBcc() as $address) {
            $headers[] = 'Bcc: '.$address->toString();
        }

        $headerString = implode("\r\n", $headers);
        $toLine = implode(', ', $toAddresses);

        $params = $this->params;
        $ok = @mail($toLine, $subject, $body, $headerString, $params);
        if (! $ok) {
            $this->getLogger()->error('PhpMailTransport: mail() returned false.', [
                'to' => $toLine,
                'subject' => $subject,
            ]);
        }
    }
}
