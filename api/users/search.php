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
$response = [];
$keywords = isset($data->keywords) ? $data->keywords : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: Permissões insuficientes'];
    } else {
      if (!empty($keywords)) {
        $stmt = $user->search($keywords);
        if ($stmt->rowCount() > 0) {
          $code = 200;
          $records = [];
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            unset($row['password_hash']);
            $records[] = $row;
          }
          $response = ['records' => $records];
        } else {
          $code = 404;
          $response = ['message' => 'Sem registros encontrados'];
        }
      } else {
        $code = 400;
        $response = ['message' => 'Palavra-chave não fornecida'];
      }
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
