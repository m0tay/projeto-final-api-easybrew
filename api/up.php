<?php
require_once '../config.php';
require_once '../core.php';

$code = 200;
$response = ['message' => 'Up and alive!'];

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
die(); // https://stackoverflow.com/questions/4064444/returning-json-from-a-php-script#comment88774427_4064468
