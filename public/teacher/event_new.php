<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();
if (!is_teacher()) { header('Location: ../index.php'); exit; }

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $starts_at = trim($_POST['starts_at'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $seats = (int)($_POST['seats'] ?? 0);
  $description = trim($_POST['description'] ?? '');

  if (!$title || !$starts_at) {
    $err = 'Заполните название и дату/время';
  } else {
    $pdo = db();
    $stmt = $pdo->prepare("INSERT INTO events (title, description, location, starts_at, seats) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $location, $starts_at, $seats]);
    header('Location: ' . url('teacher/events.php')); exit;
  }
}
?>
<div class="card form-card">
  <h2>Создать событие</h2>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <form method="post">
    <label class="label">Название</label>
    <input class="input" name="title" required>
    <label class="label">Дата и время начала</label>
    <input class="input" type="datetime-local" name="starts_at" required>
    <label class="label">Место</label>
    <input class="input" name="location">
    <label class="label">Количество мест</label>
    <input class="input" type="number" name="seats" min="0" value="0">
    <label class="label">Описание</label>
    <textarea class="input" name="description" rows="5"></textarea>
    <div class="form-actions">
      <button class="btn">Сохранить</button>
      <a class="btn ghost" href="<?php echo url('teacher/events.php'); ?>">Отмена</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
