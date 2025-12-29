<?php
require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';
require_once '../../vendor/autoload.php';

$pdo = connectDB($db);
$user = new User($pdo);

use \Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents('php://input'));

$code = 400;
$response = ['message' => 'Dados inválidos'];

if (!empty($data)) {
  $user->email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : '';
  
  if (empty($data->email)) {
    $response = ['message' => 'Email não definido'];
  } else {
    if (!$user->emailExists()) {
      $code = 404;
      $response = ['message' => 'Email não encontrado'];
    } else if (boolval($user->email_verified)) {
      $code = 400;
      $response = ['message' => 'Email já verificado'];
    } else {
      // Generate new token and expiration
      $new_token = bin2hex(random_bytes(32));
      $new_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
      
      // Update user with new token
      $query = "UPDATE users SET email_verification_token = :token, email_verification_expires = :expires WHERE id = :id";
      $stmt = $pdo->prepare($query);
      $stmt->bindValue(':token', $new_token);
      $stmt->bindValue(':expires', $new_expires);
      $stmt->bindValue(':id', $user->id);
      
      if ($stmt->execute()) {
        $confirmation_link = WEB_SERVER . WEB_ROOT . 'api/auth/confirm_email.php?token=' . $new_token;
        
        $mail = new PHPMailer(true);
        
        try {
          $mail->CharSet = EMAIL_CHARSET;
          $mail->Encoding = EMAIL_ENCODING;
          $mail->isSMTP();
          $mail->Host = EMAIL_HOST;
          $mail->SMTPAuth = EMAIL_SMTPAUTH;
          $mail->Username = EMAIL_USERNAME;
          $mail->Password = EMAIL_PASSWORD;
          $mail->Port = EMAIL_PORT;
          
          $mail->setFrom(EMAIL_USERNAME, EMAIL_FROM);
          $mail->addAddress($user->email, $user->first_name . ' ' . $user->last_name);
          
          $mail->isHTML(true);
          $mail->Subject = 'Reenvio de Confirmação de Registo - EasyBrew';
          $mail->Body = '
            <html>
            <head>
              <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #6f42c1; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .button { display: inline-block; background-color: #6f42c1; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; color: #777; font-size: 12px; margin-top: 20px; }
              </style>
            </head>
            <body>
              <div class="container">
                <div class="header">
                  <h1>EasyBrew</h1>
                </div>
                <div class="content">
                  <h2>Confirmação de Email</h2>
                  <p>Olá ' . htmlspecialchars($user->first_name) . ',</p>
                  <p>Recebeste este email porque solicitaste o reenvio da confirmação de registo.</p>
                  <p>Para ativares a tua conta, clica no botão abaixo:</p>
                  <p style="text-align: center;">
                    <a href="' . $confirmation_link . '" class="button">Confirmar Email</a>
                  </p>
                  <p>Ou copia e cola o seguinte link no teu navegador:</p>
                  <p style="word-break: break-all; background-color: #fff; padding: 10px; border: 1px solid #ddd;">
                    ' . $confirmation_link . '
                  </p>
                  <p><strong>Este link é válido por 24 horas.</strong></p>
                </div>
                <div class="footer">
                  <p>© ' . ANO_LETIVO . ' EasyBrew - Universidade de Aveiro</p>
                </div>
              </div>
            </body>
            </html>
          ';
          $mail->AltBody = "Olá {$user->first_name},\n\nRecebeste este email porque solicitaste o reenvio da confirmação de registo.\n\nPara ativares a tua conta, acede ao seguinte link:\n{$confirmation_link}\n\nEste link é válido por 24 horas.\n\n© " . ANO_LETIVO . " EasyBrew - Universidade de Aveiro";
          
          $mail->send();
          
          $code = 200;
          $response = ['message' => 'Email de confirmação reenviado. Verifica a tua caixa de entrada.'];
        } catch (Exception $e) {
          $code = 500;
          $response = ['message' => 'Erro ao enviar email: ' . $mail->ErrorInfo];
        }
      } else {
        $code = 500;
        $response = ['message' => 'Erro ao gerar novo token'];
      }
    }
  }
}

header('Content-Type: application/json; charset=UTF-8');
http_response_code($code);
echo json_encode($response);
