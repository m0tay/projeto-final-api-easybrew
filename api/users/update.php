<?php

// Carregar configurações
require_once '../../config.php';
require_once '../../core.php';
$pdo = connectDB($db);
// Carregar classe
require_once '../../objects/User.php';
$user = new User($pdo);

// Carregar JWT
require '../../../vendor/autoload.php';

use \Firebase\JWT\JWT;


// Obter dados do POST
$data = json_decode(file_get_contents('php://input'));
$user->first_name = filter_var($data->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$user->last_name = filter_var($data->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$user->email = filter_var($data->email, FILTER_VALIDATE_EMAIL);

// Obter JWT
$jwt = isset($data->jwt) ? $data->jwt : "";
if ($jwt) {
  try {
    // Decode do JWT
    $decoded = JWT::decode($jwt, $jwt_conf['key'], array('HS256'));
    // Definição do ID de utilizador
    $user->id = filter_var($decoded->data->id, FILTER_SANITIZE_NUMBER_INT);
    // Atualizar
    if ($user->edit()) {
      // Gerar novo token
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
          "email" => $user->email
        ],
      ];
      $code = 200;
      // Criar novo JWT
      $jwt = JWT::encode($token, $jwt_conf['key']);
      // Enviar resposta
      $response = ['message' => 'Atualizado com sucesso', 'jwt' => $jwt];
    } else {
      // Erro ao atualizar - 503 service unavailable
      $code = 503;
      // Enviar resposta com mensagens de erro
      $response = ['error' => 'Erro ao atualizar o registro'];
    }
  } catch (Exception $e) {
    // Acesso negado - 401 Unauthorized
    $code = 401;
    // Enviar resposta com mensagens de erro
    $response = ['error' => 'Acesso negado'];
  }
} else {
  // Acesso negado - 401 Unauthorized
  $code = 401;
  $response = ['error' => 'Acesso negado'];
}

header("Content-Type: application/json; charset=UTF-8");
http_response_code($code);
echo json_encode($response);
