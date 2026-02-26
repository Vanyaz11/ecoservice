<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';

require_login();
require_teacher();

$pdo = db();
$uid = (int)current_user()['id'];
$id  = (int)($_GET['id'] ?? 0);

$err = null;

// Load section + course
$st = $pdo->prepare(
  "SELECT s.*, c.title AS course_title FROM course_sections s JOIN courses c ON c.id = s.course_id WHERE s.id = ?"
);
$st->execute([$id]);
$section = $st->fetch();

if (!$section) {
  http_response_code(404);
  echo "<div class='card'>Раздел не найден</div>";
  require_once __DIR__ . '/../../includes/footer.php';
  exit;
}

// If schema has courses.author_id then enforce ownership (admins may edit any)
$course_id = (int)$section['course_id'];
$cst = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$cst->execute([$course_id]);
$course = $cst->fetch();
if ($course && array_key_exists('author_id', $course) && user_role() !== 'admin') {
  if ((int)$course['author_id'] !== $uid) {
    http_response_code(403);
    echo "<div class='card'>Нет доступа к редактированию этого раздела</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title    = trim($_POST['title'] ?? '');
  $content  = trim($_POST['content'] ?? '');
  $position = (int)($_POST['position'] ?? 1);

  if ($title === '') {
    $err = 'Укажите заголовок раздела';
  } else {
    $pdo->prepare("UPDATE course_sections SET title=?, content=?, position=? WHERE id=?")
        ->execute([$title, $content, $position, $id]);
    header('Location: ' . url('teacher/edit_course.php?id=' . $course_id, true));
    exit;
  }
}
?>

<div class="card">
  <div class="card-header">
    <div>
      <h2 class="card-title">Редактирование раздела</h2>
      <div class="card-subtitle">Курс: <strong><?php echo h($section['course_title']); ?></strong></div>
    </div>
    <div class="actions">
      <a class="btn ghost sm" href="<?php echo url('teacher/edit_course.php?id=' . (int)$course_id); ?>">← Назад к курсу</a>
    </div>
  </div>

  <?php if ($err): ?>
    <div class="alert error"><?php echo h($err); ?></div>
  <?php endif; ?>

  <form method="post" class="form">
    <div class="form-grid">
      <div class="field">
        <label for="position">Позиция</label>
        <input id="position" type="number" min="1" name="position" value="<?php echo h($_POST['position'] ?? $section['position']); ?>" required>
      </div>

      <div class="field">
        <label for="title">Заголовок</label>
        <input id="title" name="title" required value="<?php echo h($_POST['title'] ?? $section['title']); ?>">
      </div>

      <div class="field field-span-2">
        <label for="content">Контент раздела</label>
        <textarea id="content" name="content" rows="12"><?php echo h($_POST['content'] ?? ($section['content'] ?? '')); ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Сохранить</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
