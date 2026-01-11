<?php
require_once __DIR__ . '/../includes/header.php';

$result = callAPI('machines/browse.php');
$machines = $result['records'] ?? [];
$error = '';

if (isset($result['message']) && empty($machines)) {
    $error = $result['message'];
}
?>

<h1 class="mt-4">Máquinas</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Máquinas</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="mb-3">
    <a href="add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Adicionar Máquina
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Lista de Máquinas
    </div>
    <div class="card-body">
        <?php if (empty($machines)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Não existem máquinas registadas. Clique em "Adicionar Máquina" para criar a primeira.
            </div>
        <?php else: ?>
        <table id="datatablesSimple">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Local</th>
                    <th>Endereço API</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($machines as $m): ?>
                <tr>
                    <td><?= h($m['id']) ?></td>
                    <td><?= h($m['machine_code']) ?></td>
                    <td><?= h($m['location_name']) ?></td>
                    <td><?= h($m['api_address']) ?></td>
                    <td>
                        <?php if (!empty($m['is_active'])): ?>
                            <span class="badge bg-success">Ativa</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inativa</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" action="read.php" class="d-inline">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                        </form>
                        <form method="POST" action="edit.php" class="d-inline">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        </form>
                        <form method="POST" action="delete.php" class="d-inline">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Apagar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
