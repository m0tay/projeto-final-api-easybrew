<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') !== null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../core.php';
    require_once __DIR__ . '/../includes/api_helper.php';
    
    $preparation = filter_input(INPUT_POST, 'preparation', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $is_active_input = filter_input(INPUT_POST, 'is_active');
    
    $data = [
        'id' => filter_input(INPUT_POST, 'id'),
        'name' => filter_input(INPUT_POST, 'name'),
        'type' => filter_input(INPUT_POST, 'type'),
        'size' => filter_input(INPUT_POST, 'size'),
        'preparation' => $preparation ? implode(',', $preparation) : null,
        'price' => filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        'description' => filter_input(INPUT_POST, 'description'),
        'is_active' => (int)$is_active_input
    ];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $upload_size = $_FILES['image']['size'];
        $upload_tmp_name = $_FILES['image']['tmp_name'];
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024;
        
        if (!in_array($upload_extension, $allowed_extensions)) {
            $error = 'Formato de imagem não permitido. Use JPG, PNG ou GIF.';
        } elseif ($upload_size > $max_size) {
            $error = 'Imagem muito grande. Máximo 2MB.';
        } else {
            $image_filename = 'beverage_' . $data['id'] . '_' . time() . '.' . $upload_extension;
            $image_path = BEVERAGE_PATH . $image_filename;
            
            create_dir(BEVERAGE_PATH);
            
            if (move_uploaded_file($upload_tmp_name, $image_path)) {
                $data['image'] = $image_filename;
                
                $old_beverage = callAPI('beverages/read.php', ['id' => $data['id']]);
                if (!empty($old_beverage['image']) && file_exists(BEVERAGE_PATH . $old_beverage['image'])) {
                    delete_file(BEVERAGE_PATH . $old_beverage['image']);
                }
            } else {
                $error = 'Erro ao fazer upload da imagem.';
            }
        }
    } else {
        $old_beverage = callAPI('beverages/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
        $data['image'] = $old_beverage['image'] ?? null;
    }
    
    if (!isset($error)) {
        $result = callAPI('beverages/edit.php', $data);
        
        if (isset($result['http_code']) && $result['http_code'] == 200) {
            header('Location: ' . ADMIN_BASE_PATH . '/beverages/browse.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

$error = '';
$beverage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') !== null) {
    $error = $result['message'] ?? 'Erro ao atualizar bebida';
    $beverage = callAPI('beverages/read.php', ['id' => filter_input(INPUT_POST, 'id')]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && filter_input(INPUT_POST, 'submit') === null) {
    $beverage_id = filter_input(INPUT_POST, 'id');
    if ($beverage_id) {
        $beverage = callAPI('beverages/read.php', ['id' => $beverage_id]);
        if (!isset($beverage['id'])) {
            $error = 'Bebida não encontrada';
        }
    }
}

if (!$beverage) {
    header('Location: ' . ADMIN_BASE_PATH . '/beverages/browse.php');
    exit;
}

$preparation_array = !empty($beverage['preparation']) ? explode(',', $beverage['preparation']) : [];
?>

<h1 class="mt-4">Editar Bebida</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/beverages/browse.php">Bebidas</a></li>
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
        <i class="fas fa-coffee me-1"></i>
        Editar Bebida #<?= h($beverage['id']) ?>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= h($beverage['id']) ?>">
            
            <div class="mb-4">
                <label class="form-label">Imagem</label>
                <div class="d-flex align-items-center">
                    <img src="<?= get_beverage_image_url($beverage['image'] ?? '') ?>" 
                         alt="Imagem" class="rounded me-3" 
                         width="80" height="80" id="image-preview" style="object-fit: cover;">
                    <div>
                        <input type="file" class="form-control" id="image" name="image" 
                               accept="image/jpeg,image/png,image/gif">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= h($beverage['name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Preço (€) *</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?= h($beverage['price']) ?>" 
                               step="0.01" min="0" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo</label>
                        <input type="text" class="form-control" id="type" name="type" 
                               value="<?= h($beverage['type'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="size" class="form-label">Tamanho</label>
                        <input type="text" class="form-control" id="size" name="size" 
                               value="<?= h($beverage['size'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Preparação</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="hot" id="prep_hot" 
                           <?= in_array('hot', $preparation_array) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="prep_hot">Quente</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="iced" id="prep_iced" 
                           <?= in_array('iced', $preparation_array) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="prep_iced">Gelado</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="warm" id="prep_warm" 
                           <?= in_array('warm', $preparation_array) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="prep_warm">Morno</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="cold" id="prep_cold" 
                           <?= in_array('cold', $preparation_array) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="prep_cold">Frio</label>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= h($beverage['description'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="is_active" class="form-label">Estado *</label>
                <select class="form-select" id="is_active" name="is_active" required>
                    <option value="1" <?= boolval($beverage['is_active']) ? 'selected' : '' ?>>Ativa</option>
                    <option value="0" <?= !boolval($beverage['is_active']) ? 'selected' : '' ?>>Inativa</option>
                </select>
            </div>
            
            <div class="mt-4">
                <button type="submit" name="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="<?= ADMIN_BASE_PATH ?>/beverages/browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
