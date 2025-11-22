<?php

// Carregar configurações
require_once '../../config.php';
require_once '../../core.php';
$pdo = connectDB($db);
// Carregar classe
require_once '../../objects/User.php';
$user = new User($pdo);

// Carregar JWT
require '../../vendor/autoload.php';
use \Firebase\JWT\JWT;

// Obter dados do POST
$data = json_decode(file_get_contents('php://input'));

// Obter JWT
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
          'timeleft' => $decoded->exp-time(),
        ];
    } catch (Exception $e) {
      // Acesso negado - 401 Unauthorized
      $code = 401;
      $response = ['error' => 'Acesso negado: ' . $e->getMessage()];
    }
} else {
  // Acesso negado - 401 Unauthorized
  $code = 401;
  $response = ['error' => 'Acesso negado: ' . $e->getMessage()];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
