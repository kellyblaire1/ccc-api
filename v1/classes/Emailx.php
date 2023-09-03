<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// using Google OAuth2
use League\OAuth2\Client\Provider\Google;

trait Email
{

    private $business_long = 'MindWeb Creationz Ltd';
    private $business_short = 'Scallat';
    private $website = 'scallat.com';
    private $url = 'https://scallat.com/';
    private $logoUrl = 'https://scallat.com/img/logo.png';

    private $mailHost = 'gator4243.hostgator.com';
    // private $mailHost = 'smtp.scallat.com';
    private $mailUsername = 'noreply@scallat.com';
    private $mailPassword = '#Scallat@2020';
    private $mailSenderEmail = 'noreply@scallat.com';
    private $mailSenderName = 'Scallat';

    private function header()
    {
        // <a href="' . $this->website . '" style="text-decoration: none;color: #111111;" target="_blank"><img src="' . $this->logoUrl . '" alt="' . $this->business_short . '" width="100"></a>
        return '<div id="header">
                    <div style="float: left;">
                        <a href="' . $this->website . '" style="text-decoration: none;color: #000000!important;font-size: 30px; font-weight: bold;" target="_blank"><img src="' . $this->logoUrl . '" alt="' . $this->business_short . '" width="100"/></a>
                    </div>
                </div>';
    }

    private function teamRegards()
    {

    }

    private function footer()
    {
        return '<div style="width: 100%; text-align: center; font-size: 10px;">
            <p>You are receiving ' . $this->business_short . ' notification emails because an account was created with this email address.</p>

            <p><a href="mailto:support@scallat.com">Get Support</a></p>
            <hr>
            ' . $this->copyRight() . '
        </div>';
    }

    private function copyRight()
    {
        return '<p>© ' . date('Y') . ' <a href="' . $this->url . '">' . $this->website . '</a>. ' . $this->business_short . ' is a registered business name of ' . $this->business_long . '.
        ' . $this->business_short . ' and the ' . $this->business_short . ' logo are registered trademarks of ' . $this->business_long . '.</p>';
    }

