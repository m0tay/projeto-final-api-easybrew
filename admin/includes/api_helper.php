<?php

if (!defined('ADMIN_BASE_PATH')) {
    define('ADMIN_BASE_PATH', '/tesp-ds-g24/projeto/admin');
}

function callAPI($endpoint, $data = []) {
    if (isset($_SESSION['jwt'])) {
        $data['jwt'] = $_SESSION['jwt'];
    }
   
    $url = $_ENV['URL_BASE'] . 'api/' . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode == 401) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header('Location: ' . ADMIN_BASE_PATH . '/login.php?erro=sessao_expirada');
        exit;
    }
    
    return $result;
}

function requireLogin() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ' . ADMIN_BASE_PATH . '/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: ' . ADMIN_BASE_PATH . '/index.php?erro=permissao_negada');
        exit;
    }
}

function getUser() {
    return $_SESSION['user'] ?? null;
}

function showAlert($message, $type = 'danger') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' 
           . htmlspecialchars($message) 
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}
