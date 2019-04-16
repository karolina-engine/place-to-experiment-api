<?php
/**
 * Created by PhpStorm.
 * User: arnarfjodur
 * Date: 29/11/16
 * Time: 12:33
 */

namespace Karolina;
use Aws\Ses\SesClient;
use Karolina\Language\Field;
use PHPMailer as PHPMailer;

class Notification
{

    private $toEmail = array();
    private $ccEmail = array();
    private $bccEmail = array();
    private $fromEmail;
    private $fromName;
    private $subject;
    private $tags = array();
    private $platform;
    private $htmlBody;
    private $plaintextBody;
    private $templateEngine;


    public function __construct()
    {

        $this->platform = new Platform();
        $this->subject = "Notification";
        $this->fromEmail = $this->platform->conf('email_noreply');
        $this->fromName = $this->platform->conf('platform_name');
        $this->toEmail[] = $this->platform->conf('email_info');

    }

    public function setFromEmail ($email) {

        $this->fromEmail = $email;

    }

    public function addBccEmail ($email) {

        $this->bccEmail[] = $email;

    }

    public function addCCEmail ($email) {

        $this->ccEmail[] = $email;

    }


    private function getTemplate ($key) {

        return "/notifications/".$key.".twig";

    }

    public function setSingleRecipient ($email) {

        $this->toEmail = array($email);
    }

    public function setTemplateEngine ($engine) {

        $this->templateEngine = $engine;

    }

    public function setSubject ($subjectLine) {

        $this->subject = $subjectLine;

    }

    public function safeHTML ($content) {

        return htmlentities($content, ENT_QUOTES, 'UTF-8');

    }
    
    public function setSubjectFromTemplate ($templateKey, $args = array()) {

        $template = $this->getTemplate($templateKey);
        $this->setSubjectFromHtml($this->templateEngine->render($template, $args));

    }

    public function setBodyFromTemplate ($templateKey, $args = array()) {

        $template = $this->getTemplate($templateKey);
        $this->setBodyFromHtml($this->templateEngine->render($template, $args));

    }

    public function setSubjectFromHtml ($html) {

        $field = new Field($html, 'html');
        $this->setSubject(html_entity_decode(strip_tags($field->getAsHTML())));

    }

    public function setBodyFromHtml ($html) {

        $field = new Field($html, 'html');

        $this->htmlBody = $field->getAsHTML();
        $this->plaintextBody = strip_tags($field->getAsHTML());

    }


    public function setBodyFromPlaintext ($plaintext) {

        $this->htmlBody = nl2br($this->safeHtml($plaintext));
        $this->plaintextBody = $plaintext;

    }

    public function send () {

        if (getenv('emailtesting') == "mailtrap") { // When doing tests
 
            $this->sendWithMailTrapTester();

        } else {

            $this->sendWithSES();
        }


    }

    private function sendWithSES () {


        $client = SesClient::factory(array(
            'version'     => 'latest',
            'region'  => 'us-east-1'
        ));

        $result = $client->sendEmail(array(
            // Source is required
            'Source' => $this->fromEmail,
            // Destination is required
            'Destination' => array(
                'ToAddresses' => $this->toEmail,
                'CcAddresses' => $this->ccEmail,
                'BccAddresses' => $this->bccEmail
            ),
            // Message is required
            'Message' => array(
                // Subject is required
                'Subject' => array(
                    // Data is required
                    'Data' => $this->subject,
                    'Charset' => 'UTF-8',
                ),
                // Body is required
                'Body' => array(
                    'Text' => array(
                        // Data is required
                        'Data' => $this->plaintextBody,
                        'Charset' => 'UTF-8',
                    ),
                    'Html' => array(
                        // Data is required
                        'Data' => $this->htmlBody,
                        'Charset' => 'UTF-8',
                    ),
                ),
            ),
            'ReplyToAddresses' => array($this->fromEmail),
            'ReturnPath' => $this->fromEmail
        ));       

    }


    private function sendWithMailTrapTester () {

        // See results on https://mailtrap.io/

        $mail = new PHPMailer;

        $mail->SMTPDebug = 3;                              

        $mail->isSMTP();                                      
        $mail->Host = 'smtp.mailtrap.io';  
        $mail->SMTPAuth = true;                       
        $mail->Username = 'fae1cc7d01adff';  
        $mail->Password = '39e528ea25be11';      
        $mail->SMTPSecure = 'tls';                           
        $mail->Port = 2525;              

        $mail->setFrom($this->fromEmail);

        foreach ($this->toEmail as $email) {
           $mail->addAddress($email); 
        }

        $mail->addReplyTo($this->fromEmail); 

        foreach ($this->ccEmail as $email) {
           $mail->addCC($email); 
        }
        foreach ($this->bccEmail as $email) {
           $mail->addBCC($email); 
        }

        $mail->isHTML(true);

        $mail->Subject = $this->subject;
        $mail->Body    = $this->htmlBody;
        $mail->AltBody = $this->plaintextBody;

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }


    }

}