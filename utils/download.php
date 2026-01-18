<?php
require_once '../config.php';
require_once '../core.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die('Method Not Allowed');
}

$action = $_POST['action'] ?? null;
$filename = $_POST['filename'] ?? null;

if (!$action || !$filename) {
  http_response_code(400);
  die('Missing required parameters');
}

$filename = basename($filename);

if ($action === 'file') {
  $filepath = UPLOAD_PATH . $filename;

  if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
  }

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $filepath);
  finfo_close($finfo);

  header('Content-Type: ' . $mime);
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header('Content-Length: ' . filesize($filepath));
  header('Cache-Control: no-cache, must-revalidate');
  header('Pragma: no-cache');
  header('Expires: 0');

  readfile($filepath);
  exit;
} else {
  http_response_code(400);
  die('Invalid action');
}
