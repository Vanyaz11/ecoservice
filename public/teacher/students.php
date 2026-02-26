<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_login(); require_teacher();
$pdo = db();
$students = $pdo->query("SELECT id, name, email FROM users WHERE role='student' ORDER BY name")->fetchAll();
?>
<div class="card">
  <h2>Студенты и подписки</h2>
  <?php if (!$students): ?><p class="muted">Студентов пока нет.</p><?php endif; ?>
  <?php foreach($students as $s): ?>
    <div class="card">
      <h3><a href="<?php echo url('teacher/student_profile.php?id='.$s['id']); ?>"><?php echo h($s['name']); ?></a></h3>
      <div class="muted"><?php echo h($s['email']); ?></div>
    </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
