<?php

require_once('simplystrava_api.php');

if (is_file('simply_strava.png')) {
    $modtime = filemtime('simply_strava.png');
} else {
    $modtime = filemtime('simplystrava.php');
}

if ($modtime != $_GET['auth']) {
    $auth = array("error"=>"No Logged In");
} else {

    $stravaObj = new SimplyStrava;

    // Prevent caching.
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 01 Jan 1996 00:00:00 GMT');

    // The JSON standard MIME header.
    header('Content-type: application/json');

    // This ID parameter is sent by our javascript client.
    $email = $_GET['email'];
    $password = $_GET['password'];

    $auth = $stravaObj->obtain_token($email, $password);

    if (!$auth) {
        $auth = array("error"=>"Authentication Error.");
    }
}
echo json_encode($auth);
?>
