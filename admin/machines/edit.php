<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../config_local.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $result = callAPI('machines/edit.php', [
        'id' => $_POST['id'],
        'machine_code' => $_POST['machine_code'],
        'location_name' => $_POST['location_name'],
        'api_address' => $_POST['api_address'],
        'is_active' => $_POST['is_active']
    ]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/machines/browse.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$machine = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $error = $result['message'] ?? 'Erro ao atualizar máquina';
    $machine = callAPI('machines/read.php', ['id' => $_POST['id']]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['submit'])) {
    $machine_id = $_POST['id'] ?? null;
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

<h1 class="mt-4">Editar Máquina</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Máquinas</a></li>
    <li class="breadcrumb-item active">Editar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-edit me-1"></i>
        Editar Máquina #<?= htmlspecialchars($machine['id']) ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($machine['id']) ?>">
            
            <div class="mb-3">
                <label for="machine_code" class="form-label">Código da Máquina *</label>
                <input type="text" class="form-control" id="machine_code" name="machine_code" 
                       value="<?= htmlspecialchars($machine['machine_code']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="location_name" class="form-label">Nome do Local *</label>
                <input type="text" class="form-control" id="location_name" name="location_name" 
                       value="<?= htmlspecialchars($machine['location_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="api_address" class="form-label">Endereço API *</label>
                <input type="text" class="form-control" id="api_address" name="api_address" 
                       value="<?= htmlspecialchars($machine['api_address']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="is_active" class="form-label">Estado *</label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="1" <?= $machine['is_active'] ? 'selected' : '' ?>>Ativa</option>
                    <option value="0" <?= !$machine['is_active'] ? 'selected' : '' ?>>Inativa</option>
                </select>
            </div>
            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
