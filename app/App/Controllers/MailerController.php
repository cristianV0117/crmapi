<?php namespace App\Controllers;
use App\Controllers\BaseController;
use Config\Mail;
use Slim\Http\{Request, Response};
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Exceptions\MailException;
class MailerController extends BaseController
{
    private $mail;
    private $host;
    private $user;
    private $pass;
    private $port;
    private $secure;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->host = Mail::mail()["SMTP_HOST"];
        $this->user = Mail::mail()["SMTP_USER"];
        $this->pass = Mail::mail()["SMTP_PASS"];
        $this->port = Mail::mail()["SMTP_PORT"];
        $this->secure = Mail::mail()["SMTP_SECU"];
        $this->config();
    }

    private function config()
    {
        try {
            //$this->mail->SMTPDebug = 2;
            $this->mail->isSMTP();
            $this->mail->Host       = $this->host;
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $this->user;
            $this->mail->Password   = $this->pass;
            $this->mail->SMTPSecure = $this->secure;
            $this->mail->Port       = $this->port;
            $this->mail->setFrom('nativa@refocosta.com', 'CRM');
        } catch (Exception $e) {
            throw new MailException($this->mail->ErrorInfo, 500);
        }
    }

    public function mail(Request $request, Response $response): Response
    {
        try {
            $post = $request->getParsedBody();
            $address = $post['Address'];
            $subject = $post['Subject'];
            $body    = $post['Body'];
            $cc     = $post['Cc'];
            $this->mail->addAddress($address);
            if (count($cc) > 0) {
                foreach ($cc as $to) {
                    $this->mail->addCC($to);
                }
            }
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->send();
            return $this->response('Mensaje enviado', 200, $response);
        } catch (Exception $e) {
            throw new MailException($this->mail->ErrorInfo, 500);
        }
    }

    public function mailFromSystem(array $data)
    {
        try {
            $address = $data['Address'];
            $subject = $data['Subject'];
            $body    = $data['Body'];
            $this->mail->addAddress($address);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            $this->mail->send();
        } catch (Exception $e) {
            throw new MailException($this->mail->ErrorInfo, 500);
        }
    }

    public function __destruct()
    {
        $this->mail = null;
    }
}