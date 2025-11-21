<?php
require_once '../config.php';
require_once '../core.php';

$code = 200;
$response = ['message' => 'ppumbapumbapumbapumbaumba'];

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);

