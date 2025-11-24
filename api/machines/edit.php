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

    $machine_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $machine_code = isset($data->machine_code) ? filter_var($data->machine_code, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $location_name = isset($data->location_name) ? filter_var($data->location_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $api_url = isset($data->api_url) ? filter_var($data->api_url, FILTER_SANITIZE_URL) : '';

    if (empty($machine_id) || empty($machine_code) || empty($location_name) || empty($api_url)) {
      $code = 400;
      $response = ['message' => 'Campos obrigatórios faltando: id, machine_code, location_name, api_url'];
    } else {
      $machine->id = $machine_id;
      $machine->machine_code = $machine_code;
      $machine->location_name = $location_name;
      $machine->api_url = $api_url;

      if ($machine->edit()) {
        $code = 200;
        $response = [
          'message' => 'Máquina atualizada com sucesso',
          'id' => $machine->id
        ];
      } else {
        $code = 400;
        $response = ['message' => 'Não foi possível atualizar a máquina'];
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
