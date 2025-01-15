<?php

namespace Utilities;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailSender {
    /**
     * @throws Exception
     */
    public static function send($html, $text, $subject, $emailDest): void {
        $mailer = new PHPMailer(true);

        $senderEmailAddress = getenv('MAIL_SENDER_EMAIL');
        $smtpHost = getenv('MAIL_SENDER_HOST');
        $smtpPort = getenv('MAIL_SENDER_PORT');
        $smtpUsername = getenv('MAIL_SENDER_USERNAME');
        $smtpPassword = getenv('MAIL_SENDER_PASSWORD');

        $mailer->isSMTP();
        $mailer->Host = $smtpHost;
        $mailer->SMTPAuth = true;
        $mailer->Username = $smtpUsername;
        $mailer->Password = $smtpPassword;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailer->Port = $smtpPort;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($senderEmailAddress, 'Kittens');
        $mailer->isHTML(true);

        $mailer->Subject = $subject;
        $mailer->Body = $html;
        $mailer->AltBody = $text;

        $mailer->addAddress($emailDest);

        $mailer->send();
    }

}

