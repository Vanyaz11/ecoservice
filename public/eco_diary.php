<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/badges.php';
require_login();

$u = current_user();
if (($u['role'] ?? '') !== 'student') {
  http_response_code(403);
  echo '<div class="card"><h2>Эко‑дневник</h2><p>Раздел доступен только студентам.</p></div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

$pdo = db();
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = trim($_POST['type'] ?? '');
  $value = floatval($_POST['value'] ?? 0);
  $note = trim($_POST['note'] ?? '');
  if (!$type) $err = 'Выберите тип действия';
  else {
    $stmt = $pdo->prepare("INSERT INTO eco_entries (user_id, type, value, note) VALUES (?,?,?,?)");
    $stmt->execute([current_user()['id'], $type, $value, $note]);
    check_and_award_for_entry(current_user()['id']);
  }
}
$entries = $pdo->prepare("SELECT id, created_at, type, value, note FROM eco_entries WHERE user_id = ? ORDER BY created_at DESC");
$entries->execute([current_user()['id']]);
$entries = $entries->fetchAll();

$sum_stmt = $pdo->prepare("SELECT type, COUNT(*) as cnt, SUM(value) as total FROM eco_entries WHERE user_id = ? GROUP BY type");
$sum_stmt->execute([current_user()['id']]);
$stats = $sum_stmt->fetchAll();
?>
<div class="card">
  <h2>Эко‑дневник</h2>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <form method="post">
    <label class="label">Тип действия</label>
    <select class="input" name="type" required>
      <option value="">— Выберите —</option>
      <option>Сортировка отходов</option>
      <option>Экономия электроэнергии</option>
      <option>Мобильность без авто</option>
      <option>Отказ от одноразового пластика</option>
      <option>Участие в субботнике</option>
    </select>
    <label class="label">Количественный показатель (необязательно)</label>
    <input class="input" type="number" step="0.01" name="value" placeholder="кг, кВт·ч, км — по ситуации">
    <label class="label">Заметка</label>
    <input class="input" type="text" name="note" placeholder="Комментарий">
    <p style="margin-top:12px"><button class="btn" type="submit">Добавить запись</button></p>
  </form>
</div>

<div class="card">
  <h3>Мои записи</h3>
  <table class="table">
    <tr><th>Дата</th><th>Тип</th><th>Показатель</th><th>Заметка</th></tr>
    <?php foreach ($entries as $e): ?>
      <tr>
        <td><?php echo h($e['created_at']); ?></td>
        <td><?php echo h($e['type']); ?></td>
        <td><?php echo h($e['value']); ?></td>
        <td><?php echo h($e['note']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="card">
  <h3>Статистика</h3>
  <div class="grid">
    <?php foreach ($stats as $s): ?>
      <div class="card"><strong><?php echo h($s['type']); ?></strong><p>Количество: <?php echo (int)$s['cnt']; ?><br>Сумма: <?php echo h($s['total']); ?></p></div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
