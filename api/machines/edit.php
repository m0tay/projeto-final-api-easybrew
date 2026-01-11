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

    if (!isset($decoded->data->role) || $decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: apenas administradores podem editar máquinas'];
      header('Content-Type: application/json; charset=UTF-8');
      http_response_code($code);
      echo json_encode($response);
      exit();
    }

    $machine_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $machine_code = isset($data->machine_code) ? filter_var($data->machine_code, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $location_name = isset($data->location_name) ? filter_var($data->location_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $api_address = isset($data->api_address) ? filter_var($data->api_address, FILTER_SANITIZE_URL) : '';
    $is_active = isset($data->is_active) ? (filter_var($data->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0) : 1;

    if (empty($machine_id) || empty($machine_code) || empty($location_name) || empty($api_address)) {
      $code = 400;
      $response = ['message' => 'Campos obrigatórios faltando: id, machine_code, location_name, api_address'];
    } else {
      $machine->id = $machine_id;
      $machine->machine_code = $machine_code;
      $machine->location_name = $location_name;
      $machine->api_address = $api_address;
      $machine->is_active = $is_active;


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
