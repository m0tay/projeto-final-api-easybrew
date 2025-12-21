<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $result = callAPI('machines/add.php', [
        'machine_code' => $_POST['machine_code'],
        'location_name' => $_POST['location_name'],
        'api_address' => $_POST['api_address']
    ]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        $success = true;
        header('Location: ' . ADMIN_BASE_PATH . '/machines/browse.php');
        exit;
    } else {
        $error = $result['message'] ?? 'Erro ao adicionar máquina';
    }
}
?>

<h1 class="mt-4">Adicionar Máquina</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Máquinas</a></li>
    <li class="breadcrumb-item active">Adicionar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-plus me-1"></i>
        Nova Máquina
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="machine_code" class="form-label">Código da Máquina *</label>
                <input type="text" class="form-control" id="machine_code" name="machine_code" required>
            </div>
            <div class="mb-3">
                <label for="location_name" class="form-label">Nome do Local *</label>
                <input type="text" class="form-control" id="location_name" name="location_name" required>
            </div>
            <div class="mb-3">
                <label for="api_address" class="form-label">Endereço API *</label>
                <input type="text" class="form-control" id="api_address" name="api_address" 
                       placeholder="http://exemplo.com/api/" required>
                <div class="form-text">URL completo do endereço API da máquina</div>
            </div>
            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Adicionar
                </button>
                <a href="browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
