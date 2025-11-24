
<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Machine.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$machine = new Machine($pdo);

use \Firebase\JWT\JWT;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';
$machine_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, $jwt_conf['key'], array('HS256'));

    if (empty($machine_id)) {
      $code = 400;
      $response = ['message' => 'ID da máquina não fornecido'];
    } else {
      $machine->id = $machine_id;
      $machine->read();

      if ($machine->machine_code != null) {
        $code = 200;
        $response = ['message' => 'Up and alive!'];
      } else {
        $code = 404;
        $response = ['message' => 'Máquina não encontrada'];
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
echo json_encode($response);
