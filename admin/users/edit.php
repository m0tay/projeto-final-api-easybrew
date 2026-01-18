<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') !== null) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../core.php';
    session_start();
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $data = [
        'id' => filter_input(INPUT_POST, 'id'),
        'first_name' => filter_input(INPUT_POST, 'first_name'),
        'last_name' => filter_input(INPUT_POST, 'last_name'),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'role' => filter_input(INPUT_POST, 'role'),
        'balance' => filter_input(INPUT_POST, 'balance', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'is_active' => filter_input(INPUT_POST, 'is_active')
    ];
    
    $password = filter_input(INPUT_POST, 'password');
    if (!empty($password)) {
        $data['password'] = $password;
    }
    
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $upload_size = $_FILES['avatar']['size'];
        $upload_tmp_name = $_FILES['avatar']['tmp_name'];
        
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $max_size = 2 * 1024 * 1024; // 2mb only bae
        
        if (!in_array($upload_extension, $allowed_extensions)) {
            $error = 'Formato de imagem não permitido. Use JPG ou PNG.';
        } elseif ($upload_size > $max_size) {
            $error = 'Imagem muito grande. Máximo 2MB.';
        } else {
            $avatar_filename = $data['id'] . '-img.' . $upload_extension;
            $avatar_path = AVATAR_PATH . $avatar_filename;
            
            create_dir(AVATAR_PATH);
            
            $source_image = match($upload_extension) {
                'jpg', 'jpeg' => imagecreatefromjpeg($upload_tmp_name),
                'png' => imagecreatefrompng($upload_tmp_name),
            };
            
            if ($source_image) {
                $original_width = imagesx($source_image);
                $original_height = imagesy($source_image);
                
                $new_image = imagecreatetruecolor(256, 256);
                
                if ($upload_extension === 'png') {
                    imagealphablending($new_image, false);
                    imagesavealpha($new_image, true);
                    $transparent = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
                    imagefill($new_image, 0, 0, $transparent);
                }
                
                imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, 256, 256, $original_width, $original_height);
                
                $save_success = match($upload_extension) {
                    'jpg', 'jpeg' => imagejpeg($new_image, $avatar_path, 85),
                    'png' => imagepng($new_image, $avatar_path, 6),
                };
                
                imagedestroy($source_image);
                imagedestroy($new_image);
                
                if ($save_success) {
                    $data['avatar'] = $avatar_filename;
                    
                    $old_user = callAPI('users/read.php', ['id' => $data['id']]);
                    if (!empty($old_user['avatar']) && $old_user['avatar'] !== AVATAR_DEFAULT && file_exists(AVATAR_PATH . $old_user['avatar'])) {
                        delete_file(AVATAR_PATH . $old_user['avatar']);
                    }
                } else {
                    $error = 'Erro ao processar imagem.';
                }
            } else {
                $error = 'Erro ao ler imagem.';
            }
        }
    }
    
    if (!isset($error)) {
        $result = callAPI('users/edit.php', $data);
        
        if (isset($result['http_code']) && $result['http_code'] == 200) {
            header('Location: ' . ADMIN_BASE_PATH . '/users/browse.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') !== null) {
    $error = $result['message'] ?? 'Erro ao atualizar utilizador';
    $user = callAPI('users/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') === null) {
    $user_id = filter_input(INPUT_POST, 'id');
    if ($user_id) {
        $user = callAPI('users/read.php', ['id' => $user_id]);
        if (!isset($user['id'])) {
            $error = 'Utilizador não encontrado';
        }
    }
}

if (!$user) {
    header('Location: ' . ADMIN_BASE_PATH . '/users/browse.php');
    exit;
}
?>

<h1 class="mt-4">Editar Utilizador</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/users/browse.php">Utilizadores</a></li>
    <li class="breadcrumb-item active">Editar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-user-edit me-1"></i>
        Editar Utilizador #<?= h($user['id']) ?>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= h($user['id']) ?>">
            
            <div class="mb-4">
                <label class="form-label">Avatar</label>
                <div class="d-flex align-items-center">
                    <img src="<?= get_avatar_url($user['avatar'] ?? '') ?>" 
                         alt="Avatar" class="rounded-circle me-3" 
                         width="80" height="80" id="avatar-preview">
                    <div>
                        <input type="file" class="form-control" id="avatar" name="avatar" 
                               accept="image/jpeg,image/png">
                        <div class="form-text">JPG ou PNG. Máximo 2MB.</div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Primeiro Nome *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?= h($user['first_name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Último Nome *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?= h($user['last_name']) ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= h($user['email']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       minlength="6" placeholder="Deixe vazio para manter a atual">
                <div class="form-text">Preencha apenas se pretende alterar a password.</div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="role" class="form-label">Função *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="costumer" <?= $user['role'] === 'costumer' ? 'selected' : '' ?>>Cliente</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="balance" class="form-label">Saldo (€) *</label>
                        <input type="number" class="form-control" id="balance" name="balance" 
                               value="<?= h($user['balance']) ?>" 
                               step="0.01" min="0" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Estado *</label>
                        <select class="form-select" id="is_active" name="is_active" required>
                            <option value="1" <?= boolval($user['is_active']) ? 'selected' : '' ?>>Ativo</option>
                            <option value="0" <?= !boolval($user['is_active']) ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="<?= ADMIN_BASE_PATH ?>/users/browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
