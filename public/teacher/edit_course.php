<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';

require_login();
require_teacher();

$pdo = db();
$uid = (int)current_user()['id'];
$id  = (int)($_GET['id'] ?? 0);

$err = null;

// Load course
$st = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$st->execute([$id]);
$course = $st->fetch();

if (!$course) {
  http_response_code(404);
  echo "<div class='card'>Курс не найден</div>";
  require_once __DIR__ . '/../../includes/footer.php';
  exit;
}

// Ownership: if schema has author_id then enforce it (admins may edit any)
if (array_key_exists('author_id', $course) && user_role() !== 'admin') {
  if ((int)$course['author_id'] !== $uid) {
    http_response_code(403);
    echo "<div class='card'>Можно редактировать только свои курсы</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
  $title   = trim($_POST['title'] ?? '');
  $short   = trim($_POST['short_desc'] ?? '');
  $content = trim($_POST['content'] ?? '');

  if ($title === '') {
    $err = 'Укажите название курса';
  } else {
    $pdo->prepare("UPDATE courses SET title=?, short_desc=?, content=? WHERE id=?")
        ->execute([$title, $short, $content, $id]);

    header('Location: ' . url('teacher/edit_course.php?id=' . $id, true));
    exit;
  }
}

// Sections
$sec = $pdo->prepare("SELECT id, title, position FROM course_sections WHERE course_id = ? ORDER BY position ASC, id ASC");
$sec->execute([$id]);
$sections = $sec->fetchAll();
?>

<div class="card">
  <div class="card-header">
    <div>
      <h2 class="card-title">Редактирование курса</h2>
      <div class="card-subtitle">Курс: <strong><?php echo h($course['title']); ?></strong></div>
    </div>
    <div class="actions">
      <a class="btn secondary sm" href="<?php echo url('learning_view.php?id=' . (int)$id); ?>">Открыть как студент</a>
      <a class="btn ghost sm" href="<?php echo url('teacher/dashboard.php'); ?>">← К панели</a>
    </div>
  </div>

  <?php if ($err): ?>
    <div class="alert error"><?php echo h($err); ?></div>
  <?php endif; ?>

  <form method="post" class="form">
    <input type="hidden" name="save_course" value="1">

    <div class="form-grid">
      <div class="field">
        <label for="title">Название</label>
        <input id="title" name="title" required value="<?php echo h($_POST['title'] ?? $course['title']); ?>">
      </div>

      <div class="field">
        <label for="short_desc">Краткое описание</label>
        <input id="short_desc" name="short_desc" value="<?php echo h($_POST['short_desc'] ?? ($course['short_desc'] ?? '')); ?>">
      </div>

      <div class="field field-span-2">
        <label for="content">Содержание (основной текст)</label>
        <textarea id="content" name="content" rows="10"><?php echo h($_POST['content'] ?? ($course['content'] ?? '')); ?></textarea>
        <div class="hint">Поддерживается обычный текст. Если вы вставляете HTML — он будет сохранён как есть.</div>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn" type="submit">Сохранить</button>
    </div>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <h3 class="card-title" style="font-size:20px">Разделы курса</h3>
      <div class="card-subtitle">Управляйте структурой курса: порядок, редактирование и удаление разделов.</div>
    </div>
    <div class="actions">
      <a class="btn sm" href="<?php echo url('teacher/add_section.php?course_id=' . (int)$id); ?>">+ Добавить раздел</a>
    </div>
  </div>

  <?php if (!$sections): ?>
    <div class="empty">
      <div class="empty-title">Разделов пока нет</div>
      <p class="empty-text">Добавьте первый раздел, чтобы курс можно было проходить.</p>
      <a class="btn" href="<?php echo url('teacher/add_section.php?course_id=' . (int)$id); ?>">+ Добавить раздел</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table table-nice">
        <thead>
          <tr>
            <th style="width:1%;white-space:nowrap">Позиция</th>
            <th>Заголовок</th>
            <th class="col-actions">Действия</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($sections as $s): ?>
          <tr>
            <td><?php echo (int)$s['position']; ?></td>
            <td class="title-cell"><?php echo h($s['title']); ?></td>
            <td class="col-actions">
              <a class="btn secondary sm" href="<?php echo url('teacher/edit_section.php?id='.(int)$s['id']); ?>">Редактировать</a>
              <form method="post" action="<?php echo url('teacher/section_delete.php'); ?>" style="display:inline-block" onsubmit="return confirm('Удалить раздел?');">
                <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                <input type="hidden" name="course_id" value="<?php echo (int)$id; ?>">
                <button class="btn danger sm" type="submit">Удалить</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
