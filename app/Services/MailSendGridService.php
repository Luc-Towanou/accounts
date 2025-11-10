<?php 
namespace App\Services;

use Exception;
use SendGrid;
use SendGrid\Mail\Mail;

class MailSendGridService
{
    public function send($to, $subject, $content)
    {
        $email = new Mail();
        $email->setFrom(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/plain", $content);

        $sendgrid = new SendGrid(env('SENDGRID_API_KEY'));

        try {
            $response = $sendgrid->send($email);
            return $response->statusCode();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
