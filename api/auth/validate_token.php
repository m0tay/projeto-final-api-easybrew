<?php

require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$user = new User($pdo);

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));
    $code = 200;
    $response = [
      'message' => 'Acesso autorizado',
      'data' => $decoded->data,
      'expiration' => date('c', $decoded->exp),
      'timeleft' => $decoded->exp - time(),
      'data' => array(
        'id' => $user->id,
        'email' => $user->email,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'role' => $user->role,
        'balance' => $user->balance,
        'is_active' => $user->is_active
      )
    ];
  } catch (Exception $e) {
    $code = 403;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
