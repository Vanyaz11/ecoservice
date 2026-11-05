<?php
$pageTitle = 'Администрирование';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_admin();
require __DIR__ . '/_nav.php';
$pdo = db();
$s = $pdo->query("SELECT
  (SELECT count(*) FROM users) AS users,
  (SELECT count(*) FROM users WHERE role='student') AS students,
  (SELECT count(*) FROM users WHERE role='teacher') AS teachers,
  (SELECT count(*) FROM courses) AS courses,
  (SELECT count(*) FROM events) AS events,
  (SELECT count(*) FROM event_registrations) AS regs,
  (SELECT count(*) FROM eco_entries) AS entries,
  (SELECT count(*) FROM user_badges) AS badges,
  (SELECT count(*) FROM feedback WHERE status='new') AS new_fb,
  (SELECT count(*) FROM materials) AS materials")->fetch();
$recent = $pdo->query("SELECT u.name, e.type, e.value, e.created_at FROM eco_entries e JOIN users u ON u.id=e.user_id ORDER BY e.created_at DESC LIMIT 8")->fetchAll();
$ver = $pdo->query("SHOW server_version")->fetchColumn();
?>
<div class="grid">
  <?php foreach ([['Пользователей', $s['users']], ['Студентов', $s['students']], ['Преподавателей', $s['teachers']], ['Курсов', $s['courses']], ['Мероприятий', $s['events']], ['Регистраций', $s['regs']], ['Эко-записей', $s['entries']], ['Выдано бейджей', $s['badges']], ['Материалов', $s['materials']], ['Новых обращений', $s['new_fb']]] as [$t, $v]): ?>
    <div class="card"><div class="small"><?php echo $t; ?></div><h2 style="margin:4px 0"><?php echo (int)$v; ?></h2></div>
  <?php endforeach; ?>
</div>
<div class="card">
  <h3>Последние записи эко-дневников</h3>
  <table class="table"><thead><tr><th>Студент</th><th>Действие</th><th>Значение</th><th>Дата</th></tr></thead><tbody>
  <?php foreach ($recent as $r): ?><tr><td><?php echo h($r['name']); ?></td><td><?php echo h($r['type']); ?></td><td><?php echo h($r['value']); ?></td><td><?php echo date('d.m.Y H:i', strtotime($r['created_at'])); ?></td></tr><?php endforeach; ?>
  </tbody></table>
</div>
<div class="card">
  <h3>Сведения о системе</h3>
  <p class="small">PHP <?php echo PHP_VERSION; ?> · PostgreSQL <?php echo h($ver); ?> · Эко-сервис 1.1 · Автор: <?php echo h(app_config()['app']['author']); ?></p>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
