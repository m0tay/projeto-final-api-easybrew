<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';
$users = [];
$machines = [];
$beverages = [];

$usersData = callAPI('users/browse.php');
if (isset($usersData['records'])) {
    $users = $usersData['records'];
}

$machinesData = callAPI('machines/browse.php');
if (isset($machinesData['records'])) {
    $machines = $machinesData['records'];
}

$beveragesData = callAPI('beverages/browse.php');
if (isset($beveragesData['records'])) {
    $beverages = $beveragesData['records'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = callAPI('transactions/add.php', [
        'user_id' => filter_input(INPUT_POST, 'user_id'),
        'machine_id' => filter_input(INPUT_POST, 'machine_id'),
        'beverage_id' => filter_input(INPUT_POST, 'beverage_id'),
        'beverage_name' => filter_input(INPUT_POST, 'beverage_name'),
        'preparation_chosen' => filter_input(INPUT_POST, 'preparation_chosen'),
        'amount' => filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'type' => filter_input(INPUT_POST, 'type'),
        'status' => filter_input(INPUT_POST, 'status')
    ]);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        header('Location: ' . ADMIN_BASE_PATH . '/transactions/browse.php');
        exit;
    } else {
        $error = $result['message'] ?? 'Erro ao criar transação';
    }
}
?>

<h1 class="mt-4">Adicionar Transação</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/transactions/browse.php">Transações</a></li>
    <li class="breadcrumb-item active">Adicionar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-plus me-1"></i>
        Nova Transação
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Utilizador *</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Selecione um utilizador...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= h($user['id']) ?>">
                                    #<?= h($user['id']) ?> - <?= h($user['first_name'] . ' ' . $user['last_name']) ?> (<?= h($user['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="machine_id" class="form-label">Máquina *</label>
                        <select class="form-select" id="machine_id" name="machine_id" required>
                            <option value="">Selecione uma máquina...</option>
                            <?php foreach ($machines as $machine): ?>
                                <option value="<?= h($machine['id']) ?>">
                                    #<?= h($machine['id']) ?> - <?= h($machine['machine_code']) ?> (<?= h($machine['location_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="beverage_id" class="form-label">Bebida *</label>
                <select class="form-select" id="beverage_id" name="beverage_id" required>
                    <option value="">Selecione uma bebida...</option>
                    <?php foreach ($beverages as $beverage): ?>
                        <option value="<?= h($beverage['id']) ?>" data-name="<?= h($beverage['name']) ?>" data-price="<?= h($beverage['price']) ?>">
                            #<?= h($beverage['id']) ?> - <?= h($beverage['name']) ?> (€<?= number_format($beverage['price'], 2, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="beverage_name" class="form-label">Nome da Bebida *</label>
                <input type="text" class="form-control" id="beverage_name" name="beverage_name" 
                       placeholder="Ex: Café Expresso" required readonly>
            </div>
            
            <div class="mb-3">
                <label for="preparation_chosen" class="form-label">Preparação Escolhida *</label>
                <input type="text" class="form-control" id="preparation_chosen" name="preparation_chosen" 
                       placeholder="Ex: Normal, Com Açúcar, etc." required>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Valor (€) *</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Selecione...</option>
                            <option value="payment">Pagamento</option>
                            <option value="refund">Reembolso</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Estado *</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Selecione...</option>
                            <option value="completed">Concluído</option>
                            <option value="pending">Pendente</option>
                            <option value="failed">Falhado</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Transação
                </button>
                <a href="<?= ADMIN_BASE_PATH ?>/transactions/browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('beverage_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const beverageName = selected.getAttribute('data-name');
    const beveragePrice = selected.getAttribute('data-price');
    
    if (beverageName) {
        document.getElementById('beverage_name').value = beverageName;
    }
    if (beveragePrice) {
        document.getElementById('amount').value = beveragePrice;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
