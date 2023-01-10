<?php

namespace v1\Models;

use Exception as GlobalException;
use v1\Libraries\Utilities\Helper;

use PDO;
use PDOException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class QuoteException extends GlobalException
{
}

enum QuoteResolved: int
{
    case YES = 1;
    case NO = 0;
}

class Quote
{
    private string $_table = 'quotes';
    private string $_unique_key = 'quoteRef';
    private $_id;
    private string $_quoteRef;
    private string $_name;
    private string $_email;
    private string $_subject;
    private string $_message;
    private string $_resolved;
    private $_createdAt;

    public function __construct($name, $email, $subject, $message, $resolved = null, $createdAt = null)
    {
        $this->_quoteRef = Helper::generateUniqueRef($this->_table, $this->_unique_key);
        $this->setName($name);
        $this->setEmail($email);
        $this->setSubject($subject);
        $this->setMessage($message);
        $this->_resolved = is_null($resolved) ? QuoteResolved::NO->value : $resolved;
        $this->_createdAt = is_null($createdAt) ? Helper::getDateTime() : $createdAt;
    }

    function setID($id)
    {
        if (!is_null($id) && !is_numeric($id)) throw new QuoteException("Invalid id");
        elseif (is_numeric($id) && $id < 1) throw new QuoteException("Invalid id");
        $this->_id = $id;
    }

    private function setName($name)
    {
        if (!is_null($name) && $name === "") throw new QuoteException("Invalid name");
        $this->_name = $name;
    }

    private function setEmail($email)
    {
        if (!is_null($email) && !Helper::isValidEmail($email)) throw new QuoteException("Invalid email address");
        $this->_email = $email;
    }

    private function setSubject($subject)
    {
        if (!is_null($subject) && $subject === "") throw new QuoteException("Invalid subject");
        $this->_subject = $subject;
    }

    private function setMessage($message)
    {
        if (!is_null($message) && $message === "") throw new QuoteException("Invalid message");
        $this->_message = $message;
    }

    public function getQuoteRef()
    {
        return $this->_quoteRef;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function create()
    {
        global $pdo;

        $query = "INSERT INTO `$this->_table` (`quoteRef`, `name`, `email`, `subject`, `message`) VALUES (:quoteRef, :name, :email, :subject, :message)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':quoteRef', $this->_quoteRef, PDO::PARAM_STR);
        $stmt->bindParam(':name', $this->_name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $this->_email, PDO::PARAM_STR);
        $stmt->bindParam(':subject', $this->_subject, PDO::PARAM_STR);
        $stmt->bindParam(':message', $this->_message, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function mail()
    {
        global $WEB_URL;
        global $SUPPORT_EMAIL;
        global $SUPPORT_EMAIL_HOST;
        global $SMTP_PORT;

        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $SUPPORT_EMAIL_HOST;           //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $SUPPORT_EMAIL;   //SMTP username
            $mail->Password   = $SUPPORT_EMAIL;   //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $SMTP_PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($SUPPORT_EMAIL, 'OASIS Research Community Contact Form');
            $mail->addAddress($SUPPORT_EMAIL);     //Add a recipient
            // $mail->addAddress('ellen@example.com');             //Name is optional
            $mail->addReplyTo($this->_email, $this->_name);
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $this->_subject;
            $mail->Body    = <<<HTML

                <style>html,body { padding:0; margin:0; font-family: Inter, Helvetica, "sans-serif"; } a:hover { color: #009ef7; }</style>
                <div id="#kt_app_body_content" style="background-color:#D5D9E2; font-family:Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:20px 0; width:100%;">
                    <div style="background-color:#ffffff; padding: 45px 0 34px 0; border-radius: 24px; margin:40px auto; max-width: 600px;">
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" height="auto" style="border-collapse:collapse">
                            <tbody>
                                <tr>
                                    <td align="center" valign="center" style="text-align:center; padding-bottom: 10px">
                                        
                                        <div style="text-align:center; margin:0 15px 34px 15px">
                                            
                                            <div style="margin-bottom: 10px">
                                                <a href="{$WEB_URL}" rel="noopener" target="_blank">
                                                    <img alt="Logo" src="{$WEB_URL}/assets/media/logos/default-logo.png" style="height: 35px; width: 100px; object-fit: contain;" />
                                                </a>
                                            </div>
                                            
                                            
                                            <div style="margin-bottom: 15px">
                                                <img alt="Logo" src="{$WEB_URL}/assets/media/email/icon-positive-vote-2.svg" />
                                            </div>
                                            
                                            
                                            <div style="font-size: 14px; font-weight: 500; margin-bottom: 27px; font-family:Arial,Helvetica,sans-serif;">
                                                <p style="margin-bottom:9px; color:#000204; font-size: 22px; font-weight:700">Contact form message!</p>
                                                <p style="margin-bottom:2px; color:#A1A5B7">$this->_message</p>
                                            </div>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="center" style="font-size: 13px; padding:0 15px; text-align:center; font-weight: 500; color: #A1A5B7;font-family:Arial,Helvetica,sans-serif">
                                        <p>&copy; Copyright OASIS Research Community.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            HTML;
            $mail->AltBody = $this->_message;

            $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    public function getDBQuoteData()
    {
        global $pdo;

        if (is_null($this->_id)) {
            throw new QuoteException("Quote id is null");
            exit();
        }

        $query = "SELECT * FROM `$this->_table` WHERE `id` = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $this->_id, PDO::PARAM_STR);
        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if ($rowCount === 0) {
            throw new PDOException("Failed to get data after creation");
            exit();
        }

        $quotes = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $quote = [
                'id' => $id,
                'quoteRef' => $quoteRef,
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'resolved' => $resolved,
                'createdAt' => $createdAt,
            ];
            $quotes[] = $quote;
        }

        $return_data = array();
        $return_data['total'] = $rowCount;
        $return_data['quotes'] = $quotes;

        return $return_data;
    }

    public function getQuoteData()
    {
        return (object) [
            'id' => $this->_id,
            'quoteRef' => $this->_quoteRef,
            'name' => $this->_name,
            'email' => $this->_email,
            'subject' => $this->_subject,
            'message' => $this->_message,
            'resolved' => $this->_resolved,
            'createdAt' => $this->_createdAt,
        ];
    }
}
