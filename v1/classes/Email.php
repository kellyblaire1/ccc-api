<?php
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private $mailHost;
    private $mailUsername;
    private $mailPassword;
    private $mailSenderEmail;
    private $mailSenderName;
    public function __construct()
    {
        $this->mailHost = MAILHOST;
        $this->mailUsername = MAILUSERNAME;
        $this->mailPassword = MAILPASSWORD;
        $this->mailSenderEmail = FROMEMAIL;
        $this->mailSenderName = FROMNAME;
    }
    public function sendMail($subject, $body)
    {
        // Load Composer's autoloader
        require 'vendor/autoload.php';

        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = false; //SMTP::DEBUG_SERVER;  // Enable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = $this->mailHost; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = $this->mailUsername; // SMTP username
            $mail->Password = $this->mailPassword; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port = 587; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            // From email address and name
            $mail->setFrom($this->mailSenderEmail, $this->mailSenderName);

            // To email addresss
            $mail->addAddress(TOEMAIL); // Add a recipient
            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            //echo 'Message has been sent';
            return true;

        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
    
    public function sendUserMail($email,$subject, $body)
    {
        // Load Composer's autoloader
        require 'vendor/autoload.php';

        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = false; //SMTP::DEBUG_SERVER;  // Enable verbose debug output
            $mail->isSMTP(); // Send using SMTP
            $mail->Host = $this->mailHost; // Set the SMTP server to send through
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = $this->mailUsername; // SMTP username
            $mail->Password = $this->mailPassword; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port = 587; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            // From email address and name
            $mail->setFrom($this->mailSenderEmail, $this->mailSenderName);

            // To email addresss
            $mail->addAddress($email); // Add a recipient
            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            //echo 'Message has been sent';
            return true;

        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}