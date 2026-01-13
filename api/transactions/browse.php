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

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    $is_admin = $decoded->data->role === 'admin';

    if ($is_admin) {
      $transactions = $transaction->browse();
    } else {
      $transaction->user_id = $decoded->data->id;
      $transactions = $transaction->browseByUser();
    }

    if ($transactions->rowCount() > 0) {
      $code = 200;
      $records = [];
      while ($row = $transactions->fetch(PDO::FETCH_ASSOC)) {
        $records[] = $row;
      }
      $response['records'] = $records;
      $response['message'] = 'Transações obtidas com sucesso';
    } else {
      $code = 404;
      $response['message'] = 'Sem registros';
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
