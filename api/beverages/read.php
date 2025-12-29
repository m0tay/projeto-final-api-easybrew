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
$beverage_id = isset($data->id) ? filter_var($data->id, FILTER_UNSAFE_RAW) : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if (empty($beverage_id)) {
      $code = 400;
      $response = ['message' => 'ID da bebida não fornecido'];
    } else {
      $beverage->id = $beverage_id;
      
      if ($beverage->read()) {
        $code = 200;
        $response = [
          'id' => $beverage->id,
          'name' => $beverage->name,
          'type' => $beverage->type,
          'size' => $beverage->size,
          'preparation' => $beverage->preparation,
          'price' => $beverage->price,
          'description' => $beverage->description,
          'image' => $beverage->image,
          'is_active' => $beverage->is_active
        ];
      } else {
        $code = 404;
        $response = ['message' => 'Bebida não encontrada'];
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
