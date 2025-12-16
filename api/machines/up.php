<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Machine.php';
require '../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$pdo = connectDB($db);
$machine = new Machine($pdo);

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';
$machine_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if (!isset($decoded->data->role) || $decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: apenas administradores'];
    } else if (empty($machine_id)) {
      $code = 400;
      $response = ['message' => 'ID da máquina não fornecido'];
    } else {

      $machine->id = $machine_id;
      $machine->read();

      if (empty($machine->id)) {
        $code = 404;
        $response = ['message' => 'Máquina não encontrada'];
      } else if (empty($machine->api_address)) {
        $code = 400;
        $response = ['message' => 'Máquina não possui URL de API configurado'];
      } else {
        $api_up = $machine->api_address . '/' . $machine->machine_code . '/' .  'api/up.php';

        $start_time = microtime(true);

        $ch = curl_init($api_up);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $response_body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        $end_time = microtime(true);
        $response_time = round(($end_time - $start_time) * 1000, 2); // milliseconds

        if ($httpCode === 200 && !$curl_error) {
          $code = 200;
          $response = [
            'message' => 'Máquina online',
            'status' => 'online',
            'response_body' => $response_body,
            'response_time_ms' => $response_time,
            'machine_code' => $machine->machine_code,
            'location' => $machine->location_name,
          ];
        } else {
          $code = 503;
          $response = [
            'message' => 'Máquina offline',
            'status' => 'offline',
            'error' => $curl_error ?: 'HTTP ' . $httpCode,
            'response_time_ms' => $response_time,
            'response_body' => $response_body,
            'machine_code' => $machine->machine_code,
            'location' => $machine->location_name,
          ];
        }
      }
    }
  } catch (Exception $e) {
    $code = 401;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
