<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') !== null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $result = callAPI('transactions/delete.php', ['id' => filter_input(INPUT_POST, 'id')]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/transactions/browse.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$transaction = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') !== null) {
    $error = $result['message'] ?? 'Erro ao apagar transação';
    $transaction = callAPI('transactions/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'confirm') === null) {
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

<h1 class="mt-4">Apagar Transação</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Transações</a></li>
    <li class="breadcrumb-item active">Apagar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
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
            <strong>Atenção!</strong> Esta ação não pode ser revertida. Tem a certeza que deseja apagar esta transação?
        </div>
        
        <table class="table table-bordered">
            <tr>
                <th style="width: 250px;">ID</th>
                <td><?= htmlspecialchars($transaction['id']) ?></td>
            </tr>
            <tr>
                <th>ID do Utilizador</th>
                <td><?= htmlspecialchars($transaction['user_id']) ?></td>
            </tr>
            <tr>
                <th>ID da Máquina</th>
                <td><?= htmlspecialchars($transaction['machine_id']) ?></td>
            </tr>
            <tr>
                <th>Bebida</th>
                <td><?= htmlspecialchars($transaction['beverage_name']) ?> (<?= htmlspecialchars($transaction['preparation_chosen']) ?>)</td>
            </tr>
            <tr>
                <th>Valor</th>
                <td>€<?= number_format($transaction['amount'], 2, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Tipo</th>
                <td>
                    <?php if ($transaction['type'] === 'payment'): ?>
                        <span class="badge bg-success">Pagamento</span>
                    <?php elseif ($transaction['type'] === 'refund'): ?>
                        <span class="badge bg-warning">Reembolso</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($transaction['type']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    <?php if ($transaction['status'] === 'completed'): ?>
                        <span class="badge bg-success">Concluído</span>
                    <?php elseif ($transaction['status'] === 'pending'): ?>
                        <span class="badge bg-warning">Pendente</span>
                    <?php elseif ($transaction['status'] === 'failed'): ?>
                        <span class="badge bg-danger">Falhado</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($transaction['status']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Criada em</th>
                <td><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
            </tr>
        </table>
        
        <form method="POST" class="mt-4">
            <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
            <button type="submit" name="confirm" class="btn btn-danger">
                <i class="fas fa-trash"></i> Confirmar Eliminação
            </button>
            <a href="browse.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
