<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_login(); require_teacher();
$pdo = db();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $short = trim($_POST['short_desc'] ?? '');
  $content = trim($_POST['content'] ?? '');
  if ($title) {
    // Support both DB schemas (with or without author_id)
    $hasAuthor = false;
    try {
      $q = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='courses' AND COLUMN_NAME='author_id' LIMIT 1");
      $hasAuthor = (bool)$q->fetch();
    } catch (Exception $e) { $hasAuthor = false; }

    if ($hasAuthor) {
      $st = $pdo->prepare("INSERT INTO courses (title, short_desc, content, author_id) VALUES (?,?,?,?)");
      $st->execute([$title, $short, $content, current_user()['id']]);
    } else {
      $st = $pdo->prepare("INSERT INTO courses (title, short_desc, content) VALUES (?,?,?)");
      $st->execute([$title, $short, $content]);
    }
    header('Location: '.url('teacher/dashboard.php', true));
    exit;
  } else { $error = 'Укажите название курса'; }
}
?>
<div class="card">
  <div class="card-header">
    <div>
      <h2 class="card-title">Создание курса</h2>
      <div class="card-subtitle">Заполните информацию о курсе. После создания вы сможете добавлять разделы.</div>
    </div>
    <div class="actions">
      <a class="btn ghost sm" href="dashboard.php">← К панели</a>
    </div>
  </div>

  <?php if ($error): ?><div class="alert error"><?php echo h($error); ?></div><?php endif; ?>

  <form method="post" class="form">
    <div class="form-grid">
      <div class="field">
        <label for="title">Название</label>
        <input id="title" name="title" required value="<?php echo h($_POST['title'] ?? ''); ?>">
      </div>

      <div class="field">
        <label for="short_desc">Краткое описание</label>
        <input id="short_desc" name="short_desc" value="<?php echo h($_POST['short_desc'] ?? ''); ?>">
      </div>

      <div class="field field-span-2">
        <label for="content">Содержание (основной текст)</label>
        <textarea id="content" name="content" rows="10"><?php echo h($_POST['content'] ?? ''); ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Создать курс</button>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
