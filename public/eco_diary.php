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
$err = null; $ok = null;
// Виды действий — из справочника, только активные
$types = $pdo->query("SELECT name, unit FROM action_types WHERE is_active ORDER BY id")->fetchAll();
$allowed = array_column($types, 'unit', 'name');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $type = trim($_POST['type'] ?? '');
  $value = (float)str_replace(',', '.', $_POST['value'] ?? '0');
  $note = mb_substr(trim($_POST['note'] ?? ''), 0, 500);
  // Серверная валидация: вид действия только из справочника, значение положительное
  if ($type === '' || !isset($allowed[$type])) $err = 'Выберите вид действия из списка';
  elseif ($value <= 0) $err = 'Укажите значение больше нуля';
  else {
    $stmt = $pdo->prepare("INSERT INTO eco_entries (user_id, type, value, note) VALUES (?,?,?,?)");
    $stmt->execute([current_user()['id'], $type, $value, $note]);
    $awarded = check_and_award_for_entry(current_user()['id']);
    $_SESSION['flash_diary'] = 'Запись добавлена' . ($awarded ? '. Получен новый бейдж: ' . implode(', ', $awarded) : '');
    header('Location: ' . url('eco_diary.php')); // PRG: повторная отправка формы исключена
    exit;
  }
}
if (!empty($_SESSION['flash_diary'])) { $ok = $_SESSION['flash_diary']; unset($_SESSION['flash_diary']); }
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
  <?php if ($ok): ?><div class="alert success"><?php echo h($ok); ?></div><?php endif; ?>
  <form method="post">
    <label class="label" for="d-type">Вид действия *</label>
    <select class="input" id="d-type" name="type" required onchange="document.getElementById('d-unit').textContent=this.options[this.selectedIndex].dataset.unit||''">
      <option value="">— Выберите —</option>
      <?php foreach ($types as $t): ?><option data-unit="<?php echo h($t['unit']); ?>"><?php echo h($t['name']); ?></option><?php endforeach; ?>
    </select>
    <label class="label" for="d-value">Значение * <span class="small">(единица: <span id="d-unit">—</span>)</span></label>
    <input class="input" id="d-value" type="number" step="0.01" min="0.01" name="value" required placeholder="например, 2">
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
        <td><?php echo date('d.m.Y H:i', strtotime($e['created_at'])); ?></td>
        <td><?php echo h($e['type']); ?></td>
        <td><?php echo h($e['value']); ?> <?php echo h($allowed[$e['type']] ?? ''); ?></td>
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
