<?php
http_response_code(404);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h2>Раздел удалён</h2>
  <p>Раздел «Файлы» больше не используется. Все документы размещаются в разделе <a href="<?= url('materials.php') ?>">«Материалы»</a>.</p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
