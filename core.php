<?php

/**
 * @param Array $db
 * @return \PDO
 */
function connectDB($db)
{
  try {
    $pdo = new PDO(
      'mysql:host=' . $db['host'] . '; ' .
        'port=' . $db['port'] . ';' .
        'charset=' . $db['charset'] . ';' .
        'dbname=' . $db['dbname'] . ';',
      $db['username'],
      $db['password']
    );
  } catch (PDOException $e) {
    die('Erro ao ligar ao servidor ' . $e->getMessage());
  }
  
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
  return $pdo;
}


/**
 * @param mixed $info
 * @param string $type
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
 * @param array $info
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
  }
  
  return null;
}

/**
 * @return boolean
 */
function is_admin()
{
  return (isset($_SESSION['email']) && $_SESSION['profile'] == 'admin');
}

/**
 * @param string $path
 * @return bool
 */
function create_dir($path)
{
  if (!file_exists($path)) {
    return mkdir($path, 0755, true);
  }
  return true;
}

/**
 * @param string $filepath
 * @return bool
 */
function delete_file($filepath)
{
  if (is_file($filepath)) {
    return unlink($filepath);
  }
  return false;
}

/**
 * @param string $path
 * @return bool
 */
function delete_dir($path)
{
  if (!is_dir($path)) {
    return false;
  }
  
  $files = array_diff(scandir($path), ['.', '..']);
  foreach ($files as $file) {
    $filepath = $path . DIRECTORY_SEPARATOR . $file;
    is_dir($filepath) ? delete_dir($filepath) : unlink($filepath);
  }
  
  return rmdir($path);
}

/**
 * Converte string para slug (nome de ficheiro seguro)
 * @param string $string
 * @return string
 */
function slugify($string)
{
  $string = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $string);
  $string = preg_replace('/-+/', '-', $string);
  $string = trim($string, '-');
  return strtolower($string);
}

/**
 * Obtém o caminho web do avatar de um utilizador
 * @param string $avatar Nome do ficheiro do avatar
 * @return string
 */
function get_avatar_url($avatar)
{
  if (empty($avatar) || !file_exists(AVATAR_PATH . $avatar)) {
    return AVATAR_WEB_PATH . AVATAR_DEFAULT;
  }
  return AVATAR_WEB_PATH . $avatar;
}

function get_beverage_image_url($image)
{
  if (empty($image) || !file_exists(BEVERAGE_PATH . $image)) {
    return BEVERAGE_WEB_PATH . BEVERAGE_DEFAULT;
  }
  return BEVERAGE_WEB_PATH . $image;
}
