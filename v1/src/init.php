<?php

require __DIR__ . '/../vendor/autoload.php';

use v1\Models\Database;
use v1\Models\Response;
use v1\Models\User;
use v1\Models\UserException;

date_default_timezone_set('Africa/Lagos');

// DEFINE SETINGS CONSTANTS
// $WEB_URL = 'https://oasisresearchcommunity.org/';
// $API_URL = 'https://oasisresearchcommunity.org/v1/';

$WEB_URL = 'http://192.168.64.3/ORC/';
$API_URL = 'http://192.168.64.3/ORC/v1/';


$NO_REPLY_EMAIL = 'no-reply@oasisresearchcommunity.org';
$NO_REPLY_EMAIL_HOST = 'oasisresearchcommunity.org';

$SUPPORT_EMAIL = 'support@oasisresearchcommunity.org';
$SUPPORT_EMAIL_HOST = 'oasisresearchcommunity.org';
$SMTP_PORT = 465;

try {
    $pdo = Database::connectDB();
} catch (UserException $e) {
    $response = new Response();
    $response->notAcceptable($e->getMessage());
    exit();
} catch (PDOException $e) {
    error_log("Connection Error: " . $e);
    $response = new Response();
    $response->internalServerError();
    exit();
} catch (Exception $e) {
    error_log("Error: " . $e);
    $response = new Response();
    $response->internalServerError();
    exit();
}
