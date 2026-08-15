<?php

declare(strict_types=1);
namespace In2code\Powermail\Utility;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MailUtility
 * @codeCoverageIgnore
 */
class MailUtility
{
    /**
     * Send a plain mail for simple notifies
     *
     * @param string $receiverEmail Email address to send to
     * @param string $senderEmail Email address from sender
     * @param string $subject Subject line
     * @param string $body Message content
     * @return bool mail was sent?
     */
    public static function sendPlainMail(
        string $receiverEmail,
        string $senderEmail,
        string $subject,
        string $body
    ): bool {
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->setTo([$receiverEmail => '']);
        $message->setFrom([$senderEmail => 'Sender']);
        $message->setSubject($subject);
        $message->text($body);

        return self::send($message);
    }

    /**
     * Send a prepared MailMessage, logging (instead of fataling) on a transport
     * or message-validity failure.
     *
     * Shared by sendPlainMail() above, SendMailService::prepareAndSend(), and
     * ExportService::sendEmail() - all three used to duplicate this same
     * try/catch independently.
     *
     * @param MailerInterface|null $mailer Pass an already-resolved mailer to
     *        avoid a fresh container lookup on every call (relevant for
     *        callers invoking this once per receiver in a loop); defaults to
     *        resolving one for single-call callers like sendPlainMail() above.
     */
    public static function send(MailMessage $message, ?MailerInterface $mailer = null): bool
    {
        $mailer ??= GeneralUtility::makeInstance(MailerInterface::class);
        try {
            $mailer->send($message);
        } catch (TransportExceptionInterface|RfcComplianceException $exception) {
            ObjectUtility::getLogger(self::class)->error('Mail could not be sent: ' . $exception->getMessage());
            return false;
        }

        return true;
    }
}
