<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';

$pdo = connectDB($db);
$user = new User($pdo);

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

$login_url = WEB_SERVER . WEB_ROOT . 'admin/login.php';

if (empty($token)) {
  header('Location: ' . $login_url . '?erro=token_invalido');
  exit;
}

$query = "SELECT * FROM users WHERE email_verification_token = :token AND email_verification_expires > NOW()";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() > 0) {
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $user_id = $row['id'];
  
  $update_query = "UPDATE users SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL, is_active = 1 WHERE id = :id";
  $update_stmt = $pdo->prepare($update_query);
  $update_stmt->bindParam(':id', $user_id);
  
  if ($update_stmt->execute()) {
    header('Location: ' . $login_url . '?sucesso=email_confirmado');
  } else {
    header('Location: ' . $login_url . '?erro=erro_confirmacao');
  }
} else {
  // Check if token exists but is expired
  $check_query = "SELECT * FROM users WHERE email_verification_token = :token";
  $check_stmt = $pdo->prepare($check_query);
  $check_stmt->bindParam(':token', $token);
  $check_stmt->execute();
  
  if ($check_stmt->rowCount() > 0) {
    // Token exists but expired - user can request resend
    header('Location: ' . $login_url . '?erro=token_expirado&pode_reenviar=1');
  } else {
    // Token doesn't exist or already used
    header('Location: ' . $login_url . '?erro=token_invalido');
  }
}
exit;