    public function template($body)
    {
        return '<!DOCTYPE html>
        <html lang="en">
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                <style type="text/css">
                    body, html {
                        color: #333;
                        font-size: 14px;
                        display: flex;
                        align-content: center;
                        justify-content: center;
                    }
                    a {
                        color: #111111;
                    }
                    #wrapper {
                        width: 100%; clear: both; padding: 20px 15px; box-sizing: border-box; display: inline-block;text-align: left;
                    }
                    #header {
                        background: #ffc107; width: 100%; clear: both; padding: 15px; box-sizing: border-box; display: inline-block;
                    }
                </style>
            </head>
            <body style="padding: 0px;margin:0px;display: flex; align-content: center; justify-content: center; background: #edf0f3;font-family:\'Open Sans\', Arial, sans-serif;">
                <div style="width: 600px; border: 1px solid #edf0f0;margin-left: auto; margin-right: auto; margin-top: 10px;" align="center">
                    <!-- MAIN CONTENTS STARTS -->
                    <div style="background: #ffffff;">
                        <!--   HEADER STARTS   -->
                        ' . $this->header() . '
                        <!-- HEADER ENDS -->

                        <!--   BODY CONTENT STARTS   -->
                        <div id="wrapper">
                            <!-- CONTENTS STARTS -->
                            ' . $body . '
                            <!-- CONTENTS ENDS -->
                        </div>
                        <!-- BODY CONTENTS ENDS -->
                    </div>
                    <!-- MAIN CONTENTS ENDS -->

                    <!-- FOOTER CONTENTS STARTS -->
                    ' . $this->footer() . '
                    <!-- FOOTER CONTENTS ENDS -->
                    </div>
            </body>

        </html>';

    }

    /* SEND EMAILS */
    // PHPMailer
    public function sendMail($email,$subject,$body)
    {
        require_once 'vendor/autoload.php';
        
        $body = $this->template($body);
        // create a new mailing object
        $mail = new PHPMailer();
        // SMTP configuration
        $phpmailer = new PHPMailer();
        $phpmailer->isSMTP();
        $phpmailer->Host = $this->mailHost;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = 2525;
        $phpmailer->Username = $this->mailUsername;
        $phpmailer->Password = $this->mailPassword;

        $phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        // $mail->Port = 587; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        $mail->setFrom($this->mailSenderEmail, $this->mailSenderName);
        // $mail->addReplyTo($this->mailSenderEmail, $this->mailSenderName);
        // $mail->addCustomHeader( 'In-Reply-To', '<' . $this->mailSenderEmail . '>' );    
        $mail->addAddress($email, '');
        $mail->Subject = $subject;

        // Our HTML setup

        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        // adding mailing attachment for payment plan
        // $mail->addAttachment('//node/paymments.pdf', 'payments.pdf');
        // send the thank you messange
        if(!$mail->send()){
            // echo 'Your message could not be develired, try again later';
            // echo 'Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            // echo 'Your message has been sent successfully.';
            return true;
        }
    }

    public function send($email,$subject,$body) {
        //Load Composer's autoloader
        require 'vendor/autoload.php';

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = false;//SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $this->mailHost; //'smtp.gmail.com'; //Set the SMTP server to send through
            $mail->HostName       = 'api.scallat.com'; //'smtp.gmail.com'; //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $this->mailUsername;//'okere.kelechukwu@gmail.com';                     //SMTP username
            $mail->Password   = $this->mailPassword; //'acjymoddqgelhtcs';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->XMailer = " "; //Disable 'Using PHPMailer 6.7.1 header content...text when viewed from Show Original
            
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom('noreply@scallat.com', 'Scallat');

            $mail->DKIM_domain = 'scallat.com';
            $mail->DKIM_private = '/home1/bloomnet/private.key';
            $mail->DKIM_selector = '1675102440.scallat';
            $mail->DKIM_passphrase = '';
            $mail->DKIM_identity = $mail->From;            
            
            //Recipients
            // $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
            $mail->addAddress($email); //Name is optional
            // $mail->addReplyTo('info@scallat.com', 'Scallat');
            // $mail->addCC('meetkellyonline@gmail.com');
            // $mail->addBCC('okere.kelechukwu@gmail.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
            $mail->ConfirmReadingTo = "scallatng@gmail.com";

            // For most clients expecting the Priority header:
            // 1 = High, 2 = Medium, 3 = Low
            $mail->Priority = 1;
            // MS Outlook custom header
            // May set to "Urgent" or "Highest" rather than "High"
            $mail->AddCustomHeader("X-MSMail-Priority: High");
            // Not sure if Priority will also set the Importance header:
            $mail->AddCustomHeader("Importance: High");

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $htmlbody = $this->template($body);
            // $mail->Body    = $htmlbody;
            $mail->MsgHTML($htmlbody);
            $mail->AltBody = strip_tags($body);

            $mail->send();
            // echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
    
    // SwiftMailer
    public function sendMailX($email,$subject,$body)
    {
        require_once 'vendor/autoload.php';
        
        $body = $this->template($body);
        // Create the Transport
        $transport = (new Swift_SmtpTransport('smtp.mailtrap.io', 2525))
        ->setUsername('fe1b1cb67a98b0')
        ->setPassword('7fd0dc380189d8');
        
        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        // Create a message
        $message = (new Swift_Message())
        ->setSubject($subject)
        ->setFrom([$this->mailSenderEmail])
        ->setTo([$email => ''])
        ->setCc(['okere.kelechukwu@gmail.com' => 'Product Manager']);

        // $message->setBody(
        //     '<html>' .
        //     ' <body>' .
        //     '  <img src="' .
        //         $message->embed(Swift_Image::fromPath('image.png')) . '" alt="Image" />' .
        //     '  <p>Welcome to Mailtrap!</p>'.
        //     'Now your test emails will be <i>safe</i>' .
        //     ' </body>' .
        //     '</html>',
        //     'text/html'
        // );
        $message->setBody($body);

        $message->addPart(strip_tags($body), 'text/plain');

        // $message->attach(Swift_Attachment::fromPath('/path/to/confirmation.pdf'));

        $mailer->send($message);
    }

    public function sendVCode($email, $fname, $vcode)
    {
        $subject = "Account Verification";
        $body = "<p>Hi " . $fname . ",</p>";
        $body .= "<p>Your verification code is:</p>";
        $body .= "<h1>" . $vcode . "</h1>";
        $body .= "<p>Thank you for choosing us.</p><br>";
        $body .= '<p style="margin-top: 10px;">Best Regards,<br>The ' . $this->business_short . ' Team</p>';

        $this->send($email, $subject, $body);
    }

    public function sendResetLink($email, $fname, $token)
    {
        $subject = "Account Verification";
        $body = "<p>Hi " . $fname . ",</p>";
        $body .= "<p>Click on the link below to reset your password:</p>";
        $body .= "<a href='".$url."reset/".$token."'>" . $vcode . "</a>";
        $body .= "<p>Thank you for choosing us.</p><br>";
        $body .= '<p style="margin-top: 10px;">Best Regards,<br>The ' . $this->business_short . ' Team</p>';

        $this->send($email, $subject, $body);
    }

    public function passwordReset($fname, $email)
    {
        $subject = "Password Reset Successful";
        // $body = "<h2>Password Update Successful!</h2>";
        $body = "<p>Hi ".$fname.",</p>";
        $body .= "<p>You have successfully updated your password on Scallat.</p>";
        $body .= "<p>If you did not initiate this action, kindly notify us via security@scallat.com.</p>";
        $body .= "<p>Thank you.</p>";
        $body .= '<p style="margin-top: 10px;">Best Regards,<br>The ' . $this->business_short . ' Team</p>';

        $this->send($email, $subject, $body);

    }

    public function welcomeEmail($email, $fname)
    {
        $subject = "Welcome to Scallat";
        $body = "<p>Dear " . $fname . ",</p>";
        $body .= "<p>Thank you for downloading our app.</p>";
        $body .= "<p>You can manage your vehicle documentation with ease on our platform.</p>";
        $body .= "<p>Below are some of the benefits of being on the Scallat platform:</p>";
        $body .= "<ol>";
        $body .= "<li>Get Vehicle Document Expiration Reminders via email, SMS or WhatsApp.</li>";
        $body .= "<li>Register new vehicles.</li>";
        $body .= "<li>Renew expired vehicle documents.</li>";
        $body .= "<li>Process your vehicle change of ownership.</li>";
        $body .= "<li>Register new vehicle engine.</li>";
        $body .= "<li>You can also manage your company's fleet on this robust platform with ease and convenience.</li>";
        $body .= "</ol>";
        $body .= "<p>We deliver convenience. Once your documents are processed, we deliver straight to your doorstep, at your request; or you can pick up your document at the designated pickup point near you.</p>";
        $body .= "<p>As an innovative tech company, we are evolving with our high-tech infrastructures to ensure you have the best experience on this platform. More exciting features unfolding. So, stay connected in order not to miss out on any of these features.</p>";
        $body .= "<p>Welcome to the easiest, convenient and most affordable vehicle management system in Nigeria.</p>";
        $body .= '<p style="margin-top: 10px;">Best Regards,<br>The ' . $this->business_short . ' Team</p>';

        $this->send($email, $subject, $body);
    }

    public function NotifyAdmins()
    {
        $subject = "New Order Booking";
        $body = "<p>Dear Admin,</p>";
        $body .= "<p>There is a new order on your platform.</p>";
        $body .= "<p>Please log on to the console to manage the order.</p>";
        $body .= "<p>https://console.scallat.com.</p>";
        $body .= "<p></p>";
        $body .= "<p>Best regards,<br>The Scallat Team</p>";

        $this->send('info@scallat.com', $subject, $body);
        $this->send('scallatng@gmail.com', $subject, $body);
    }

    public function newVehicleEmail($email, $fname,$make,$model,$year,$regnum)
    {
        $subject = "New Vehicle Added";
        // $body = "<h2>New vehicle added on Scallat!</h2>";
        $body = "<p>Hi ".$fname.",</p>";
        $body .= "<p>You have added ".$make." ".$model."(".$year.") with Registration Number, ".$regnum.", to your fleet on Scallat.</p>";
        $body .= "<p>Thank you.</p>";
        $body .= '<p style="margin-top: 10px;">Best Regards,<br>The ' . $this->business_short . ' Team</p>';

        $this->send($email, $subject, $body);
    }
    
    public function setReminderEmail($doc,$email,$fname,$name,$regnum,$vin,$make,$model,$color,$year,$engine,$expirydate,$daysleft)
    {        
        $expire = $expirydate;
        
        $subject = $doc." Expiration Reminder";
        $body = '<p>Dear '.$fname.',</p>
                    <p>Thank you for enrolling your '.$doc.' in the all-in-one vehicle management system. We will take you on a journey of a fast, reliable, genuine and affordable vehicle documentation service. This starts with prompt notification of expiry log and every transaction records update.</p>
                    <p>Below is your valid information:<p>
                    <p>Owners Name: '.$name.'<br>
                    Vehicle Registration:  '.$regnum.'<br>
                    Vin Number: '.$vin.'<br>
                    Engine Number: '.$engine.'<br>
                    Vehicle Make: 	'.$make.' '.$model.'<br>
                    Color:	'.$color.'<br>
                    Year:	'.$year.'</p>

                    <p>Your '.$doc.' will expire on '.$expirydate.' ('.$daysleft.' days left).</p>
                    <p>You will be informed within 1 month, 2 weeks and 4 days intervals, respectively, before the due date of the document expiration, at no cost.</p>

                    <p>For further enquires, kindly contact us via:<br>
                    Email: support@scallat<br>
                    Phone: +2348035382247.<br>
                    WhatsApp: +2348035382247</p>

                    <p>Thanks for choosing Scallat.</p>';
        $body .= '<p style="margin-top: 10px;">Best Regards,<br>The ' . $this->business_short . ' Team</p>';                    

        $this->send($email, $subject, $body);
    }

    public function welcome($email, $fname)
    {
        $subject = "Welcome to Scallat";

        $body = "<p>Dear " . $fname . ",</p>";
        $body .= "<p>Welcome to the easiest and most affordable delivery network in Lagos.</p>";
        $body .= "<p>Thank you for downloading our app.</p>";
        $body .= "<p>We are pleased to have you onboard. Over 600 verified dispatchers on our platform are waiting to pick up your orders for swift delivery across Lagos.</p>";
        $body .= "<p>Always use your Scallat app to connect to riders in less than a minute. You can reach us via our Customer Support hotline, either by phone call or WhatsApp chat, on +2347046169983.</p>";
        $body .= "<p>Cheers to a greater experience!</p>";

        $body .= "<p>Thank you for choosing Scallat.</p><br>";
        $body .= "<p>Best regards,<br>The Scallat Team</p>";

        $this->send($email, $subject, $body);
    }
}