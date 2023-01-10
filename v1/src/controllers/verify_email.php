<?php

require __DIR__ . '/../../vendor/autoload.php';

use v1\Models\Response;
use v1\Models\Quote;
use v1\Models\QuoteException;
use v1\Models\User;
use v1\Models\UserException;

extract($_SERVER);

require_once('../init.php');
/**
 * /verify POST
 */
if (empty($_GET)) {

    if ($REQUEST_METHOD === 'POST') {
        try {
            if ((isset($CONTENT_TYPE) && $CONTENT_TYPE !== 'application/json') || !isset($CONTENT_TYPE)) {
                $response = new Response();
                $response->badRequest('Content type not set to application/json');
                exit();
            }

            $raw_data = file_get_contents('php://input');

            if (!($json = json_decode($raw_data))) {
                $response = new Response();
                $response->badRequest('Request body is not valid JSON');
                exit();
            }

            if (!isset($json->email) || !isset($json->otp)) {
                $response = new Response();

                $messages = [];

                if (!isset($json->email)) {
                    array_push($messages, 'Provide email');
                }
                if (!isset($json->otp)) {
                    array_push($messages, 'Provide OTP');
                }

                $response->badRequest($messages);
                exit();
            }

            $user = new User($json->email);
            $user->setVerificationCode($json->otp);
            $user->verify();
            exit();
        } catch (UserException $e) {
            $response = new Response();
            $response->notAcceptable([$e->getMessage()]);
            exit();
        } catch (PDOException $e) {
            error_log("Database query error - " . $e);
            $response = new Response();
            $response->internalServerError([$e->getMessage()]);
            exit();
        }
    }

    $response = new Response();
    $response->methodNotAllowed();
    exit();
}

$response = new Response();
$response->notFound();
exit();
