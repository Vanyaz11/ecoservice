<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_login(); require_teacher();
$pdo = db();
$course_id = (int)($_GET['course_id'] ?? 0);

// Support both DB schemas (with or without author_id)
$hasAuthor = false;
try {
  $q = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='courses' AND COLUMN_NAME='author_id' LIMIT 1");
  $hasAuthor = (bool)$q->fetch();
} catch (Exception $e) { $hasAuthor = false; }

if ($hasAuthor) {
  $st = $pdo->prepare("SELECT id, title, author_id FROM courses WHERE id = ?");
  $st->execute([$course_id]);
  $course = $st->fetch();
  if (!$course || (user_role() !== 'admin' && (int)$course['author_id'] !== (int)current_user()['id'])) {
    echo "<div class='card'>Курс не найден или нет доступа</div>";
    require_once __DIR__ . '/../../includes/footer.php'; exit;
  }
} else {
  $st = $pdo->prepare("SELECT id, title FROM courses WHERE id = ?");
  $st->execute([$course_id]);
  $course = $st->fetch();
  if (!$course) {
    echo "<div class='card'>Курс не найден</div>";
    require_once __DIR__ . '/../../includes/footer.php'; exit;
  }
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $position = (int)($_POST['position'] ?? 1);
  if ($title) {
    $ins = $pdo->prepare("INSERT INTO course_sections (course_id,title,content,position) VALUES (?,?,?,?)");
    $ins->execute([$course_id,$title,$content,$position]);
    header('Location: '.url('learning_view.php?id='.$course_id, true));
    exit;
  } else { $error = 'Укажите заголовок раздела'; }
}
?>
<div class="card">
  <div class="card-header">
    <div>
      <h2 class="card-title">Добавить раздел</h2>
      <div class="card-subtitle">Курс: <strong><?php echo h($course['title']); ?></strong></div>
    </div>
    <div class="actions">
      <a class="btn ghost sm" href="dashboard.php">← К панели</a>
    </div>
  </div>

  <?php if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>

  <form method="post" class="form">
    <div class="form-grid">
      <div class="field">
        <label for="position">Позиция</label>
        <input id="position" type="number" min="1" name="position" value="<?php echo h($_POST['position'] ?? '1'); ?>" required>
      </div>

      <div class="field">
        <label for="title">Заголовок</label>
        <input id="title" name="title" required value="<?php echo h($_POST['title'] ?? ''); ?>">
      </div>

      <div class="field field-span-2">
        <label for="content">Контент раздела</label>
        <textarea id="content" name="content" rows="10"><?php echo h($_POST['content'] ?? ''); ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Добавить</button>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
