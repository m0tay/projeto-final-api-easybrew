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
      if (!empty($data)) {
        $beverage->id = isset($data->id) ? $data->id : '';
        $beverage->name = isset($data->name) ? $data->name : '';
        $beverage->type = isset($data->type) ? $data->type : '';
        $beverage->size = isset($data->size) ? $data->size : '';
        $beverage->preparation = isset($data->preparation) ? $data->preparation : '';
        $beverage->price = isset($data->price) ? $data->price : 0;
        $beverage->description = isset($data->description) ? $data->description : '';
        $beverage->image = isset($data->image) ? $data->image : '';
        $beverage->is_active = isset($data->is_active) ? filter_var($data->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : 1;

        if (empty($beverage->id) || empty($beverage->name)) {
          $code = 400;
          $response = ['message' => 'ID e nome obrigatórios'];
        } else {
          if ($beverage->edit()) {
            $code = 200;
            $response = ['message' => 'Bebida atualizada'];
          } else {
            $code = 503;
            $response = ['message' => 'Erro ao atualizar bebida'];
          }
        }
      } else {
        $code = 400;
        $response = ['message' => 'Sem dados'];
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
