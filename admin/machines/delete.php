<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') !== null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../config_local.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $result = callAPI('machines/delete.php', ['id' => filter_input(INPUT_POST, 'id')]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/machines/browse.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$machine = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') !== null) {
    $error = $result['message'] ?? 'Erro ao apagar máquina';
    $machine = callAPI('machines/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') === null) {
    $machine_id = filter_input(INPUT_POST, 'id');
    if ($machine_id) {
        $machine = callAPI('machines/read.php', ['id' => $machine_id]);
        if (!isset($machine['id'])) {
            $error = 'Máquina não encontrada';
        }
    }
}

if (!$machine) {
    header('Location: ' . ADMIN_BASE_PATH . '/machines/browse.php');
    exit;
}
?>

<h1 class="mt-4">Apagar Máquina</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Máquinas</a></li>
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
            <strong>Atenção!</strong> Esta ação não pode ser revertida. Tem a certeza que deseja apagar esta máquina?
        </div>
        
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">ID</th>
                <td><?= htmlspecialchars($machine['id']) ?></td>
            </tr>
            <tr>
                <th>Código da Máquina</th>
                <td><?= htmlspecialchars($machine['machine_code']) ?></td>
            </tr>
            <tr>
                <th>Nome do Local</th>
                <td><?= htmlspecialchars($machine['location_name']) ?></td>
            </tr>
            <tr>
                <th>Endereço API</th>
                <td><?= htmlspecialchars($machine['api_address']) ?></td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if ($machine['is_active']): ?>
                        <span class="badge bg-success">Ativa</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inativa</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Criada em</th>
                <td><?= date('d/m/Y H:i', strtotime($machine['created_at'])) ?></td>
            </tr>
        </table>
        
        <form method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= htmlspecialchars($machine['id']) ?>">
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
