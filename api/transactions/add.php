<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Transaction.php';
require_once '../../objects/User.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$transaction = new Transaction($pdo);
$user = new User($pdo);

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';
$response = [];

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if (!empty($data)) {
      $transaction->user_id = isset($data->user_id) ? filter_var($data->user_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : $decoded->data->id;
      $transaction->machine_id = isset($data->machine_id) ? filter_var($data->machine_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
      $transaction->beverage_id = isset($data->beverage_id) ? filter_var($data->beverage_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
      $transaction->beverage_name = isset($data->beverage_name) ? filter_var($data->beverage_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
      $transaction->preparation_chosen = isset($data->preparation_chosen) ? filter_var($data->preparation_chosen, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
      $transaction->amount = isset($data->amount) ? filter_var($data->amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
      $transaction->type = isset($data->type) ? filter_var($data->type, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
      $transaction->status = isset($data->status) ? filter_var($data->status, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'completed';

      if ($decoded->data->id != $transaction->user_id && $decoded->data->role !== 'admin') {
        $code = 403;
        $response = ['message' => 'Acesso negado: Não pode criar transações para outros utilizadores'];
      } else {
        $error = '';

        if ($transaction->amount <= 0) {
          $error .= 'Valor inválido. ';
        }

        if (!in_array($transaction->type, ['purchase', 'deposit', 'refund'])) {
          $error .= 'Tipo de transação inválido. ';
        }

        if (!in_array($transaction->status, ['completed', 'failed', 'refunded'])) {
          $error .= 'Status inválido. ';
        }

        if ($transaction->type === 'purchase') {
          if (empty($transaction->beverage_id)) {
            $error .= 'ID da bebida não fornecido. ';
          }
          if (empty($transaction->beverage_name)) {
            $error .= 'Nome da bebida não fornecido. ';
          }
          if ($transaction->preparation_chosen && !in_array($transaction->preparation_chosen, ['hot', 'warm', 'iced'])) {
            $error .= 'Preparação inválida. ';
          }
        }

        if ($transaction->type === 'deposit') {
          if (empty($transaction->beverage_id)) {
            $transaction->beverage_id = '0';
          }
          if (empty($transaction->beverage_name)) {
            $transaction->beverage_name = 'Deposit';
          }
          $transaction->machine_id = null;
          $transaction->preparation_chosen = null;
        }

        if ($error == '') {
          $pdo->beginTransaction();

          try {
            if ($transaction->type === 'purchase' && $transaction->status === 'completed') {
              $user->id = $transaction->user_id;
              $user->read();

              if ($user->balance < $transaction->amount) {
                $pdo->rollBack();
                $code = 400;
                $response = ['message' => 'Saldo insuficiente'];
              } else {
                $user->deductBalance($transaction->amount);

                if ($transaction->add()) {
                  $pdo->commit();
                  $code = 201;
                  $response = ['message' => 'Transação criada com sucesso', 'id' => $transaction->id];
                } else {
                  $pdo->rollBack();
                  $code = 503;
                  $response = ['message' => 'Erro ao criar transação'];
                }
              }
            } elseif ($transaction->type === 'deposit' && $transaction->status === 'completed') {
              $user->id = $transaction->user_id;
              $user->read();
              $user->addBalance($transaction->amount);

              if ($transaction->add()) {
                $pdo->commit();
                $code = 200;
                $response = ['message' => 'Depósito criado com sucesso'];
              } else {
                $pdo->rollBack();
                $code = 503;
                $response = ['message' => 'Erro ao criar depósito'];
              }
            } else {
              if ($transaction->add()) {
                $pdo->commit();
                $code = 201;
                $response = ['message' => 'Transação criada com sucesso', 'id' => $transaction->id];
              } else {
                $pdo->rollBack();
                $code = 503;
                $response = ['message' => 'Erro ao criar transação'];
              }
            }
          } catch (Exception $e) {
            $pdo->rollBack();
            $code = 503;
            $response = ['message' => 'Erro ao processar transação: ' . $e->getMessage()];
          }
        } else {
          $code = 400;
          $response = ['message' => $error];
        }
      }
    } else {
      $code = 400;
      $response = ['message' => 'Pedido sem informação'];
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
die();
