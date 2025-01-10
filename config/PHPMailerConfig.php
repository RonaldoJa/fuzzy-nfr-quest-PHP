<?php

require_once 'bookstores/PHPMailer/src/Exception.php';
require_once 'bookstores/PHPMailer/src/PHPMailer.php';
require_once 'bookstores/PHPMailer/src/SMTP.php';
require_once 'entities/SendEmailEntity.php';

use PHPMailer\PHPMailer\PHPMailer;

class PHPMailerConfig
{

    public static function sendEmail(SendEmailEntity $send)
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST');
        $mail->Port = getenv('MAIL_PORT');
        $mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = 0;
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');
        $mail->setFrom(getenv('MAIL_USERNAME'), 'FRUIT');
        $mail->addAddress($send->email);
        $mail->isHTML(true);
        $mail->Subject = $send->Subject;
        $mail->Body = $send->Body;
        $mail->CharSet = 'UTF-8';
        return $mail;
    }
}
