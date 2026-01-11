<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = callAPI('users/add.php', [
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'password' => filter_input(INPUT_POST, 'password')
    ]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/users/browse.php');
        exit;
    } else {
        $error = $result['message'] ?? 'Erro ao criar utilizador';
    }
}
?>

<h1 class="mt-4">Adicionar Utilizador</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/users/browse.php">Utilizadores</a></li>
    <li class="breadcrumb-item active">Adicionar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-user-plus me-1"></i>
        Novo Utilizador
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            O nome será gerado automaticamente a partir do email. Por exemplo, <strong>joao.silva@email.com</strong> gerará o nome <strong>Joao Silva</strong>.
        </div>
        
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="utilizador@exemplo.com" required>
                <div class="form-text">O utilizador usará este email para fazer login.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password" class="form-control" id="password" name="password" 
                       minlength="6" required>
                <div class="form-text">Mínimo 6 caracteres.</div>
            </div>
            
            <div class="alert alert-warning">
                <strong>Nota:</strong> O utilizador será criado com:
                <ul class="mb-0 mt-2">
                    <li>Função: <strong>Cliente</strong></li>
                    <li>Saldo: <strong>€0,00</strong></li>
                    <li>Estado: <strong>Ativo</strong></li>
                </ul>
                <div class="mt-2">Use a opção "Editar" para modificar estes valores após criação.</div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Utilizador
                </button>
                <a href="<?= ADMIN_BASE_PATH ?>/users/browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
