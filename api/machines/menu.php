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
$response = [];
$machine_id = isset($data->machine_id) ? filter_var($data->machine_id, FILTER_SANITIZE_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if (empty($machine_id)) {
      $code = 400;
      $response = ['message' => 'ID da máquina não fornecido'];
    } else {
      $machine->id = $machine_id;

      if (!$machine->read()) {
        $code = 404;
        $response = ['message' => 'Máquina não encontrada', 'machine_id' => $machine_id];
      } else {
        if (!boolval($machine->is_active)) {
          $code = 403;
          $response = [
            'message' => 'Máquina não está ativa',
            'machine_id' => $machine_id,
            'machine_code' => $machine->machine_code,
            'is_active' => $machine->is_active
          ];
        } else {
          $menu_url = $machine->api_address . $machine->machine_code . '/api/menu.php';
          $host = parse_url($menu_url, PHP_URL_HOST);

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $menu_url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_TIMEOUT, 5);
          curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; Host: ' . $host]);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

          $menu_response = curl_exec($ch);
          $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $curl_error = curl_error($ch);
          curl_close($ch);

          if ($menu_response === false || $http_code !== 200) {
            $code = 503;
            $response = [
              'message' => 'Erro ao comunicar com a máquina',
              'error' => $curl_error ?: 'HTTP ' . $http_code,
              'machine_url' => $menu_url
            ];
          } else {
            $menu_data = json_decode($menu_response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
              $code = 500;
              $response = ['message' => 'Erro ao processar resposta da máquina'];
            } else {
              $code = 200;
              $response = [
                'records' => $menu_data['records'] ?? []
              ];
            }
          }
        }
      }
    }
  } catch (Exception $e) {
    $code = 500;
    $response = ['message' => 'Erro: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado: Token não fornecido'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
