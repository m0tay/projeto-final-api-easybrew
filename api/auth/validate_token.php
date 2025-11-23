<?php

require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$user = new User($pdo);

use \Firebase\JWT\JWT;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : "";

if ($jwt) {
  try {
    // Decode do JWT
    $decoded = JWT::decode($jwt, $jwt_conf['key'], array('HS256'));
    // Sucesso na operação - 200 OK
    $code = 200;
    // Enviar Resposta
    $response = [
      'message' => 'Acesso autorizado',
      'data' => $decoded->data,
      'expiration' => date('c', $decoded->exp),
      'timeleft' => $decoded->exp - time(),
    ];
  } catch (Exception $e) {
    $code = 401;
    $response = ['error' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['error' => 'Acesso negado: ' . $e->getMessage()];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
