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
$response = [];
$user_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: Permissões insuficientes'];
    } else {
      if (empty($user_id)) {
        $code = 400;
        $response = ['message' => 'ID do utilizador não fornecido'];
      } else {
        $user->id = $user_id;
        
        if ($user->read()) {
          if (!empty($user->avatar) && $user->avatar !== AVATAR_DEFAULT) {
            delete_file(AVATAR_PATH . $user->avatar);
          }
        }

        if ($user->delete()) {
          $code = 200;
          $response = ['message' => 'Utilizador deletado com sucesso'];
        } else {
          $code = 400;
          $response = ['message' => 'Não foi possível deletar o utilizador'];
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
