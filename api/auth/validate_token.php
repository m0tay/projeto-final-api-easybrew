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
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));
    
    // Carregar dados atualizados do usuário do banco de dados
    $user->id = $decoded->data->id;
    $user->read();
    
    // Verificar se o usuário ainda está ativo
    if (!boolval($user->is_active)) {
      $code = 403;
      $response = ['message' => 'Conta desativada'];
    } else if (!boolval($user->email_verified)) {
      $code = 403;
      $response = ['message' => 'Email não verificado'];
    } else {
      // Gerar novo token com dados atualizados
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
      $new_jwt = JWT::encode($token, $jwt_conf['key'], $jwt_conf['alg']);
      
      $code = 200;
      $response = [
        'message' => 'Acesso autorizado',
        'jwt' => $new_jwt,
        'data' => array(
          'id' => $user->id,
          'email' => $user->email,
          'first_name' => $user->first_name,
          'last_name' => $user->last_name,
          'role' => $user->role,
          'balance' => $user->balance,
          'is_active' => $user->is_active
        ),
        'expiration' => date('c', $decoded->exp),
        'timeleft' => $decoded->exp - time()
      ];
    }
  } catch (Exception $e) {
    $code = 403;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Token não fornecido'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
