<?php
require_once __DIR__ . '/../includes/header.php';

$result = callAPI('transactions/browse.php');
$transactions = $result['records'] ?? [];
$error = '';

if (isset($result['message']) && empty($transactions)) {
    $error = $result['message'];
}
?>

<h1 class="mt-4">Transações</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Transações</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-receipt me-1"></i>
        Lista de Transações
        <a href="add.php" class="btn btn-sm btn-success float-end">
            <i class="fas fa-plus"></i> Adicionar Transação
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($transactions)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Não existem transações registadas.
            </div>
        <?php else: ?>
        <table id="datatablesSimple">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilizador</th>
                    <th>Máquina</th>
                    <th>Bebida</th>
                    <th>Valor</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= h($transaction['id']) ?></td>
                        <td>ID: <?= h($transaction['user_id']) ?></td>
                        <td>ID: <?= h($transaction['machine_id']) ?: '-' ?></td>
                        <td><?= h($transaction['beverage_name']) ?: '-' ?></td>
                        <td>€<?= number_format($transaction['amount'] ?? 0, 2, ',', '.') ?></td>
                        <td>
                            <?php if (($transaction['type'] ?? '') === 'payment'): ?>
                                <span class="badge bg-success">Pagamento</span>
                            <?php elseif (($transaction['type'] ?? '') === 'refund'): ?>
                                <span class="badge bg-warning">Reembolso</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= h($transaction['type']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($transaction['status'] ?? '') === 'completed'): ?>
                                <span class="badge bg-success">Concluído</span>
                            <?php elseif (($transaction['status'] ?? '') === 'pending'): ?>
                                <span class="badge bg-warning">Pendente</span>
                            <?php elseif (($transaction['status'] ?? '') === 'failed'): ?>
                                <span class="badge bg-danger">Falhado</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= h($transaction['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= isset($transaction['created_at']) ? date('d/m/Y H:i', strtotime($transaction['created_at'])) : 'N/A' ?></td>
                        <td>
                            <form method="POST" action="read.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?= h($transaction['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            <form method="POST" action="edit.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?= h($transaction['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </form>
                            <form method="POST" action="delete.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?= h($transaction['id']) ?>">
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
