<?php
$pageTitle = 'Мероприятия';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_admin();
require __DIR__ . '/_nav.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unregister'])) {
  $pdo->prepare("DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?")->execute([(int)$_POST['user_id'], (int)$_POST['event_id']]);
  header('Location: ' . url('admin/events.php?id=' . (int)$_POST['event_id'])); exit;
}
$events = $pdo->query("SELECT e.*, (SELECT count(*) FROM event_registrations r WHERE r.event_id = e.id) AS regs FROM events e ORDER BY e.starts_at DESC")->fetchAll();
$sel = (int)($_GET['id'] ?? 0);
$members = [];
if ($sel) {
  $st = $pdo->prepare("SELECT u.id, u.name, u.email, r.registered_at FROM event_registrations r JOIN users u ON u.id = r.user_id WHERE r.event_id = ? ORDER BY r.registered_at");
  $st->execute([$sel]); $members = $st->fetchAll();
}
?>
<div class="card">
  <h3>Мероприятия и участники</h3>
  <p class="small">Создание и редактирование мероприятий — в <a href="<?php echo url('teacher/events.php'); ?>">кабинете преподавателя</a>; здесь — контроль регистраций.</p>
  <div style="overflow-x:auto">
  <table class="table">
    <thead><tr><th>Дата</th><th>Название</th><th>Место</th><th>Мест</th><th>Записано</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($events as $e): ?>
      <tr>
        <td><?php echo date('d.m.Y H:i', strtotime($e['starts_at'])); ?></td><td><?php echo h($e['title']); ?></td><td><?php echo h($e['location']); ?></td>
        <td><?php echo (int)$e['seats']; ?></td><td><?php echo (int)$e['regs']; ?></td>
        <td><a class="btn secondary" href="<?php echo url('admin/events.php?id=' . (int)$e['id']); ?>">Участники</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php if ($sel): ?>
<div class="card">
  <h3>Участники мероприятия №<?php echo $sel; ?></h3>
  <?php if (!$members): ?><p class="small">Регистраций нет.</p><?php endif; ?>
  <table class="table">
    <thead><tr><th>Студент</th><th>E-mail</th><th>Дата записи</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($members as $m): ?>
      <tr><td><?php echo h($m['name']); ?></td><td><?php echo h($m['email']); ?></td><td><?php echo date('d.m.Y H:i', strtotime($m['registered_at'])); ?></td>
        <td><form method="post"><input type="hidden" name="user_id" value="<?php echo (int)$m['id']; ?>"><input type="hidden" name="event_id" value="<?php echo $sel; ?>"><button class="btn secondary" name="unregister" type="submit">Снять с регистрации</button></form></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
