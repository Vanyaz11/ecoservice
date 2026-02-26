<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_login(); require_teacher();
$pdo = db();
$uid = current_user()['id'];

// Support both DB schemas: with or without courses.author_id
$hasAuthor = false;
try {
  $st = $pdo->prepare("SELECT COUNT(*) AS c
                       FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE()
                         AND TABLE_NAME = 'courses'
                         AND COLUMN_NAME = 'author_id'");
  $st->execute();
  $hasAuthor = ((int)($st->fetch()['c'] ?? 0)) > 0;
} catch (Exception $e) { $hasAuthor = false; }

if ($hasAuthor) {
  $courses = $pdo->prepare("SELECT id, title FROM courses WHERE author_id = ? ORDER BY id DESC");
  $courses->execute([$uid]);
  $my = $courses->fetchAll();
} else {
  // Old schema: show all courses
  $my = $pdo->query("SELECT id, title FROM courses ORDER BY id DESC")->fetchAll();
}
?>

<div class="card">
  <div class="card-header">
    <div>
      <h2 class="card-title">Панель преподавателя</h2>
      <div class="card-subtitle">Управляйте курсами, разделами и подписками студентов</div>
    </div>
    <div class="actions">
      <a class="btn" href="<?php echo url('teacher/create_course.php'); ?>">+ Создать курс</a>
      <a class="btn secondary" href="<?php echo url('teacher/students.php'); ?>">Студенты и подписки</a>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div>
      <h3 style="margin:0">Мои курсы</h3>
      <div class="small">Нажмите на название курса, чтобы посмотреть его как студент</div>
    </div>
    <?php if (!$hasAuthor): ?>
    <?php endif; ?>
  </div>

  <?php if (!$my): ?>
    <div class="alert" style="background:#f3f4f6;border:1px solid #e5e7eb;color:#374151">
      <?php echo $hasAuthor ? 'У вас пока нет курсов. Создайте первый курс — и добавьте в него разделы.' : 'Пока нет курсов.'; ?>
    </div>
    <a class="btn" href="<?php echo url('teacher/create_course.php'); ?>">Создать курс</a>
  <?php else: ?>
    <div class="grid" style="margin-top:14px">
      <?php foreach($my as $c): ?>
        <div class="card course-card" style="margin:0">
          <div class="course-meta">
            <span class="badge">Курс #<?php echo (int)$c['id']; ?></span>
          </div>
          <h3>
            <a class="title" href="<?php echo url('learning_view.php?id='.$c['id']); ?>">
              <?php echo h($c['title']); ?>
            </a>
          </h3>
          <div class="course-actions">
            <a class="btn sm secondary" href="<?php echo url('teacher/edit_course.php?id='.$c['id']); ?>">Редактировать</a>
            <a class="btn sm ghost" href="<?php echo url('teacher/add_section.php?course_id='.$c['id']); ?>">+ Добавить раздел</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
