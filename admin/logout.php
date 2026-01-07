<?php
session_start();
session_destroy();

if (!defined('ADMIN_BASE_PATH')) {
    define('ADMIN_BASE_PATH', '/tesp-ds-g24/projeto/admin');
}
header('Location: ' . ADMIN_BASE_PATH . '/login.php');
exit;
