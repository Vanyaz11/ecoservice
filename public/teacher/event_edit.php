<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();
if (!is_teacher()) { header('Location: ../index.php'); exit; }

$pdo = db();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
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
    $stmt = $pdo->prepare("UPDATE events SET title=?, description=?, location=?, starts_at=?, seats=? WHERE id=?");
    $stmt->execute([$title, $description, $location, $starts_at, $seats, $id]);
    header('Location: ' . url('teacher/events.php')); exit;
  }
}

$e = $pdo->prepare("SELECT * FROM events WHERE id=?");
$e->execute([$id]);
$e = $e->fetch();
if (!$e) { echo "<div class='alert error'>Событие не найдено</div>"; require_once __DIR__ . '/../../includes/footer.php'; exit; }
?>
<div class="card form-card">
  <h2>Редактировать событие</h2>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
    <label class="label">Название</label>
    <input class="input" name="title" value="<?php echo h($e['title']); ?>" required>
    <label class="label">Дата и время начала</label>
    <input class="input" type="datetime-local" name="starts_at" value="<?php echo str_replace(' ', 'T', h($e['starts_at'])); ?>" required>
    <label class="label">Место</label>
    <input class="input" name="location" value="<?php echo h($e['location']); ?>">
    <label class="label">Количество мест</label>
    <input class="input" type="number" name="seats" min="0" value="<?php echo (int)$e['seats']; ?>">
    <label class="label">Описание</label>
    <textarea class="input" name="description" rows="5"><?php echo h($e['description']); ?></textarea>
    <div class="form-actions">
      <button class="btn">Сохранить</button>
      <a class="btn ghost" href="<?php echo url('teacher/events.php'); ?>">Отмена</a>
    </div>
  </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
