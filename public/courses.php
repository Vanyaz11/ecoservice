<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/badges.php';

require_login();

$pdo = db();
$u = current_user();

// enroll / unenroll actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['enroll'])) {
    $course_id = (int)($_POST['course_id'] ?? 0);
    if ($course_id > 0) {
      $stmt = $pdo->prepare("INSERT IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)");
      $stmt->execute([$u['id'], $course_id]);
      // award badges related to learning
      check_and_award_for_entry($u['id']);
    }
  }
  if (isset($_POST['unenroll'])) {
    $course_id = (int)($_POST['course_id'] ?? 0);
    if ($course_id > 0) {
      $stmt = $pdo->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
      $stmt->execute([$u['id'], $course_id]);
    }
  }
}

$q = trim($_GET['q'] ?? '');

// My courses
$my = $pdo->prepare(
  "SELECT c.id, c.title, c.short_desc, e.enrolled_at,
          (SELECT COUNT(*) FROM course_sections s WHERE s.course_id = c.id) as sections_cnt
   FROM enrollments e
   JOIN courses c ON c.id = e.course_id
   WHERE e.user_id = ?
   ORDER BY e.enrolled_at DESC"
);
$my->execute([$u['id']]);
$my_courses = $my->fetchAll();

// All courses (with search)
$params = [];
$sql = "SELECT c.id, c.title, c.short_desc,
               (SELECT COUNT(*) FROM course_sections s WHERE s.course_id = c.id) as sections_cnt,
               (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as enrolled_cnt
        FROM courses c";
if ($q !== '') {
  $sql .= " WHERE c.title LIKE ? OR c.short_desc LIKE ?";
  $params[] = "%{$q}%";
  $params[] = "%{$q}%";
}
$sql .= " ORDER BY c.id DESC";
$st = $pdo->prepare($sql);
$st->execute($params);
$courses = $st->fetchAll();

$my_ids = array_column($my_courses, 'id');
?>
<div class="card">
  <h2>Курсы</h2>
  <p class="muted">Здесь вы можете записаться на курс, продолжить обучение и отслеживать свои курсы.</p>

  <form method="get" style="margin: 14px 0 0;">
    <label class="label" for="q">Поиск</label>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <input class="input" id="q" name="q" value="<?php echo h($q); ?>" placeholder="Например: сортировка, климат, отходы">
      <button class="btn" type="submit">Найти</button>
      <?php if ($q !== ''): ?>
        <a class="btn secondary" href="<?php echo url('courses.php'); ?>">Сброс</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3>Мои курсы</h3>
  <?php if (!$my_courses): ?>
    <p class="muted">Пока вы не записаны ни на один курс. Выберите курс ниже и нажмите «Записаться».</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($my_courses as $c): ?>
        <div class="card">
          <h4><?php echo h($c['title']); ?></h4>
          <p><?php echo h($c['short_desc']); ?></p>
          <p class="badge">Разделов: <?php echo (int)$c['sections_cnt']; ?></p>
          <p class="muted">Записаны: <?php echo h($c['enrolled_at']); ?></p>
          <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
            <a class="btn secondary" href="<?php echo url('learning_view.php?id='.(int)$c['id']); ?>">Открыть</a>
            <form method="post" onsubmit="return confirm('Отписаться от курса?');">
              <input type="hidden" name="course_id" value="<?php echo (int)$c['id']; ?>">
              <button class="btn" name="unenroll">Отписаться</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <h3>Все курсы</h3>
  <?php if (!$courses): ?>
    <p class="muted">Курсы не найдены.</p>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($courses as $c): ?>
      <div class="card">
        <h4><?php echo h($c['title']); ?></h4>
        <p><?php echo h($c['short_desc']); ?></p>
        <p class="badge">Разделов: <?php echo (int)$c['sections_cnt']; ?> · Учащихся: <?php echo (int)$c['enrolled_cnt']; ?></p>

        <?php if (in_array($c['id'], $my_ids)): ?>
          <a class="btn secondary" href="<?php echo url('learning_view.php?id='.(int)$c['id']); ?>">Продолжить</a>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="course_id" value="<?php echo (int)$c['id']; ?>">
            <button class="btn" name="enroll">Записаться</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
