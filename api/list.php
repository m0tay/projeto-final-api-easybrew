<?php
require_once '../config.php';
require_once '../core.php';

$files = scandir(UPLOAD_PATH);
foreach ($files as $f) {
  if ($f != '.' && $f != '..') {
?>
    <div class="row mb-2">
      <div class="col-12">
        <form action="../utils/download.php" target="_blank" method="post" class="d-flex align-items-center">
          <input type="hidden" name="action" value="file">
          <input type="hidden" name="filename" value="<?php echo $f; ?>">
          <button type="submit" class="btn btn-primary btn-sm me-2">
            <i class="fa fa-download"></i> Download
          </button>
          <span><?php echo htmlspecialchars($f); ?></span>
        </form>
      </div>
    </div>
<?php
  }
}
?>
