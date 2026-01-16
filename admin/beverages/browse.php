<?php
require_once __DIR__ . '/../includes/header.php';

$result = callAPI('beverages/browse.php');
$beverages = $result['records'] ?? [];
$error = '';

if (isset($result['message']) && empty($beverages)) {
    $error = $result['message'];
}
?>

<h1 class="mt-4">Bebidas</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Bebidas</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-coffee me-1"></i>
        Lista de Bebidas
        <a href="add.php" class="btn btn-sm btn-success float-end">
            <i class="fas fa-plus"></i> Adicionar Bebida
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($beverages)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Não existem bebidas registadas. Clique em "Adicionar Bebida" para criar a primeira.
            </div>
        <?php else: ?>
        <table id="datatablesSimple">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Tamanho</th>
                    <th>Preço</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($beverages as $beverage): ?>
                    <tr>
                        <td><?= h(substr($beverage['id'] ?? '', 0, 8)) ?>...</td>
                        <td><?= h($beverage['name']) ?></td>
                        <td><?= h($beverage['type']) ?: '-' ?></td>
                        <td><?= h($beverage['size']) ?: '-' ?></td>
                        <td>€<?= number_format($beverage['price'] ?? 0, 2, ',', '.') ?></td>
                        <td>
                            <?php if (!empty($beverage['is_active'])): ?>
                                <span class="badge bg-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="read.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            <form method="POST" action="edit.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </form>
                            <form method="POST" action="delete.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
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
