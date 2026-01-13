<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$user = new User($pdo);

use \Firebase\JWT\JWT;

$data = json_decode(file_get_contents('php://input'));

$code = 400;
$response = ['message' => 'Dados não fornecidos'];

if (!empty($data)) {
  $user->email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : '';
  $plain_password = isset($data->password) ? $data->password : '';

  $error = '';
  if ($plain_password == '') {
    $error .= 'Password não definida. ';
  }
  if ($user->email == '') {
    $error .= 'Email não definido. ';
  }
  if ($error == '') {
    if (!$user->emailExists()) {
      $code = 401;
      $response = ['message' => 'Credenciais inválidas'];
    } else {
      if (!boolval($user->is_active)) {
        $code = 403;
        $response = ['message' => 'Conta desativada'];
      } else if (!boolval($user->email_verified)) {
        $code = 403;
        $response = ['message' => 'Email não verificado. Verifica a tua caixa de entrada.'];
      } else if (password_verify($plain_password, $user->password_hash)) {
        $token = array(
          'iss' => $jwt_conf['iss'],
          'jti' => $jwt_conf['jti'],
          'iat' => $jwt_conf['iat'],
          'nbf' => $jwt_conf['nbf'],
          'exp' => $jwt_conf['exp'],
          'data' => array(
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'balance' => $user->balance,
            'is_active' => $user->is_active
          )
        );
        $jwt = JWT::encode($token, $jwt_conf['key'], $jwt_conf['alg']);
        $code = 200;
        $response = ['message' => 'Autenticado com sucesso', 'jwt' => $jwt];
      } else {
        $code = 401;
        $response = ['message' => 'Credenciais inválidas'];
      }
    }
  } else {
    $code = 400;
    $response = ['message' => $error];
  }
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
