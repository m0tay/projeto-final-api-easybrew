<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';
require '../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$pdo = connectDB($db);
$user = new User($pdo);
$data = json_decode(file_get_contents('php://input'));
$jwt = isset($data->jwt) ? $data->jwt : "";
$response = [];

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    $target_user_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : $decoded->data->id;

    if ($decoded->data->id != $target_user_id && $decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: Permissões insuficientes'];
    } else {
      $user->id = $target_user_id;
      $user->first_name = isset($data->first_name) ? filter_var($data->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
      $user->last_name = isset($data->last_name) ? filter_var($data->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
      $user->email = isset($data->email) ? filter_var($data->email, FILTER_VALIDATE_EMAIL) : '';
      $user->password_hash = isset($data->password) ? $data->password : '';
      $user->avatar = isset($data->avatar) ? filter_var($data->avatar, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

      $is_admin = $decoded->data->role === 'admin';
      $has_admin_fields = isset($data->role) || isset($data->balance) || isset($data->is_active);

      if ($has_admin_fields && !$is_admin) {
        $code = 403;
        $response = ['message' => 'Acesso negado: Não pode modificar role, balance ou is_active'];
      } else {
        if (empty($user->first_name) || empty($user->last_name) || empty($user->email)) {
          $code = 400;
          $response = ['message' => 'Campos obrigatórios faltando: first_name, last_name, email'];
        } else {
          if ($is_admin && $has_admin_fields) {
            $user->role = isset($data->role) ? filter_var($data->role, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
            $user->balance = isset($data->balance) ? filter_var($data->balance, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
            $user->is_active = isset($data->is_active) ? filter_var($data->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

            $update_result = $user->adminEdit();
          } else {
            $update_result = $user->edit();
          }

          if ($update_result) {
            // user editou a si mesmo, precisa gerar novo token
            if ($decoded->data->id == $target_user_id) {
              $new_role = ($is_admin && isset($data->role)) ? $user->role : $decoded->data->role;

              $token = [
                "iss" => $jwt_conf['iss'],
                "jti" => $jwt_conf['jti'],
                "iat" => $jwt_conf['iat'],
                "nbf" => $jwt_conf['nbf'],
                "exp" => $jwt_conf['exp'],
                "data" => [
                  "id" => $user->id,
                  "first_name" => $user->first_name,
                  "last_name" => $user->last_name,
                  "email" => $user->email,
                  "role" => $new_role
                ],
              ];
              $code = 200;
              $jwt = JWT::encode($token, $jwt_conf['key'], $jwt_conf['alg']);
              $response = ['message' => 'Atualizado com sucesso', 'jwt' => $jwt];
            } else {
              $code = 200;
              $response = ['message' => 'Utilizador atualizado com sucesso'];
            }
          } else {
            $code = 503;
            $response = ['message' => 'Erro ao atualizar o utilizador'];
          }
        }
      }
    }
  } catch (Exception $e) {
    $code = 500;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado: Token JWT não fornecido'];
}

header("Content-Type: application/json; charset=UTF-8");
http_response_code($code);
echo json_encode($response);
