<?php

/**
 * Cria uma ligação a uma base de dados e devolve um objeto PDO com a ligação
 * @param Array $db
 * @return \PDO
 */
function connectDB($db)
{
  try {
    $pdo = new PDO(
      'mysql:host=' . $db['host'] . '; ' . // string de ligação
        'port=' . $db['port'] . ';' . // string de ligação
        'charset=' . $db['charset'] . ';' . // string de ligação
        'dbname=' . $db['dbname'] . ';', // string de ligação
      $db['username'], // username
      $db['password']                         // password
    );
  } catch (PDOException $e) {
    die('Erro ao ligar ao servidor ' . $e->getMessage());
  }
  // Definir array associativo como default para fetch()
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

  // Definir lançamento de exceção para erros PDO
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $pdo;
}


/**
 * Verifica se o modo DEBUG está definido e ativo e escreve na consola do browser
 * @param mixed $info
 * @param sting $type [log, error, info]
 * @return bool
 */
function debug($info = '', $type = 'log')
{
  if (defined('DEBUG') && DEBUG) {
    echo "<script>console.$type(" . json_encode($info, JSON_PRETTY_PRINT) . ");</script>";
    return true;
  }
  return false;
}

/**
 * Verifica se o modo DEBUG está definido e ativo e acrescenta $info ao array $_DEBUG
 * @global array $_DEBUG
 * @param array $info array("key"=>"value")
 * @return bool
 */
function debug_array($info = '')
{
  if (defined('DEBUG') && DEBUG) {
    global $_DEBUG;
    $_DEBUG = $info;
    return true;
  }
  return false;
}

/**
 * Obtém módulo a partir do PATHINFO e valida
 * Devolve false caso não possua módulo ou o mesmo não seja válido
 * @return mixed
 */
function get_path_module()
{
  $pathinfo = filter_input(INPUT_SERVER, 'PATH_INFO');
  if (empty($pathinfo)) {
    return false;
  }
  $patharray = explode("/", $pathinfo);
  if (in_array($patharray[1], API_MODULES)) {
    return $patharray[1];
  } else {
    return false;
  }
}

/**
 * Obtém id a partir do PATHINFO e valida
 * Devolve null caso não possua id ou id válido
 * @return mixed
 */
function get_path_id()
{
  $pathinfo = filter_input(INPUT_SERVER, 'PATH_INFO');
  if (empty($pathinfo)) {
    return null;
  }
  $patharray = explode("/", $pathinfo);
  if (!empty($patharray) && isset($patharray[2]) && filter_var($patharray[2], FILTER_VALIDATE_INT)) {
    return (int)$patharray[2];
  } else {
    return null;
  }
}

/**
 * Verifica se o utilizador autenticado é admin
 * @return boolean
 */
function is_admin()
{
  if (isset($_SESSION['email']) && $_SESSION['profile'] == 'admin') {
    return true;
  } else {
    return false;
  }
}

