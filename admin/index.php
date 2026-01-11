<?php
require_once __DIR__ . '/includes/header.php';

$machines_result = callAPI('machines/browse.php');
$users_result = callAPI('users/browse.php');
$transactions_result = callAPI('transactions/browse.php');

$total_machines = count($machines_result['records'] ?? []);
$active_machines = count(array_filter($machines_result['records'] ?? [], fn($m) => !empty($m['is_active'])));
$total_users = count($users_result['records'] ?? []);
$total_transactions = count($transactions_result['records'] ?? []);

$today = date('Y-m-d');
$transactions_today = array_filter($transactions_result['records'] ?? [], function($t) use ($today) {
    return strpos($t['created_at'], $today) === 0;
});
$transactions_today_count = count($transactions_today);
$revenue_today = array_sum(array_column($transactions_today, 'amount'));

$recent_transactions = array_slice($transactions_result['records'] ?? [], 0, 10);
?>

<h1 class="mt-4">Dashboard</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Total de Máquinas</div>
                        <div class="fs-2"><?= $total_machines ?></div>
                    </div>
                    <i class="fas fa-coffee fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?= ADMIN_BASE_PATH ?>/machines/browse.php">Ver Detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Máquinas Ativas</div>
                        <div class="fs-2"><?= $active_machines ?></div>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?= ADMIN_BASE_PATH ?>/machines/browse.php">Ver Detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Total de Utilizadores</div>
                        <div class="fs-2"><?= $total_users ?></div>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?= ADMIN_BASE_PATH ?>/machines/browse.php">Ver Detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Transações Hoje</div>
                        <div class="fs-2"><?= $transactions_today_count ?></div>
                    </div>
                    <i class="fas fa-receipt fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="<?= ADMIN_BASE_PATH ?>/users/browse.php">Ver Detalhes</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-receipt me-1"></i>
                Receita Hoje: <strong>€<?= number_format($revenue_today, 2, ',', '.') ?></strong>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Últimas 10 Transações
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Utilizador</th>
                    <th>Máquina</th>
                    <th>Bebida</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_transactions as $t): ?>
                <tr>
                    <td><?= h($t['id']) ?></td>
                    <td><?= h($t['user_id']) ?></td>
                    <td><?= h($t['machine_id']) ?: '-' ?></td>
                    <td><?= h($t['beverage_name']) ?: '-' ?></td>
                    <td>€<?= number_format($t['amount'] ?? 0, 2, ',', '.') ?></td>
                    <td>
                        <?php
                        $badge_class = 'secondary';
                        if (($t['status'] ?? '') === 'completed') $badge_class = 'success';
                        if (($t['status'] ?? '') === 'failed') $badge_class = 'danger';
                        if (($t['status'] ?? '') === 'pending') $badge_class = 'warning';
                        ?>
                        <span class="badge bg-<?= $badge_class ?>"><?= h($t['status']) ?: 'N/A' ?></span>
                    </td>
                    <td><?= isset($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_transactions)): ?>
                <tr>
                    <td colspan="7" class="text-center">Sem transações</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
