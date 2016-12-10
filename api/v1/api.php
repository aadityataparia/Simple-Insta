<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : ' ';

header('Access-Control-Allow-Origin: '.$http_origin);
header('Expires: -1');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, HEAD');
header('Access-Control-Allow-Headers: x-session-pass');
header('Cache-Control: must-revalidate, private');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
  die();
}

require_once 'Rest.inc.php';
$data = RestUtils::processRequest();

require_once 'crud.php';
$rest = new RestCRUD($data);

$timestring = date("D, d M Y H:i:s", $rest->lm());
$lmheader = $timestring . " GMT";
header("Last-Modified: " . $lmheader);

// if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
//     $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lmheader) {
//     header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
//     die();
// } elseif (isset($data->getRequestVars()['if-modified-since']) &&
//     $data->getRequestVars()['if-modified-since'] == $rest->lm()) {
//     header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
//     die();
// }
$format = isset($data->getRequestVars()['format']) ? $data->getRequestVars()['format'] : 'json';
$exec = intval((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000) + 1;
$rest->addtoPHP('time_taken', $exec);
$rest->addtoPHP('request_time', $_SERVER['REQUEST_TIME_FLOAT']);
$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 0;
if ($format == 'xml' || $accept == 'application/xhtml+xml' || $accept == 'application/xml') {
    header('Content-Type: text/xml');
    $rest->printXML();
} else {
    header('Content-Type: application/json');
    $rest->printJSON();
}
