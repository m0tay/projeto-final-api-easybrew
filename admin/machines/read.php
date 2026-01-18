<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$machine = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

<h1 class="mt-4">Detalhes da Máquina</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/machines/browse.php">Máquinas</a></li>
    <li class="breadcrumb-item active">Ver</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-coffee me-1"></i>
        Máquina #<?= h($machine['id']) ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">ID</th>
                <td><?= h($machine['id']) ?></td>
            </tr>
            <tr>
                <th>Código da Máquina</th>
                <td><?= h($machine['machine_code']) ?></td>
            </tr>
            <tr>
                <th>Nome do Local</th>
                <td><?= h($machine['location_name']) ?></td>
            </tr>
            <tr>
                <th>Endereço API</th>
                <td><?= h($machine['api_address']) ?></td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if (boolval($machine['is_active'])): ?>
                        <span class="badge bg-success">Ativa</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inativa</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Criada em</th>
                <td><?= isset($machine['created_at']) ? date('d/m/Y H:i', strtotime($machine['created_at'])) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Atualizada em</th>
                <td><?= isset($machine['updated_at']) ? date('d/m/Y H:i', strtotime($machine['updated_at'])) : 'N/A' ?></td>
            </tr>
        </table>
        
        <div class="mt-4">
            <form method="POST" action="edit.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= h($machine['id']) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </form>
            
            <form method="POST" action="delete.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= h($machine['id']) ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Apagar
                </button>
            </form>
            
            <a href="<?= ADMIN_BASE_PATH ?>/machines/browse.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
