<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') !== null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $result = callAPI('transactions/edit.php', [
        'id' => filter_input(INPUT_POST, 'id'),
        'type' => filter_input(INPUT_POST, 'type'),
        'status' => filter_input(INPUT_POST, 'status')
    ]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/transactions/browse.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$transaction = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') !== null) {
    $error = $result['message'] ?? 'Erro ao atualizar transação';
    $transaction = callAPI('transactions/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') === null) {
    $transaction_id = filter_input(INPUT_POST, 'id');
    if ($transaction_id) {
        $transaction = callAPI('transactions/read.php', ['id' => $transaction_id]);
        if (!isset($transaction['id'])) {
            $error = 'Transação não encontrada';
        }
    }
}

if (!$transaction) {
    header('Location: ' . ADMIN_BASE_PATH . '/transactions/browse.php');
    exit;
}
?>

<h1 class="mt-4">Editar Transação</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Transações</a></li>
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
        Editar Transação #<?= htmlspecialchars($transaction['id']) ?>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Nota:</strong> Apenas o tipo e o estado podem ser editados. Os outros campos são apenas leitura.
        </div>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Utilizador</label>
                        <input type="text" class="form-control" value="ID: <?= htmlspecialchars($transaction['user_id']) ?>" disabled>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Máquina</label>
                        <input type="text" class="form-control" value="ID: <?= htmlspecialchars($transaction['machine_id']) ?>" disabled>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nome da Bebida</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($transaction['beverage_name']) ?>" disabled>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Preparação</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($transaction['preparation_chosen']) ?>" disabled>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Valor</label>
                        <input type="text" class="form-control" value="€<?= number_format($transaction['amount'], 2, ',', '.') ?>" disabled>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            <h5 class="mb-3">Campos Editáveis</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="payment" <?= $transaction['type'] === 'payment' ? 'selected' : '' ?>>Pagamento</option>
                            <option value="refund" <?= $transaction['type'] === 'refund' ? 'selected' : '' ?>>Reembolso</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="completed" <?= $transaction['status'] === 'completed' ? 'selected' : '' ?>>Concluído</option>
                            <option value="pending" <?= $transaction['status'] === 'pending' ? 'selected' : '' ?>>Pendente</option>
                            <option value="failed" <?= $transaction['status'] === 'failed' ? 'selected' : '' ?>>Falhado</option>
                        </select>
                    </div>
                </div>
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
