<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../includes/badges.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$u = current_user();

$stmt = $pdo->prepare("SELECT id, title, content FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course){
  echo '<div class="card">Курс не найден</div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

// Access: enrolled OR teacher/admin
$en = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ?");
$en->execute([$u['id'], $id]);
$is_enrolled = (bool)$en->fetchColumn();

if (!$is_enrolled && !is_teacher()) {
  // allow quick enroll
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $ins = $pdo->prepare("INSERT IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)");
    $ins->execute([$u['id'], $id]);
    check_and_award_for_entry($u['id']);
    header('Location: learning_view.php?id=' . $id);
    exit;
  }

  ?>
  <div class="card">
    <h2><?php echo h($course['title']); ?></h2>
    <p class="muted">Чтобы открыть материалы курса, нужно записаться.</p>
    <form method="post">
      <button class="btn" name="enroll">Записаться на курс</button>
      <a class="btn secondary" href="<?php echo url('courses.php'); ?>">Назад к курсам</a>
    </form>
  </div>
  <?php
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

// Sections
$sec = $pdo->prepare("SELECT id, title, content, position FROM course_sections WHERE course_id = ? ORDER BY position ASC, id ASC");
$sec->execute([$id]);
$sections = $sec->fetchAll();
?>
<div class="card">
  <h2><?php echo h($course['title']); ?></h2>
  <div><?php echo $course['content']; /* trusted HTML from teachers/admin */ ?></div>
</div>

<?php if ($sections): ?>
<div class="card">
  <h3>Разделы курса</h3>
  <?php foreach($sections as $s): ?>
    <section class="course-section" style="margin-top:14px">
      <h4><?php echo (int)$s['position']; ?>. <?php echo h($s['title']); ?></h4>
      <div><?php echo $s['content']; ?></div>
    </section>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
