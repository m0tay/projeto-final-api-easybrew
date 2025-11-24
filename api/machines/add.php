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

    $machine_code = isset($data->machine_code) ? filter_var($data->machine_code, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $location_name = isset($data->location_name) ? filter_var($data->location_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
    $api_url = isset($data->api_url) ? filter_var($data->api_url, FILTER_SANITIZE_URL) : '';

    if (empty($machine_code) || empty($location_name) || empty($api_url)) {
      $code = 400;
      $response = ['message' => 'Campos obrigatórios faltando: machine_code, location_name, api_url'];
    } else {
      $machine->machine_code = $machine_code;
      $machine->location_name = $location_name;
      $machine->api_url = $api_url;

      if ($machine->add()) {
        $code = 201;
        $response = [
          'message' => 'Máquina adicionada com sucesso',
          'id' => $machine->id
        ];
      } else {
        $code = 400;
        $response = ['message' => 'Não foi possível adicionar a máquina'];
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
