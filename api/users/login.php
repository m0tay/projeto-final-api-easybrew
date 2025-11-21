<?php
require_once '../../config.php';
require_once '../../core.php';
$pdo = connectDB($db);
// Carregar classe
require_once '../../objects/User.php';
$user = new User($pdo);
// Carregar JWT
require '../../vendor/autoload.php';

use \Firebase\JWT\JWT;
// Obter dados do POST
$data = json_decode(file_get_contents("php://input"));
if (!empty($data)) {
  // Validar dados
  $user->email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
  $plain_password = $data->password;
  $error = '';
  if ($plain_password == '') {
    $error .= 'Password não definida. ';
  }
  if ($user->email == '') {
    $error .= 'Email não definido. ';
  }
  if (!$user->emailExists()) {
    $error .= 'Email não existe. ';
  }
  if ($error == '') {
    if (password_verify($plain_password, $user->password_hash)) {
      // Criar token
      $token = array(
        "iss" => $jwt_conf['iss'],
        "jti" => $jwt_conf['jti'],
        "iat" => $jwt_conf['iat'],
        "nbf" => $jwt_conf['nbf'],
        "exp" => $jwt_conf['exp'],
        "data" => array(
          "id" => $user->id,
          "first_name" => $user->first_name,
          "last_name" => $user->last_name,
          "email" => $user->email
        )
      );
      // Criar JWT
      $jwt = JWT::encode($token, $jwt_conf['key']);
      // Sucesso na autenticação - 200 OK
      $code = 200;
      $response = ['message' => 'Autenticado com sucesso', 'jwt' => $jwt];
    } else {
      // Erros no pedido - 401 
      $code = 401;
      $response = ['message' => 'Erro de autenticação'];
    }
  } else {
    // Erros no pedido - 400 bad request
    $code = 400;
    $response = ["message" => $error];
  }
} else {
  // Erros no pedido - 400 bad request
  $code = 400;
  $response = ["message" => "Dados não fornecidos"];
}

header("Content-Type: application/json; charset=UTF-8");
http_response_code($code);
echo json_encode($response);
