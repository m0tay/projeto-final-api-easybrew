<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';
require_once '../../vendor/autoload.php';

$pdo = connectDB($db);
$user = new User($pdo);

use \Firebase\JWT\JWT;

$data = json_decode(file_get_contents('php://input'));

$code = 400;
$response = ['message' => 'Dados inválidos'];

if (!empty($data)) {
  $user->email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : '';
  $user->password_hash = isset($data->password) ? $data->password : '';
  
  $error = '';
  
  if (empty($data->email)) {
    $error .= 'Email não definido. ';
  }

  if (empty($data->password)) {
    $error .= 'Password não definida. ';
  }
  
  if ($error == '') {
    if ($user->emailExists()) {
      // 409 Conflict
      // Indicates that the request could not be processed because of conflict in the current state of the resource
      $code = 409;
      $response = ['message' => 'Email já registado'];
    } else {
      if ($user->add()) {
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
        $response = ['message' => 'Registado com sucesso', 'jwt' => $jwt];
      } else {
        $code = 500;
        $response = ['message' => 'Erro ao registar utilizador'];
      }
    }
  } else {
    $response = ['message' => $error];
  }
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
