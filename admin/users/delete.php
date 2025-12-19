<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['confirm'])) {
    $user_id = $_POST['id'] ?? null;
    if ($user_id) {
        $user = callAPI('users/read.php', ['id' => $user_id]);
        if (!isset($user['id'])) {
            $error = 'Utilizador não encontrado';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $result = callAPI('users/delete.php', ['id' => $_POST['id']]);
    
    if (isset($result['message']) && strpos($result['message'], 'sucesso') !== false) {
        header('Location: ' . $_ENV['URL_BASE'] . 'api/users/browse.php');
        exit;
    } else {
        $error = $result['message'] ?? 'Erro ao apagar utilizador';
        $user = callAPI('users/read.php', ['id' => $_POST['id']]);
    }
}

if (!$user) {
    header('Location: ' . $_ENV['URL_BASE'] . 'api/users/browse.php');
    exit;
}
?>

<h1 class="mt-4">Apagar Utilizador</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/admin/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Utilizadores</a></li>
    <li class="breadcrumb-item active">Apagar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-trash me-1"></i>
        Confirmar Eliminação
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Atenção!</strong> Esta ação não pode ser revertida. Tem a certeza que deseja apagar este utilizador?
        </div>
        
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">ID</th>
                <td><?= htmlspecialchars($user['id']) ?></td>
            </tr>
            <tr>
                <th>Nome</th>
                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($user['email']) ?></td>
            </tr>
            <tr>
                <th>Função</th>
                <td>
                    <?php if ($user['role'] === 'admin'): ?>
                        <span class="badge bg-danger">Admin</span>
                    <?php else: ?>
                        <span class="badge bg-primary">Cliente</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Saldo</th>
                <td>€<?= number_format($user['balance'], 2, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if ($user['is_active']): ?>
                        <span class="badge bg-success">Ativo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inativo</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Criado em</th>
                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
            </tr>
        </table>
        
        <form method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
            <button type="submit" name="confirm" class="btn btn-danger">
                <i class="fas fa-trash"></i> Confirmar Eliminação
            </button>
            <a href="browse.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
