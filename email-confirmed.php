<?php
require_once 'config.php';

$success = '';
$error = '';

if (filter_input(INPUT_GET, 'sucesso') !== null) {
  $sucesso = filter_input(INPUT_GET, 'sucesso', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  if ($sucesso === 'email_confirmado') {
    $success = 'Email confirmado com sucesso! Já podes fazer login.';
  }
}

if (filter_input(INPUT_GET, 'erro') !== null) {
  $erro = filter_input(INPUT_GET, 'erro', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  if ($erro === 'token_invalido') {
    $error = 'Token de confirmação inválido. Por favor, verifica o link recebido no email.';
  } else if ($erro === 'token_expirado') {
    $error = 'Token de confirmação expirado. Por favor, regista-te novamente para receber um novo email de confirmação.';
  } else if ($erro === 'erro_confirmacao') {
    $error = 'Erro ao confirmar o email. Por favor, tenta novamente mais tarde.';
  }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmação de Email - EasyBrew</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .confirmation-card {
      max-width: 500px;
      width: 100%;
    }

    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .card-header {
      background-color: #6f42c1;
      color: white;
      border-radius: 15px 15px 0 0 !important;
      padding: 2rem;
      text-align: center;
    }

    .card-header i {
      font-size: 4rem;
      margin-bottom: 1rem;
    }

    .card-body {
      padding: 2rem;
    }

    .btn-primary {
      background-color: #6f42c1;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background-color: #5a32a3;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(111, 66, 193, 0.3);
    }

    .alert {
      border-radius: 10px;
      border: none;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
    }

    .footer-text {
      text-align: center;
      color: white;
      margin-top: 2rem;
      font-size: 0.9rem;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="confirmation-card mx-auto">
      <div class="card">
        <div class="card-header">
          <?php if ($success): ?>
            <i class="fas fa-check-circle"></i>
            <h3 class="mb-0">Email Confirmado!</h3>
          <?php else: ?>
            <i class="fas fa-exclamation-circle"></i>
            <h3 class="mb-0">Ops!</h3>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <?php if ($success): ?>
            <div class="alert alert-success mb-4">
              <i class="fas fa-check-circle me-2"></i>
              <?= h($success) ?>
            </div>
            <p class="text-center mb-4">
              A tua conta EasyBrew está agora ativa. Podes fazer login e começar a usar a aplicação.
            </p>
          <?php elseif ($error): ?>
            <div class="alert alert-danger mb-4">
              <i class="fas fa-exclamation-circle me-2"></i>
              <?= h($error) ?>
            </div>
            <p class="text-center mb-4">
              Se precisares de ajuda, contacta o suporte técnico.
            </p>
            <div class="d-grid gap-2">
              <a href="<?= WEB_SERVER . WEB_ROOT ?>admin/login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Ir para Login
              </a>
            </div>
          <?php else: ?>
            <div class="alert alert-danger mb-4">
              <i class="fas fa-exclamation-circle me-2"></i>
              Link inválido ou parâmetros em falta.
            </div>
            <div class="d-grid">
              <a href="<?= WEB_SERVER . WEB_ROOT ?>admin/login.php" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Voltar ao Início
              </a>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer text-center py-3 bg-light" style="border-radius: 0 0 15px 15px;">
          <small class="text-muted">© <?= ANO_LETIVO ?> EasyBrew - Universidade de Aveiro</small>
        </div>
      </div>
      <div class="footer-text">
        <p><i class="fas fa-coffee me-2"></i>EasyBrew - O teu café, à tua maneira</p>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
