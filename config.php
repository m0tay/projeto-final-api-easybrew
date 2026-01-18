<?php

/* * * * * * * * * * * * * * *
 * C O N F I G U R A Ç Ã O
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
$guru = $_ENV['GURU'];
$dsg_dbo = [
  'host' => $_ENV['DB_HOST'],
  'port' => $_ENV['DB_PORT'],
  'charset' => 'utf8',
  'dbname' => 'esan-dsg24',
  'username' => $_ENV['DB_USERNAME_DBO'],
  'password' => $_ENV['DB_PASSWORD_DBO']
];
$dsg_web = [
  'host' => $_ENV['DB_HOST'],
  'port' => $_ENV['DB_PORT'],
  'charset' => 'utf8',
  'dbname' => 'esan-dsg24',
  'username' => $_ENV['DB_USERNAME_WEB'],
  'password' => $_ENV['DB_PASSWORD_WEB']
];

/** @var Array $db['host','port','charset','dbname','username','password'] */
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

/* * * * * * * * * * * * * * *
 * E M A I L
 */
define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_ENCODING', 'base64');
define('EMAIL_HOST', 'smtp-servers.ua.pt');
define('EMAIL_SMTPAUTH', true);
define('EMAIL_USERNAME', 'esan-tesp-ds-paw@ua.pt');
define('EMAIL_PASSWORD', '8ee83a66c46001b7ee7b3ee886bf8375');
define('EMAIL_PORT', 25);
define('EMAIL_FROM', 'Projeto Desenvolvimento de Software | ESAN');

define('WEB_SERVER', 'https://esan-tesp-ds-paw.web.ua.pt');
define('WEB_ROOT', '/tesp-ds-g24/projeto/');

/* * * * * * * * * * * * * * *
 * U P L O A D S
 */
define('SERVER_FILE_ROOT', '//arca.ua.pt/Hosting/esan-tesp-ds-paw.web.ua.pt' . WEB_ROOT);
define('UPLOAD_FOLDER', 'uploads/');
define('UPLOAD_PATH', '//arca.ua.pt/Hosting/esan-tesp-ds-paw.web.ua.pt/tesp-ds-g24/' . UPLOAD_FOLDER);

define('AVATAR_FOLDER', UPLOAD_FOLDER . 'avatar/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatar/');
define('AVATAR_WEB_PATH', '/tesp-ds-g24/' . UPLOAD_FOLDER . 'avatar/');
define('AVATAR_DEFAULT', 'avatar.png');

define('BEVERAGE_FOLDER', UPLOAD_FOLDER . 'beverages/');
define('BEVERAGE_PATH', UPLOAD_PATH . 'beverages/');
define('BEVERAGE_WEB_PATH', '/tesp-ds-g24/' . UPLOAD_FOLDER . 'beverages/');
define('BEVERAGE_DEFAULT', 'beverage.png');

define('ATTACHMENTS_PATH', UPLOAD_PATH . 'attach/');

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
