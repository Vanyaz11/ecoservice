<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/badges.php';
require_login();

$pdo = db();
$flash = null;
if (isset($_POST['register_event'])) {
  $uid = (int)current_user()['id'];
  $eventId = (int)$_POST['event_id'];
  // Проверка мест и вставка выполняются в одной транзакции с блокировкой строки
  // мероприятия, чтобы два одновременных запроса не заняли одно последнее место.
  $pdo->beginTransaction();
  try {
    $st = $pdo->prepare("SELECT seats - (SELECT count(*) FROM event_registrations WHERE event_id = e.id) AS left_seats
                         FROM events e WHERE e.id = ? FOR UPDATE");
    $st->execute([$eventId]);
    $left = $st->fetchColumn();
    if ($left === false) {
      $flash = ['err', 'Мероприятие не найдено'];
    } elseif ((int)$left <= 0) {
      $flash = ['err', 'Свободных мест нет'];
    } else {
      $ins = $pdo->prepare("INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?) ON CONFLICT DO NOTHING");
      $ins->execute([$uid, $eventId]);
      $flash = ['ok', $ins->rowCount() ? 'Вы зарегистрированы на мероприятие' : 'Вы уже были зарегистрированы'];
    }
    $pdo->commit();
  } catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
  }
  if ($flash[0] === 'ok') check_and_award_for_event($uid);
  // Post/Redirect/Get: повторная отправка формы при обновлении страницы исключена
  $_SESSION['flash'] = $flash;
  header('Location: ' . url('events.php'));
  exit;
}
if (!empty($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); }

$events = $pdo->query("SELECT id, title, starts_at, location, description, seats, 
  (seats - (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id=events.id)) as left_seats
  FROM events ORDER BY starts_at ASC")->fetchAll();

$my = $pdo->prepare("SELECT event_id FROM event_registrations WHERE user_id=?");
$my->execute([current_user()['id']]);
$my_ids = array_column($my->fetchAll(), 'event_id');
?>
<div class="card">
  <?php if ($flash): ?><div class="alert <?php echo $flash[0]==="ok" ? "success" : "error"; ?>"><?php echo h($flash[1]); ?></div><?php endif; ?>
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
