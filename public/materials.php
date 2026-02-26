<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();
$u = current_user();
$pdo = db();

$errors = [];
$success = null;

// Ensure table exists (soft check to avoid fatal on old DB)
$hasTable = true;
try {
  $pdo->query("SELECT 1 FROM materials LIMIT 1");
} catch (Exception $e) {
  $hasTable = false;
}

if (!$hasTable) {
  echo '<div class="card"><h2>Материалы</h2><p>Таблица материалов не найдена. Выполните миграцию: <code>sql/migrate_materials.sql</code></p></div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit();
}

$canUpload = in_array($u['role'], ['admin','teacher'], true);

// Upload handler
if ($canUpload && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if ($title === '') $errors[] = 'Укажите название материала.';
  if (!isset($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Выберите файл для загрузки.';
  }

  if (!$errors) {
    $f = $_FILES['file'];

    if ($f['error'] !== UPLOAD_ERR_OK) {
      $errors[] = 'Ошибка загрузки файла (код: ' . (int)$f['error'] . ').';
    } else {
      // Limits (can adjust)
      $maxBytes = 25 * 1024 * 1024; // 25 MB
      if ((int)$f['size'] > $maxBytes) {
        $errors[] = 'Файл слишком большой (макс. 25 МБ).';
      } else {
        $allowedExt = ['pdf','doc','docx','ppt','pptx','xls','xlsx','txt','jpg','jpeg','png','zip','rar'];
        $origName = $f['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
          $errors[] = 'Недопустимый тип файла. Разрешено: ' . implode(', ', $allowedExt);
        } else {
          $uploadDir = __DIR__ . '/../uploads/materials';
          if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

          $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
          $destPath = $uploadDir . '/' . $safeName;

          if (!move_uploaded_file($f['tmp_name'], $destPath)) {
            $errors[] = 'Не удалось сохранить файл на сервере.';
          } else {
            $mime = $f['type'] ?? '';
            $size = (int)$f['size'];
            $relPath = 'uploads/materials/' . $safeName;

            $stmt = $pdo->prepare("INSERT INTO materials (title, description, file_path, original_name, mime_type, file_size, uploaded_by) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$title, $description, $relPath, $origName, $mime, $size, (int)$u['id']]);

            $success = 'Материал загружен.';
          }
        }
      }
    }
  }
}

// Fetch materials
$q = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT m.*, u.name AS uploader_name
        FROM materials m
        LEFT JOIN users u ON u.id = m.uploaded_by";
if ($q !== '') {
  $sql .= " WHERE m.title LIKE ? OR m.description LIKE ?";
  $params[] = "%$q%";
  $params[] = "%$q%";
}
$sql .= " ORDER BY m.uploaded_by DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<div class="card">
  <h2>Материалы</h2>
  <p>В этом разделе собраны учебные и методические материалы по экологической культуре.</p>

  <form method="get" class="row" style="gap:10px; align-items:end;">
    <div style="flex:1;">
      <label class="label">Поиск</label>
      <input class="input" type="text" name="q" value="<?= h($q) ?>" placeholder="Например: раздельный сбор, экология, проект…">
    </div>
    <button class="btn" type="submit">Найти</button>
    <?php if ($q !== ''): ?>
      <a class="btn secondary" href="<?= url('materials.php') ?>">Сбросить</a>
    <?php endif; ?>
  </form>
</div>

<?php if ($canUpload): ?>
  <div class="card">
    <h3>Добавить материал</h3>

    <?php if ($success): ?>
      <div class="alert success"><?= h($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert error">
        <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form">
      <input type="hidden" name="action" value="upload">
      <label class="label">Название</label>
      <input class="input" type="text" name="title" required>

      <label class="label">Описание (необязательно)</label>
      <textarea class="input" name="description" rows="3" placeholder="Кратко: что это за файл и для чего нужен"></textarea>

      <label class="label">Файл</label>
      <input class="input" type="file" name="file" required>

      <button class="btn" type="submit">Загрузить</button>
      <div class="hint">Доступно для ролей <b>admin</b> и <b>teacher</b>. Student может только просматривать и скачивать.</div>
    </form>
  </div>
<?php endif; ?>

<div class="card">
  <h3>Список материалов</h3>

  <?php if (!$items): ?>
    <p>Пока нет загруженных материалов.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Название</th>
          <th>Описание</th>
          <th>Загрузил</th>
          <th>Дата</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $m): ?>
          <tr>
            <td><b><?= h($m['title']) ?></b><div class="small"><?= h($m['original_name']) ?></div></td>
            <td><?= !empty($m['description']) ? nl2br(h($m['description'])) : '<span class="small">—</span>' ?></td>
            <td><?= !empty($m['uploader_name']) ? h($m['uploader_name']) : '<span class="small">—</span>' ?></td>
            <td><?= h(date('d.m.Y H:i', strtotime($m['uploaded_at']))) ?></td>
            <td><a class="btn" href="<?= url('material_download.php?id='.(int)$m['id']) ?>">Скачать</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
