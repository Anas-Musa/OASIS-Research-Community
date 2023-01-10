<?php

require __DIR__ . '/../../vendor/autoload.php';

use v1\Models\Response;
use v1\Models\Quote;
use v1\Models\QuoteException;

extract($_SERVER);

require_once('../init.php');
/**
 * /quote POST, GET
 */
if (empty($_GET)) {

    if ($REQUEST_METHOD === 'GET') {

        exit();
    }

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

            if (!isset($json->name) || !isset($json->email) || !isset($json->subject) || !isset($json->message)) {
                $response = new Response();

                $messages = [];

                if (!isset($json->name)) {
                    array_push($messages, 'Provide name field');
                }
                if (!isset($json->email)) {
                    array_push($messages, 'Provide email field');
                }
                if (!isset($json->subject)) {
                    array_push($messages, 'Provide subject field');
                }
                if (!isset($json->message)) {
                    array_push($messages, 'Provide message field');
                }

                $response->badRequest($messages);
                exit();
            }

            $quote = new Quote($json->name, $json->email, $json->subject, $json->message);
            $quoteRef = $quote->getQuoteRef();
            $name     = $quote->getName();
            $email    = $quote->getEmail();
            $subject  = $quote->getSubject();
            $message  = $quote->getMessage();

            $rowCount = $quote->create();

            if ($rowCount === 0) {
                throw new PDOException();
                exit();
            }

            $lastQuoteID = intval($pdo->lastInsertId());
            $quote->setID($lastQuoteID);
            $return_data = $quote->getDBQuoteData();

            $response = new Response();
            $response->created($return_data);
            $quote->mail();
            exit();
        } catch (QuoteException $e) {
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
