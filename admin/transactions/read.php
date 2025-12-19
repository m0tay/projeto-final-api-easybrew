<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$transaction = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['id'] ?? null;
    if ($transaction_id) {
        $transaction = callAPI('transactions/read.php', ['id' => $transaction_id]);
        if (!isset($transaction['id'])) {
            $error = 'Transação não encontrada';
        }
    }
}

if (!$transaction) {
    header('Location: ' . $_ENV['URL_BASE'] . 'api/transactions/browse.php');
    exit;
}
?>

<h1 class="mt-4">Detalhes da Transação</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/admin/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Transações</a></li>
    <li class="breadcrumb-item active">Ver</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-receipt me-1"></i>
        Transação #<?= htmlspecialchars($transaction['id']) ?>
    </div>
    <div class="card-body">
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
                <th>ID da Bebida</th>
                <td><?= htmlspecialchars($transaction['beverage_id']) ?></td>
            </tr>
            <tr>
                <th>Nome da Bebida</th>
                <td><?= htmlspecialchars($transaction['beverage_name']) ?></td>
            </tr>
            <tr>
                <th>Preparação Escolhida</th>
                <td><?= htmlspecialchars($transaction['preparation_chosen']) ?></td>
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
        
        <div class="mt-4">
            <form method="POST" action="edit.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </form>
            
            <form method="POST" action="delete.php" style="display: inline;">
                <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Apagar
                </button>
            </form>
            
            <a href="browse.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
