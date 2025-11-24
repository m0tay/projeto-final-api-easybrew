<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Machine.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$machine = new Machine($pdo);

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], 'HS256'));

    $code = 200;
    $response = [
      'message' => 'Acesso autorizado',
      'data' => $decoded->data,
      'expiration' => date('c', $decoded->exp),
      'timeleft' => $decoded->exp - time(),
    ];
    $machines = $machine->browse();

    if ($machines->rowCount() > 0) {
      $code = 200;
      $response['records'] = $machines->fetchAll();
    } else {
      $code = 404;
      $response['message'] = 'Sem registros';
    }
  } catch (Exception $e) {
    $code = 401;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
}


header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
