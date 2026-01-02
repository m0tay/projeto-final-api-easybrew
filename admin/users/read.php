<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_input(INPUT_POST, 'id');
    if ($user_id) {
        $user = callAPI('users/read.php', ['id' => $user_id]);
        if (!isset($user['id'])) {
            $error = 'Utilizador não encontrado';
        }
    }
}

if (!$user) {
    header('Location: ' . ADMIN_BASE_PATH . '/users/browse.php');
    exit;
}
?>

<h1 class="mt-4">Detalhes do Utilizador</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Utilizadores</a></li>
    <li class="breadcrumb-item active">Ver</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-user me-1"></i>
        Utilizador #<?= htmlspecialchars($user['id']) ?? "N/A" ?>
    </div>
    <div class="card-body">
        <div class="mb-3 text-center">
            <img src="<?= get_avatar_url($user['avatar'] ?? '') ?>" 
                 alt="Avatar" class="rounded-circle" 
                 width="120" height="120">
        </div>
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">ID</th>
                <td><?= htmlspecialchars($user['id']) ?? "N/A" ?></td>
            </tr>
            <tr>
                <th>Primeiro Nome</th>
                <td><?= htmlspecialchars($user['first_name']) ?? "N/A" ?></td>
            </tr>
            <tr>
                <th>Último Nome</th>
                <td><?= htmlspecialchars($user['last_name']) ?? "N/A" ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?= htmlspecialchars($user['email']) ?? "N/A" ?></td>
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
                <td>€<?= number_format($user['balance'], 2, ',', '.') ?? "N/A" ?></td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if (boolval($user['is_active'])): ?>
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
            <tr>
                <th>Atualizado em</th>
                <td><?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?? "N/A" ?></td>
            </tr>
        </table>
        
        <div class="mt-4">
            <form method="POST" action="edit.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?? "N/A" ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </form>
            
            <form method="POST" action="delete.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?? "N/A" ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Apagar
                </button>
            </form>
            
            <a href="browse.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
