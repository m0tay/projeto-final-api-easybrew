
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
$machine_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], 'HS256'));

    if (!isset($decoded->data->role) || $decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: apenas administradores podem verificar status de máquinas'];
      header('Content-Type: application/json; charset=UTF-8');
      http_response_code($code);
      echo json_encode($response);
      exit();
    }

    if (empty($machine_id)) {
      $code = 400;
      $response = ['message' => 'ID da máquina não fornecido'];
    } else {
      $machine->id = $machine_id;
      $machine->read();

      if ($machine->machine_code == null) {
        $code = 404;
        $response = ['message' => 'Máquina não encontrada'];
      } else {
        if (empty($machine->api_url)) {
          $code = 400;
          $response = ['message' => 'Máquina não tem api_url configurado'];
        } else {
          $health_url = rtrim($machine->api_url, '/') . '/health';
          
          $context = stream_context_create([
            'http' => [
              'timeout' => 5,
              'method' => 'GET',
              'ignore_errors' => true
            ]
          ]);
          
          $start_time = microtime(true);
          $machine_response = @file_get_contents($health_url, false, $context);
          $response_time = round((microtime(true) - $start_time) * 1000, 2); // in ms
          
          if ($machine_response !== false) {
            $machine_data = json_decode($machine_response, true);
            $code = 200;
            $response = [
              'message' => 'Máquina online e respondendo',
              'status' => 'online',
              'machine_code' => $machine->machine_code,
              'location' => $machine->location_name,
              'response_time_ms' => $response_time,
              'machine_data' => $machine_data ?? null
            ];
          } else {
            $code = 503;
            $response = [
              'message' => 'Máquina offline ou não respondendo',
              'status' => 'offline',
              'machine_code' => $machine->machine_code,
              'location' => $machine->location_name,
              'attempted_url' => $health_url
            ];
          }
        }
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
