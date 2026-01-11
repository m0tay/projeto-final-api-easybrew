<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Transaction.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$transaction = new Transaction($pdo);

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';
$response = [];
$transaction_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: Apenas administradores podem deletar transações'];
    } else {
      if (empty($transaction_id)) {
        $code = 400;
        $response = ['message' => 'ID da transação não fornecido'];
      } else {
        $transaction->id = $transaction_id;

        if ($transaction->delete()) {
          $code = 200;
          $response = ['message' => 'Transação deletada com sucesso'];
        } else {
          $code = 400;
          $response = ['message' => 'Não foi possível deletar a transação'];
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
