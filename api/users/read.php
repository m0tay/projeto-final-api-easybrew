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
$user_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if (empty($user_id)) {
      $code = 400;
      $response = ['message' => 'ID do utilizador não fornecido'];
    } else {
      if ($decoded->data->id != $user_id && $decoded->data->role !== 'admin') {
        $code = 403;
        $response = ['message' => 'Acesso negado: Permissões insuficientes'];
      } else {
        $user->id = $user_id;
        $user->read();

        if ($user->email != null) {
          $code = 200;
          $response = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
          'balance' => $user->balance,
          'is_active' => $user->is_active
        ];
      } else {
          $code = 404;
          $response = ['message' => 'Utilizador não encontrado'];
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
