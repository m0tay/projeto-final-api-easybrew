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
$response = [];

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    $is_admin = $decoded->data->role === 'admin';

    if (!$is_admin) {
      $code = 403;
      $response = ['message' => 'Acesso negado: apenas administradores podem listar máquinas'];
    }

    if ($is_admin) {
      $machines = $machine->browse();

      if ($machines->rowCount() > 0) {
        $code = 200;
        $records = [];
        while ($row = $machines->fetch(PDO::FETCH_ASSOC)) {
          $records[] = $row;
        }
        $response['records'] = $records;
        $response['message'] = 'Máquinas obtidas com sucesso';
      } else {
        $code = 404;
        $response['message'] = 'Sem registros';
      }
    }
  } catch (Exception $e) {
    $code = 401;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado: Token JWT não fornecido'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
$json = json_encode($response);
if ($json === false) {
  $json = json_encode(['error' => 'JSON encoding failed', 'json_error' => json_last_error_msg()]);
}
echo $json;
