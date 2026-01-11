<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$beverage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beverage_id = filter_input(INPUT_POST, 'id');
    if ($beverage_id) {
        $beverage = callAPI('beverages/read.php', ['id' => $beverage_id]);
        if (!isset($beverage['id'])) {
            $error = 'Bebida não encontrada';
        }
    }
}

if (!$beverage) {
    header('Location: ' . ADMIN_BASE_PATH . '/beverages/browse.php');
    exit;
}

$preparation_array = !empty($beverage['preparation']) ? explode(',', $beverage['preparation']) : [];
?>

<h1 class="mt-4">Detalhes da Bebida</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/beverages/browse.php">Bebidas</a></li>
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
        Bebida #<?= h($beverage['id']) ?>
    </div>
    <div class="card-body">
        <div class="mb-3 text-center">
            <img src="<?= get_beverage_image_url($beverage['image'] ?? '') ?>" 
                 alt="Imagem" class="rounded" 
                 width="200" height="200" style="object-fit: cover;">
        </div>
        <table class="table table-bordered">
            <tr>
                <th style="width: 200px;">ID</th>
                <td><?= h($beverage['id']) ?></td>
            </tr>
            <tr>
                <th>Nome</th>
                <td><?= h($beverage['name']) ?></td>
            </tr>
            <tr>
                <th>Tipo</th>
                <td><?= h($beverage['type'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Tamanho</th>
                <td><?= h($beverage['size'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Preparação</th>
                <td>
                    <?php if (!empty($preparation_array)): ?>
                        <?php foreach ($preparation_array as $prep): ?>
                            <span class="badge bg-info me-1"><?= h(ucfirst($prep)) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Preço</th>
                <td>€<?= number_format($beverage['price'], 2, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Descrição</th>
                <td><?= h($beverage['description'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if (boolval($beverage['is_active'])): ?>
                        <span class="badge bg-success">Ativa</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inativa</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        
        <div class="mt-4">
            <form method="POST" action="edit.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </form>
            
            <form method="POST" action="delete.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Apagar
                </button>
            </form>
            
            <a href="<?= ADMIN_BASE_PATH ?>/beverages/browse.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
