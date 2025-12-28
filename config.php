<?php

/* * * * * * * * * * * * * * *
 * C O N F I G U R A Ç Ã O
 * 
 */
// Autor
define('AUTHOR', 'Douglas Lobo');
define('ANO_LETIVO', '2025/26');

require_once 'vendor/autoload.php';

$envFile = '.env';
if (file_exists(__DIR__ . '/.env.local')) {
    $envFile = '.env.local';
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $envFile);
$dotenv->load();

/* * * * * * * * * * * * * * *
 * B A S E   D E   D A D O S
 */
# Database credentials from .env
$guru = $_ENV['GURU'];
$dsg_dbo = [
  'host' => $_ENV['DB_HOST'],
  'port' => $_ENV['DB_PORT'],
  'charset' => $_ENV['DB_CHARSET'],
  'dbname' => $_ENV['DB_NAME'],
  'username' => $_ENV['DB_USERNAME_DBO'],
  'password' => $_ENV['DB_PASSWORD_DBO']
];
$dsg_web = [
  'host' => $_ENV['DB_HOST'],
  'port' => $_ENV['DB_PORT'],
  'charset' => $_ENV['DB_CHARSET'],
  'dbname' => $_ENV['DB_NAME'],
  'username' => $_ENV['DB_USERNAME_WEB'],
  'password' => $_ENV['DB_PASSWORD_WEB']
];

/** @var Array $db['host','port','charset','dbname','username','password'] */
# Descomentar utilizador DBO ou WEB
$db = $dsg_dbo;
/* $db = $dsg_web; */

define('API_MODULES', explode(',', $_ENV['API_MODULES']));

$jwt_conf = [
  'key' => $_ENV['JWT_KEY'],
  'iss' => $_ENV['JWT_ISS'],
  'jti' => bin2hex(random_bytes(128)),
  'iat' => time(),
  'nbf' => time(),
  'exp' => time() + (int)$_ENV['JWT_EXP'],
  'alg' => 'HS256',
];

/* * * * * * * * * *
 * D E B U G
 */
define('DEBUG', true);

if (defined('DEBUG') && DEBUG) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  $_DEBUG = '';
}
