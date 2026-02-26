<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/badges.php';
require_login();

$pdo = db();
if (isset($_POST['enroll'])) {
  $stmt = $pdo->prepare("INSERT IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)");
  $stmt->execute([current_user()['id'], (int)$_POST['course_id']]);
  check_and_award_for_entry(current_user()['id']);
}

$courses = $pdo->query("SELECT c.id, c.title, c.short_desc FROM courses c ORDER BY c.id DESC")->fetchAll();
$my = $pdo->prepare("SELECT course_id FROM enrollments WHERE user_id = ?");
$my->execute([current_user()['id']]);
$my_ids = array_column($my->fetchAll(), 'course_id');
?>
<div class="card">
  <h2>Обучение</h2>
  <p class="badge">Курсы</p>
  <div class="grid">
    <?php foreach ($courses as $c): ?>
      <div class="card">
        <h3><?php echo h($c['title']); ?></h3>
        <p><?php echo h($c['short_desc']); ?></p>
        <?php if (in_array($c['id'], $my_ids)): ?>
          <a class="btn secondary" href="<?php echo url('learning_view.php?id=' . $c['id']); ?>">Продолжить</a>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="course_id" value="<?php echo (int)$c['id']; ?>">
            <button class="btn" name="enroll">Записаться</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
