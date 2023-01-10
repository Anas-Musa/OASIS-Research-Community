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
 * /signup POST
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

            if (!isset($json->email) || !isset($json->password)) {
                $response = new Response();

                $messages = [];

                if (!isset($json->email)) {
                    array_push($messages, 'Provide email field');
                }
                if (!isset($json->password)) {
                    array_push($messages, 'Provide password field');
                }

                $response->badRequest($messages);
                exit();
            }

            $user = new User($json->email, $json->password);

            $rowCount = $user->create();

            if ($rowCount === 0) {
                throw new PDOException();
                exit();
            }

            $user->sendOTP();
            $lastQuoteID = intval($pdo->lastInsertId());
            $user->setID($lastQuoteID);
            $return_data = $user->getDBUserData();

            $response = new Response();
            $response->addMessage('OTP has been sent to ' . $user->getEmail());
            $response->created($return_data);
            exit();
        } catch (UserException $e) {
            $response = new Response();
            $response->notAcceptable([$e->getMessage()]);
            exit();
        } catch (PDOException $e) {
            error_log("Database query error - " . $e);
            $response = new Response();
            $response->internalServerError(['Quote creation failed']);
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
