<?php
require_once '../config.php';
require_once '../core.php';

session_start();

require_once __DIR__ . '/config_local.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ' . ADMIN_BASE_PATH . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    
    $ch = curl_init($_ENV['URL_BASE'] . 'api/auth/login.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // <-- aplicar esta linha
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // <-- aplicar esta linha

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($httpCode == 200 && isset($result['jwt'])) {
        require_once __DIR__ . '/../vendor/autoload.php';
        require_once __DIR__ . '/../config.php';
        
        $decoded = \Firebase\JWT\JWT::decode($result['jwt'], new \Firebase\JWT\Key($jwt_conf['key'], $jwt_conf['alg']));
        
        if ($decoded->data->role !== 'admin') {
            $error = 'Acesso negado: apenas administradores';
        } else {
            $_SESSION['logged_in'] = true;
            $_SESSION['jwt'] = $result['jwt'];
            $_SESSION['user'] = [
                'id' => $decoded->data->id,
                'email' => $decoded->data->email,
                'first_name' => $decoded->data->first_name,
                'last_name' => $decoded->data->last_name,
                'role' => $decoded->data->role,
                'balance' => $decoded->data->balance,
                'is_active' => $decoded->data->is_active
            ];
            
            header('Location: ' . ADMIN_BASE_PATH . '/index.php');
            exit;
        }
    } else {
        $error = $result['message'] ?? 'Erro ao iniciar sEssão' . var_dump($curlError) . var_dump($_ENV['URL_BASE'] . 'api/auth/login.php');
    }
}

if (isset($_GET['erro'])) {
    if ($_GET['erro'] === 'sessao_expirada') {
        $error = 'Sessão expirada. Por favor, inicie sessão novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Login - EasyBrew Admin</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">EasyBrew Admin</h3></div>
                                    <div class="card-body">
                                        <?php if ($error): ?>
                                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                        <?php endif; ?>
                                        <form method="POST">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputEmail" name="email" type="email" placeholder="email@exemplo.com" required />
                                                <label for="inputEmail">Email</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputPassword" name="password" type="password" placeholder="Password" required />
                                                <label for="inputPassword">Password</label>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-end mt-4 mb-0">
                                                <button type="submit" class="btn btn-primary">Entrar</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small">Copyright &copy; EasyBrew <?= date('Y') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
