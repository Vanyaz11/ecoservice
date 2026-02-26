<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
$u = current_user();

// Separate cabinets by role: students have their own dashboard,
// teachers/admins are redirected to the teacher panel.
if (in_array($u['role'], ['teacher','admin'], true)) {
  header('Location: ' . url('teacher/dashboard.php', true));
  exit;
}
?>
<div class="card">
  <h2>Личный кабинет</h2>
  <p><strong><?php echo h($u['name']); ?></strong> (<?php echo h($u['email']); ?>)</p>
  <div class="grid">
    <div class="card"><h3>Мои курсы</h3><p><a class="btn" href="<?php echo url('learning.php'); ?>">Перейти</a></p></div>
    <div class="card"><h3>Эко‑дневник</h3><p><a class="btn" href="<?php echo url('eco_diary.php'); ?>">Перейти</a></p></div>
    <div class="card"><h3>События</h3><p><a class="btn" href="<?php echo url('events.php'); ?>">Перейти</a></p></div>
    <div class="card"><h3>Достижения</h3><p><a class="btn" href="<?php echo url('achievements.php'); ?>">Открыть</a></p></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
