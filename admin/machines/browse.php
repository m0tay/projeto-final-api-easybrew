<?php
require_once __DIR__ . '/../includes/header.php';

$result = callAPI('machines/browse.php');
$machines = $result['records'] ?? [];
?>

<h1 class="mt-4">Máquinas</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Máquinas</li>
</ol>

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
                    <td><?= htmlspecialchars($m['id']) ?></td>
                    <td><?= htmlspecialchars($m['machine_code']) ?></td>
                    <td><?= htmlspecialchars($m['location_name']) ?></td>
                    <td><?= htmlspecialchars($m['api_address']) ?></td>
                    <td>
                        <?php if (boolval($m['is_active'])): ?>
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
