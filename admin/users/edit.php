<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['submit'])) {
    $user_id = $_POST['id'] ?? null;
    if ($user_id) {
        $user = callAPI('users/read.php', ['id' => $user_id]);
        if (!isset($user['id'])) {
            $error = 'Utilizador não encontrado';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $data = [
        'id' => $_POST['id'],
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'role' => $_POST['role'],
        'balance' => $_POST['balance'],
        'is_active' => $_POST['is_active']
    ];
    
    if (!empty($_POST['password'])) {
        $data['password'] = $_POST['password'];
    }
    
    $result = callAPI('users/edit.php', $data);
    
    if (isset($result['message']) && strpos($result['message'], 'sucesso') !== false) {
        header('Location: ' . $_ENV['URL_BASE'] . 'api/users/browse.php');
        exit;
    } else {
        $error = $result['message'] ?? 'Erro ao atualizar utilizador';
        $user = callAPI('users/read.php', ['id' => $_POST['id']]);
    }
}

if (!$user) {
    header('Location: ' . $_ENV['URL_BASE'] . 'api/users/browse.php');
    exit;
}
?>

<h1 class="mt-4">Editar Utilizador</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/admin/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Utilizadores</a></li>
    <li class="breadcrumb-item active">Editar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-user-edit me-1"></i>
        Editar Utilizador #<?= htmlspecialchars($user['id']) ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Primeiro Nome *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Último Nome *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       minlength="6" placeholder="Deixe vazio para manter a atual">
                <div class="form-text">Preencha apenas se pretende alterar a password.</div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="role" class="form-label">Função *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Cliente</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="balance" class="form-label">Saldo (€) *</label>
                        <input type="number" class="form-control" id="balance" name="balance" 
                               value="<?= htmlspecialchars($user['balance']) ?>" 
                               step="0.01" min="0" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Estado *</label>
                        <select class="form-select" id="is_active" name="is_active" required>
                            <option value="1" <?= $user['is_active'] ? 'selected' : '' ?>>Ativo</option>
                            <option value="0" <?= !$user['is_active'] ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
