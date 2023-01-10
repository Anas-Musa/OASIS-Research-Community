<?php

namespace v1\Models;

use rand;
use Exception;
use PDO;
use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use v1\Libraries\Utilities\Helper;

class UserException extends Exception
{
}

class User
{
    private string $_table = 'users';
    private string $_unique_key = 'userRef';
    private $_id = null;
    private string $_userRef;
    private string $_email;
    private $_password;
    private string $_verificationCode;
    private int $_verified;
    private $_createdAt;

    public function __construct($email, $password = null)
    {
        $this->_userRef = Helper::generateUniqueRef($this->_table, $this->_unique_key);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->_verified = Verified::NO->value;
        $this->_createdAt = Helper::getDateTime();
    }

    function setID($id)
    {
        if (!is_null($id) && !is_numeric($id)) throw new UserException("Invalid id");
        elseif (is_numeric($id) && $id < 1) throw new UserException("Invalid id");
        $this->_id = $id;
    }

    private function setEmail($email)
    {
        if (!is_null($email) && !Helper::isValidEmail($email)) throw new UserException("Invalid email address");
        $this->_email = $email;
    }

    function setVerificationCode($otp)
    {
        if (!is_null($otp) && !is_numeric($otp)) throw new UserException("Invalid OTP");
        $this->_verificationCode = $otp;
    }

    private function setPassword($password)
    {
        if (!is_null($password) && !Helper::isValidPassword($password))
            throw new UserException("Password must be at least length 8 & must contain \nat least one lowercase letter, one uppercase letter, one number & at least a special character (non-word characters)");
        $this->_password = is_null($password) ? $password : Helper::hashPassword($password);
    }

    public function getUserRef()
    {
        return $this->_userRef;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function getPassword()
    {
        return $this->_email;
    }

    public function getVerified()
    {
        return $this->_verified;
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    public function verify()
    {
        global $pdo;

        $query = "SELECT `id` FROM `$this->_table` WHERE `email` = :email AND `verificationCode` = :verificationCode LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $this->_email, PDO::PARAM_STR);
        $stmt->bindParam(':verificationCode', $this->_verificationCode, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $response = new Response();
            $response->notFound(['User not found']);
            exit();
        }

        $verificationCode = null;
        $verified = "1";
        $verifiedAt = Helper::getDateTime();

        $query = "UPDATE `$this->_table` SET `verificationCode` = :verificationCode, `verified` = :verified, `verifiedAt` = :verifiedAt";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':verificationCode', $verificationCode, PDO::PARAM_NULL);
        $stmt->bindParam(':verified', $verified, PDO::PARAM_STR);
        $stmt->bindParam(':verifiedAt', $verifiedAt, PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new PDOException('Failed to update verified status');
            exit();
        }

        $response = new Response();
        $response->ok();
        exit();
    }

    public function resendOTP()
    {
        global $pdo;

        $query = "SELECT `email` FROM `$this->_table` WHERE `email` = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $this->_email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $response = new Response();
            $response->notFound('User not found');
            exit();
        }

        $this->_verificationCode = Helper::generateNumericOTP(6);


        $query = "UPDATE `$this->_table` SET `verificationCode` = :verificationCode";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':verificationCode', $this->_verificationCode, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new PDOException("Failed to update OTP");
            exit();
        }

        $this->sendOTP();

        $response = new Response();
        $response->ok();
        exit();
    }

    public function create()
    {
        global $pdo;

        $query = "SELECT `email` FROM `$this->_table` WHERE `email` = ?";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(1, $this->_email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $response = new Response();
            $response->conflict();
            exit();
        }

        $this->_verificationCode = Helper::generateNumericOTP(6);


        $query = "INSERT INTO `$this->_table` (`userRef`, `email`, `password`, `verificationCode`, `verified`, `createdAt`) VALUES (:userRef, :email, :password, :verificationCode, :verified, :createdAt)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userRef', $this->_userRef, PDO::PARAM_STR);
        $stmt->bindParam(':email', $this->_email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $this->_password, PDO::PARAM_STR);
        $stmt->bindParam(':verificationCode', $this->_verificationCode, PDO::PARAM_STR);
        $stmt->bindParam(':verified', $this->_verified, PDO::PARAM_STR);
        $stmt->bindParam(':createdAt', $this->_createdAt, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function sendOTP()
    {
        global $WEB_URL;
        global $NO_REPLY_EMAIL;
        global $NO_REPLY_EMAIL_HOST;
        global $SMTP_PORT;

        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $NO_REPLY_EMAIL_HOST;           //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $NO_REPLY_EMAIL;   //SMTP username
            $mail->Password   = $NO_REPLY_EMAIL;   //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $SMTP_PORT;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($NO_REPLY_EMAIL, 'no-reply OASIS Research Community');
            $mail->addAddress($this->_email);     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Verify your email';
            $mail->Body    = <<<HTML

                <style>html,body { padding:0; margin:0; font-family: Inter, Helvetica, "sans-serif"; } a:hover { color: #009ef7; }</style>
                <div id="#kt_app_body_content" style="background-color:#D5D9E2; font-family: Arial,Helvetica,sans-serif; line-height: 1.5; min-height: 100%; font-weight: normal; font-size: 15px; color: #2F3044; margin:0; padding:20px 0; width:100%;">
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
                                            
                                            <div style="font-size: 14px; font-weight: 500; margin-bottom: 27px; font-family: Arial,Helvetica,sans-serif;">
                                                <p style="margin-bottom:9px; font-size: 22px; font-weight:700; color: #000204;">Verify your email</p>
                                                <p style="margin-bottom:2px; color:#A1A5B7">To activate your OASIS Research Community Account, please verify your email address.</p>
                                                
                                                <p style="margin-bottom:2px; color:#A1A5B7">Please use the OTP below to verify your account</p>
                                                <br>
                                                <p style="margin-bottom:2px; letter-spacing: 10px; font-size: 24px; text-align: center; color: #000204;">{$this->_verificationCode}</p>

                                            </div>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="center" style="font-size: 13px; padding:0 15px; text-align:center; font-weight: 500; color: #A1A5B7;font-family: Arial,Helvetica,sans-serif">
                                        <p>&copy; Copyright OASIS Research Community.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            HTML;
            $mail->AltBody = 'To activate your OASIS Research Community Account, please verify your email address. Please use the OTP below to verify your account \n ' . $this->_verificationCode;

            $mail->send();
        } catch (Exception $e) {
        }
    }

    public function getDBUserData()
    {
        global $pdo;

        if (is_null($this->_id)) {
            throw new UserException("User id is null");
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

        $users = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $user = [
                'id' => $id,
                'quoteRef' => $userRef,
                'email' => $email,
                'password' => $password,
                'verified' => $verified,
                'createdAt' => $createdAt,
            ];
            $users[] = $user;
        }

        $return_data = array();
        $return_data['total'] = $rowCount;
        $return_data['users'] = $users;

        return $return_data;
    }

    public function getUserData()
    {
        return (object) [
            'id' => $this->_id,
            'userRef' => $this->_userRef,
            'email' => $this->_email,
            'password' => $this->_password,
            'verified' => $this->_verified,
            'createdAt' => $this->_createdAt,
        ];
    }
}
