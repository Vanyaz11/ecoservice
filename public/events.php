<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/badges.php';
require_login();

$pdo = db();
if (isset($_POST['register_event'])) {
  $stmt = $pdo->prepare("INSERT IGNORE INTO event_registrations (user_id, event_id) VALUES (?, ?)");
  $stmt->execute([current_user()['id'], (int)$_POST['event_id']]);
  check_and_award_for_entry(current_user()['id']);
}

$events = $pdo->query("SELECT id, title, starts_at, location, description, seats, 
  (seats - (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id=events.id)) as left_seats
  FROM events ORDER BY starts_at ASC")->fetchAll();

$my = $pdo->prepare("SELECT event_id FROM event_registrations WHERE user_id=?");
$my->execute([current_user()['id']]);
$my_ids = array_column($my->fetchAll(), 'event_id');
?>
<div class="card">
  <div style="margin:8px 0 14px">
    <a class="btn" href="<?php echo url('export_events.php?type=xlsx'); ?>">Скачать .xlsx</a>
    <a class="btn secondary" href="<?php echo url('export_events.php?type=docx'); ?>">Скачать .docx</a>
  </div>
  <div class="grid">
    <?php foreach ($events as $ev): ?>
      <div class="card">
        <h3><?php echo h($ev['title']); ?></h3>
        <p><strong>Когда:</strong> <?php echo h($ev['starts_at']); ?><br>
        <strong>Где:</strong> <?php echo h($ev['location']); ?></p>
        <p><?php echo h($ev['description']); ?></p>
        <p class="badge">Мест осталось: <?php echo max(0,(int)$ev['left_seats']); ?></p>
        <?php if (in_array($ev['id'], $my_ids)): ?>
          <span class="badge">Вы зарегистрированы</span>
        <?php elseif ((int)$ev['left_seats'] <= 0): ?>
          <span class="badge">Нет мест</span>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="event_id" value="<?php echo (int)$ev['id']; ?>">
            <button class="btn" name="register_event">Записаться</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
