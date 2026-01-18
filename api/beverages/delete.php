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
$response = [];
$id = isset($data->id) ? $data->id : '';

if ($jwt) {
  try {
    $decoded = JWT::decode($jwt, new Key($jwt_conf['key'], $jwt_conf['alg']));

    if ($decoded->data->role !== 'admin') {
      $code = 403;
      $response = ['message' => 'Acesso negado'];
    } else {
      if (empty($id)) {
        $code = 400;
        $response = ['message' => 'ID não fornecido'];
      } else {
        $beverage->id = $id;
        
        if ($beverage->read()) {
          if (!empty($beverage->image)) {
            delete_file(BEVERAGE_PATH . $beverage->image);
          }
        }

        if ($beverage->delete()) {
          $code = 200;
          $response = ['message' => 'Bebida eliminada'];
        } else {
          $code = 400;
          $response = ['message' => 'Erro ao eliminar bebida'];
        }
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
