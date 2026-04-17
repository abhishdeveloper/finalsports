<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure Composer autoload is loaded for PHPMailer (usually in bootstrap, but we check here)
if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
    require_once ROOT_DIR . '/vendor/autoload.php';
}

require_once CONFIG_DIR . '/mail.php';

class MailHelper {

    /**
     * Send an email using PHPMailer via Google SMTP
     *
     * @param string $to Email address to send to
     * @param string $subject Subject of the email
     * @param string $body HTML body of the email
     * @return bool True on success, false on failure
     */
    public static function sendEmail($to, $subject, $body) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Silently fail or log if PHPMailer isn't installed
            error_log("PHPMailer not loaded. Cannot send email to $to");
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            // Optional: disable SSL verification for local testing, remove in prod
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
