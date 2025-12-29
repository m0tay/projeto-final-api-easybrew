<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/Beverage.php';
require '../../vendor/autoload.php';

$pdo = connectDB($db);
$beverage = new Beverage($pdo);

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$data = json_decode(file_get_contents('php://input'));

$jwt = isset($data->jwt) ? $data->jwt : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado'];
    } else {
      $beverages = $beverage->browse();

      if ($beverages->rowCount() > 0) {
        $code = 200;
        $records = [];
        while ($row = $beverages->fetch(PDO::FETCH_ASSOC)) {
          $records[] = $row;
        }
        $response = ['message' => 'Bebidas encontradas', 'records' => $records];
      } else {
        $code = 404;
        $response = ['message' => 'Sem bebidas'];
      }
    }
  } catch (Exception $e) {
    $code = 401;
    $response = ['message' => 'Acesso negado: ' . $e->getMessage()];
  }
} else {
  $code = 401;
  $response = ['message' => 'Token JWT não fornecido'];
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
