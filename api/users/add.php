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

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], 'HS256'));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: Permissões insuficientes'];
    } else {
      if (!empty($data)) {
        // Validar dados
        $user->password_hash = isset($data->password) ? filter_var($data->password, FILTER_UNSAFE_RAW) : '';
        $user->email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : '';

        $error = '';
        if ($user->password_hash == '') {
          $error .= 'Password não definida. ';
        }
        if ($user->email == '') {
          $error .= 'Email não definido. ';
        }
        if ($user->emailExists()) {
          $error .= 'Email já registado. ';
        }
        if ($error == '') {
          // Criar Utilizador
          if ($user->add()) {
            // Sucesso na criação - 201 created
            $code = 201;
            $response = ['message' => 'Utilizador criado com sucesso', 'id' => $user->id];
          } else {
            // Erros no pedido - 503 service unavailable
            $code = 503;
            $response = ['message' => 'Erro ao criar utilizador'];
          }
        } else {
          // Erros no pedido - 400 bad request
          $code = 400;
          $response = ['message' => $error];
        }
      } else {
        // Erros no pedido - 400 bad request
        $code = 400;
        $response = ['message' => 'Pedido sem informação'];
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
