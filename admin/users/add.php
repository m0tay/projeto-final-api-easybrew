<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../core.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $result = callAPI('users/add.php', [
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'password' => filter_input(INPUT_POST, 'password')
    ]);
    
    if (isset($result['http_code']) && ($result['http_code'] == 200 || $result['http_code'] == 201)) {
        $user_id = $result['id'] ?? null;
        
        if ($user_id && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $upload_size = $_FILES['avatar']['size'];
            $upload_tmp_name = $_FILES['avatar']['tmp_name'];
            
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            $max_size = 2 * 1024 * 1024;
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $upload_tmp_name);
            finfo_close($finfo);
            
            $allowed_mimes = ['image/jpeg', 'image/png'];
            
            if (!in_array($upload_extension, $allowed_extensions)) {
                $error = 'Formato de imagem não permitido. Use JPG ou PNG.';
            } elseif ($upload_size > $max_size) {
                $error = 'Imagem muito grande. Máximo 2MB.';
            } elseif (!in_array($mime, $allowed_mimes)) {
                $error = 'Tipo de arquivo inválido.';
            } else {
                $avatar_filename = $user_id . '-img.' . $upload_extension;
                $avatar_path = AVATAR_PATH . $avatar_filename;
                
                create_dir(AVATAR_PATH);
                
                $source_image = match($upload_extension) {
                    'jpg', 'jpeg' => @imagecreatefromjpeg($upload_tmp_name),
                    'png' => @imagecreatefrompng($upload_tmp_name),
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
                        $user = callAPI('users/read.php', ['id' => $user_id]);
                        callAPI('users/edit.php', [
                            'id' => $user_id,
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                            'email' => $user['email'],
                            'role' => $user['role'],
                            'balance' => $user['balance'],
                            'is_active' => $user['is_active'],
                            'avatar' => $avatar_filename
                        ]);
                    }
                }
            }
        }
        
        if (!isset($error)) {
            header('Location: ' . ADMIN_BASE_PATH . '/users/browse.php');
            exit;
        }
    } else {
        $error = $result['message'] ?? 'Erro ao criar utilizador';
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = $error ?? '';
?>
?>

<h1 class="mt-4">Adicionar Utilizador</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/users/browse.php">Utilizadores</a></li>
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
        <i class="fas fa-user-plus me-1"></i>
        Novo Utilizador
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar</label>
                <input type="file" class="form-control" id="avatar" name="avatar" 
                       accept="image/jpeg,image/png">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="utilizador@exemplo.com" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password" class="form-control" id="password" name="password" 
                       minlength="6" required>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Utilizador
                </button>
                <a href="<?= ADMIN_BASE_PATH ?>/users/browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
