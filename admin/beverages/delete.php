<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') !== null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $result = callAPI('beverages/delete.php', ['id' => filter_input(INPUT_POST, 'id')]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/beverages/browse.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$beverage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') !== null) {
    $error = $result['message'] ?? 'Erro ao apagar bebida';
    $beverage = callAPI('beverages/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') === null) {
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

<h1 class="mt-4">Apagar Bebida</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/beverages/browse.php">Bebidas</a></li>
    <li class="breadcrumb-item active">Apagar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($error) ?>
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
            <strong>Atenção!</strong> Esta ação não pode ser revertida. Tem a certeza que deseja apagar esta bebida?
        </div>
        
        <div class="mb-3 text-center">
            <img src="<?= get_beverage_image_url($beverage['image'] ?? '') ?>" 
                 alt="Imagem" class="rounded" 
                 width="150" height="150" style="object-fit: cover;">
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
        
        <form method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
            <button type="submit" name="confirm" class="btn btn-danger">
                <i class="fas fa-trash"></i> Confirmar Eliminação
            </button>
            <a href="<?= ADMIN_BASE_PATH ?>/beverages/browse.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
