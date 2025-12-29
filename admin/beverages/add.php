<?php
require_once __DIR__ . '/../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'type' => $_POST['type'] ?? null,
        'size' => $_POST['size'] ?? null,
        'preparation' => isset($_POST['preparation']) ? implode(',', $_POST['preparation']) : null,
        'price' => $_POST['price'],
        'description' => $_POST['description'] ?? null
    ];

    $result = callAPI('beverages/add.php', $data);
    
    if (isset($result['http_code']) && $result['http_code'] == 200) {
        $beverage_id = $result['id'];
        
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
                $image_filename = 'beverage_' . $beverage_id . '_' . time() . '.' . $upload_extension;
                $image_path = BEVERAGE_PATH . $image_filename;
                
                create_dir(BEVERAGE_PATH);
                
                if (move_uploaded_file($upload_tmp_name, $image_path)) {
                    $update_result = callAPI('beverages/edit.php', [
                        'id' => $beverage_id,
                        'name' => $data['name'],
                        'type' => $data['type'],
                        'size' => $data['size'],
                        'preparation' => $data['preparation'],
                        'price' => $data['price'],
                        'description' => $data['description'],
                        'image' => $image_filename,
                        'is_active' => 1
                    ]);
                    
                    if (isset($update_result['http_code']) && $update_result['http_code'] == 200) {
                        header('Location: ' . ADMIN_BASE_PATH . '/beverages/browse.php');
                        exit;
                    } else {
                        $error = 'Bebida criada mas erro ao salvar imagem: ' . ($update_result['message'] ?? 'Erro desconhecido');
                    }
                } else {
                    $error = 'Erro ao fazer upload da imagem.';
                }
            }
        } else {
            header('Location: ' . ADMIN_BASE_PATH . '/beverages/browse.php');
            exit;
        }
    } else {
        $error = $result['message'] ?? 'Erro ao criar bebida';
    }
}
?>

<h1 class="mt-4">Adicionar Bebida</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="<?= ADMIN_BASE_PATH ?>/index.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="browse.php">Bebidas</a></li>
    <li class="breadcrumb-item active">Adicionar</li>
</ol>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-coffee me-1"></i>
        Nova Bebida
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Ex: Café Expresso" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="price" class="form-label">Preço (€) *</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipo</label>
                        <input type="text" class="form-control" id="type" name="type" 
                               placeholder="Ex: Café, Chá, Chocolate">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="size" class="form-label">Tamanho</label>
                        <input type="text" class="form-control" id="size" name="size" 
                               placeholder="Ex: Pequeno, Médio, Grande">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Preparação</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="hot" id="prep_hot">
                    <label class="form-check-label" for="prep_hot">Quente</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="iced" id="prep_iced">
                    <label class="form-check-label" for="prep_iced">Gelado</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="warm" id="prep_warm">
                    <label class="form-check-label" for="prep_warm">Morno</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="preparation[]" value="cold" id="prep_cold">
                    <label class="form-check-label" for="prep_cold">Frio</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" rows="3" 
                          placeholder="Descrição da bebida..."></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Imagem</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Criar Bebida
                </button>
                <a href="browse.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
