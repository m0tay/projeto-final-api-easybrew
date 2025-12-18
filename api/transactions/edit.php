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

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado: Apenas administradores podem editar transações'];
    } else {
      if (!empty($data)) {
        $transaction_id = isset($data->id) ? filter_var($data->id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
        
        if (empty($transaction_id)) {
          $code = 400;
          $response = ['message' => 'ID da transação não fornecido'];
        } else {
          $transaction->id = $transaction_id;
          $transaction->read();
          
          if ($transaction->user_id == null) {
            $code = 404;
            $response = ['message' => 'Transação não encontrada'];
          } else {
            $transaction->type = isset($data->type) ? filter_var($data->type, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : $transaction->type;
            $transaction->status = isset($data->status) ? filter_var($data->status, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : $transaction->status;
            
            $error = '';
            
            if (!in_array($transaction->type, ['purchase', 'deposit', 'refund'])) {
              $error .= 'Tipo de transação inválido. ';
            }
            if (!in_array($transaction->status, ['completed', 'failed', 'refunded'])) {
              $error .= 'Status inválido. ';
            }
            
            if ($error == '') {
              if ($transaction->edit()) {
                $code = 200;
                $response = ['message' => 'Transação atualizada com sucesso'];
              } else {
                $code = 503;
                $response = ['message' => 'Erro ao atualizar transação'];
              }
            } else {
              $code = 400;
              $response = ['message' => $error];
            }
          }
        }
      } else {
        $code = 400;
        $response = ['message' => 'Pedido sem informação'];
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
