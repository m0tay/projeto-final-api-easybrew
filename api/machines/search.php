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
$keywords = isset($data->keywords) ? $data->keywords : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], 'HS256'));
    if (!empty($keywords)) {
      $stmt = $machine->search($keywords);
      if ($stmt->rowCount() > 0) {
        $code = 200;
        $response = ['records' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
      } else {
        $code = 404;
        $response = ['message' => 'Sem registros encontrados'];
      }
    } else {
      $code = 400;
      $response = ['message' => 'Palavra-chave não fornecida'];
    }
  } catch (Exception $e) {
    $code = 401;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado: Token não fornecido'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
