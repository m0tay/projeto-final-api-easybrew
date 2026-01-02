<?php
require_once __DIR__ . '/../includes/header.php';

$users = callAPI('users/browse.php');
?>

<h1 class="mt-4">Utilizadores</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Utilizadores</li>
</ol>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-users me-1"></i>
        Lista de Utilizadores
        <a href="add.php" class="btn btn-sm btn-success float-end">
            <i class="fas fa-plus"></i> Adicionar Utilizador
        </a>
    </div>
    <div class="card-body">
        <table id="datatablesSimple">
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($users['records'])): ?>
                    <?php foreach ($users['records'] as $user): ?>
                        <tr>
                            <td>
                                <img src="<?= get_avatar_url($user['avatar'] ?? '') ?>" 
                                     alt="Avatar" class="rounded-circle" 
                                     width="40" height="40">
                            </td>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Cliente</span>
                                <?php endif; ?>
                            </td>
                            <td>€<?= number_format($user['balance'], 2, ',', '.') ?></td>
                            <td>
                                <?php if (boolval($user['is_active'])): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="read.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-info" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                <form method="POST" action="edit.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </form>
                                <form method="POST" action="delete.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
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
