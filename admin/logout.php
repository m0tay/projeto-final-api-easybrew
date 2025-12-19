<?php
session_start();
session_destroy();
header('Location: ' . $_ENV['URL_BASE'] . 'admin/login.php');
exit;
