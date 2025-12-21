<?php
require_once __DIR__ . '/../includes/header.php';

$transactions = callAPI('transactions/browse.php');
?>

<h1 class="mt-4">Transações</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Transações</li>
</ol>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-receipt me-1"></i>
        Lista de Transações
        <a href="add.php" class="btn btn-sm btn-success float-end">
            <i class="fas fa-plus"></i> Adicionar Transação
        </a>
    </div>
    <div class="card-body">
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
                <?php if (isset($transactions['records'])): ?>
                    <?php foreach ($transactions['records'] as $transaction): ?>
                        <tr>
                            <td><?= htmlspecialchars($transaction['id']) ?></td>
                            <td>ID: <?= htmlspecialchars($transaction['user_id']) ?></td>
                            <td>ID: <?= htmlspecialchars($transaction['machine_id']) ?></td>
                            <td><?= htmlspecialchars($transaction['beverage_name']) ?></td>
                            <td>€<?= number_format($transaction['amount'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($transaction['type'] === 'payment'): ?>
                                    <span class="badge bg-success">Pagamento</span>
                                <?php elseif ($transaction['type'] === 'refund'): ?>
                                    <span class="badge bg-warning">Reembolso</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($transaction['type']) ?></span>
                                <?php endif; ?>
                            </td>
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
                            <td><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="read.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                <form method="POST" action="edit.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </form>
                                <form method="POST" action="delete.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
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
