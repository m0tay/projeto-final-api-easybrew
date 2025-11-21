<?php

// Carregar configurações
require_once '../../config.php';
require_once '../../core.php';
$pdo = connectDB($db);
// Carregar classe
require_once '../../objects/User.php';
$user = new User($pdo);

// Obter dados do POST
$data = json_decode(file_get_contents('php://input'));

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
      $response = ["message" => "Registo criado"];
    } else {
      // Erros no pedido - 503 service unavailable
      $code = 503;
      $response = ["error" => "Erro ao criar registo"];
    }
  } else {
    // Erros no pedido - 400 bad request
    $code = 400;
    $response = ["error" => "Erro no pedido: #error"];
  }
} else {
  // Erros no pedido - 400 bad request
  $code = 400;
  $response = ["error" => "Pedido sem informação"];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
