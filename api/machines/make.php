<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Machine.php';
require_once '../../objects/User.php';
require_once '../../objects/Transaction.php';
require '../../vendor/autoload.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$pdo = connectDB($db);
$machine = new Machine($pdo);
$user = new User($pdo);
$transaction = new Transaction($pdo);

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';
$machine_id = isset($data->machine_id) ? filter_var($data->machine_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$user_id = isset($data->user_id) ? filter_var($data->user_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$beverage_id = isset($data->beverage_id) ? filter_var($data->beverage_id, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';
$preparation = isset($data->preparation) ? filter_var($data->preparation, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if (empty($machine_id) || empty($user_id) || empty($beverage_id) || empty($preparation)) {
      $code = 400;
      $response = ['message' => 'Parâmetros incompletos'];
    } else {
      $user->id = $user_id;
      $user->read();

      if (empty($user->id)) {
        $code = 404;
        $response = ['message' => 'Utilizador não encontrado'];
      } else if (!$user->is_active) {
        $code = 403;
        $response = ['message' => 'Utilizador inativo'];
      } else {
        $machine->id = $machine_id;
        $machine->read();

        if (empty($machine->id)) {
          $code = 404;
          $response = ['message' => 'Máquina não encontrada'];
        } else if (!$machine->is_active) {
          $code = 403;
          $response = ['message' => 'Máquina inativa'];
        } else if (empty($machine->api_address)) {
          $code = 400;
          $response = ['message' => 'Máquina não possui URL de API configurado'];
        } else {
          $start_time = microtime(true);

          $payload = json_encode([
            'beverage_id' => $beverage_id,
            'preparation' => $preparation
          ]);

          $ch = curl_init($machine->api_address . '/' . $machine->machine_code . '/' . 'api/make.php');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
          ]);
          curl_setopt($ch, CURLOPT_TIMEOUT, 10);
          curl_setopt($ch, CURLOPT_HEADER, false);
          $response_body = curl_exec($ch);
          $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          $curl_error = curl_error($ch);
          curl_close($ch);

          $end_time = microtime(true);
          $response_time = round(($end_time - $start_time) * 1000, 2);

          $machine_response = json_decode($response_body, true);

          if ($http_code === 200 && !$curl_error && $machine_response) {
            $beverage_price = $machine_response['price'] ?? 0;
            $beverage_name = $machine_response['beverage'] ?? 'Unknown';

            if ($user->balance < $beverage_price) {
              $code = 402;
              $response = ['message' => 'Saldo insuficiente'];
            } else {
              $pdo->beginTransaction();

              try {
                $new_balance = $user->balance - $beverage_price;
                $update_query = "UPDATE users SET balance = :balance WHERE id = :user_id";
                $update_stmt = $pdo->prepare($update_query);
                $update_stmt->bindValue(':balance', $new_balance);
                $update_stmt->bindValue(':user_id', $user_id);
                $update_stmt->execute();

                $transaction->user_id = $user_id;
                $transaction->machine_id = $machine_id;
                $transaction->beverage_id = $beverage_id;
                $transaction->beverage_name = $beverage_name;
                $transaction->preparation_chosen = $preparation;
                $transaction->amount = $beverage_price;
                $transaction->type = 'purchase';
                $transaction->status = 'completed';
                $transaction->add();

                $pdo->commit();

                $code = 200;
                $response = [
                  'message' => 'Bebida preparada com sucesso',
                  'beverage' => $beverage_name,
                  'preparation' => $preparation,
                  'price' => $beverage_price,
                  'new_balance' => $new_balance,
                  'response_time_ms' => $response_time,
                ];
              } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
              }
            }
          } else {
            $code = 503;
            $response = [
              'message' => 'Falha ao preparar bebida',
              'error' => $curl_error ?: 'HTTP ' . $http_code,
              'response_body' => $response_body,
              'machine_code' => $machine->machine_code,
            ];
          }
        }
      }
    }
  } catch (Exception $e) {
    $code = 500;
    $response = ['message' => 'Erro: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Acesso negado'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
