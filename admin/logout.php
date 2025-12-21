<?php
session_start();
session_destroy();

require_once __DIR__ . '/config_local.php';
header('Location: ' . ADMIN_BASE_PATH . '/login.php');
exit;
