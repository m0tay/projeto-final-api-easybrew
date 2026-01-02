<?php
require_once __DIR__ . '/../includes/header.php';

$beverages = callAPI('beverages/browse.php');
?>

<h1 class="mt-4">Bebidas</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Bebidas</li>
</ol>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-coffee me-1"></i>
        Lista de Bebidas
        <a href="add.php" class="btn btn-sm btn-success float-end">
            <i class="fas fa-plus"></i> Adicionar Bebida
        </a>
    </div>
    <div class="card-body">
        <table id="datatablesSimple">
            <thead>
                <tr>
                    <th>Imagem</th>
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
                <?php if (isset($beverages['records'])): ?>
                    <?php foreach ($beverages['records'] as $beverage): ?>
                        <tr>
                            <td>
                                <img src="<?= get_beverage_image_url($beverage['image'] ?? '') ?>" 
                                     alt="<?= htmlspecialchars($beverage['name']) ?>" 
                                     width="60" height="60" style="object-fit: cover;">
                            </td>
                            <td><?= htmlspecialchars(substr($beverage['id'], 0, 8)) ?>...</td>
                            <td><?= htmlspecialchars($beverage['name']) ?></td>
                            <td><?= htmlspecialchars($beverage['type'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($beverage['size'] ?? '-') ?></td>
                            <td>€<?= number_format($beverage['price'], 2, ',', '.') ?></td>
                            <td>
                                <?php if (boolval($beverage['is_active'])): ?>
                                    <span class="badge bg-success">Ativa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="read.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($beverage['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                <form method="POST" action="edit.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($beverage['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </form>
                                <form method="POST" action="delete.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($beverage['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Apagar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
