<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_login(); require_teacher();
$pdo = db();
$student_id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT id, name, email FROM users WHERE id=? AND role='student'");
$st->execute([$student_id]);
$s = $st->fetch();
if (!$s){ echo "<div class='card'>Студент не найден</div>"; require_once __DIR__ . '/../../includes/footer.php'; exit; }

$c = $pdo->prepare("SELECT c.id, c.title FROM enrollments e JOIN courses c ON c.id=e.course_id WHERE e.user_id=?");
$c->execute([$student_id]);
$courses = $c->fetchAll();

$e = $pdo->prepare("SELECT ev.id, ev.title, ev.starts_at FROM event_registrations r JOIN events ev ON ev.id=r.event_id WHERE r.user_id=? ORDER BY ev.starts_at");
$e->execute([$student_id]);
$events = $e->fetchAll();
?>
<div class="card">
  <h2><?php echo h($s['name']); ?></h2>
  <div class="muted"><?php echo h($s['email']); ?></div>
  <h3>Курсы</h3>
  <ul>
    <?php foreach($courses as $x): ?>
      <li><a href="<?php echo url('learning_view.php?id='.$x['id']); ?>"><?php echo h($x['title']); ?></a></li>
    <?php endforeach; if (!$courses) echo "<li class='muted'>нет</li>"; ?>
  </ul>
  <h3>События</h3>
  <ul>
    <?php foreach($events as $x): ?>
      <li><?php echo h($x['title']); ?> — <span class="muted"><?php echo h($x['starts_at']); ?></span></li>
    <?php endforeach; if (!$events) echo "<li class='muted'>нет</li>"; ?>
  </ul>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
