<?php
$pageTitle = 'Виды действий';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_admin();
require __DIR__ . '/_nav.php';
$pdo = db();
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? ''); $unit = trim($_POST['unit'] ?? 'шт.'); $desc = trim($_POST['description'] ?? '');
  $active = isset($_POST['is_active']) ? 'true' : 'false';
  if (isset($_POST['delete']) && $id) {
    $pdo->prepare("DELETE FROM action_types WHERE id = ?")->execute([$id]);
  } elseif (mb_strlen($name) < 3) {
    $err = 'Название должно быть не короче 3 символов';
  } elseif ($id) {
    $pdo->prepare("UPDATE action_types SET name=?, unit=?, description=?, is_active=? WHERE id=?")->execute([$name, $unit, $desc, $active, $id]);
  } else {
    $pdo->prepare("INSERT INTO action_types (name, unit, description, is_active) VALUES (?,?,?,?) ON CONFLICT (name) DO NOTHING")->execute([$name, $unit, $desc, $active]);
  }
  if (!$err) { header('Location: ' . url('admin/action_types.php')); exit; }
}
$types = $pdo->query("SELECT t.*, (SELECT count(*) FROM eco_entries e WHERE e.type = t.name) AS used FROM action_types t ORDER BY t.id")->fetchAll();
?>
<div class="card">
  <h3>Справочник видов экологических действий</h3>
  <p class="small">Список отображается в форме эко-дневника. Неактивные виды скрываются из формы, но старые записи сохраняются.</p>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <table class="table">
    <thead><tr><th>Название</th><th>Единица</th><th>Пояснение</th><th>Активен</th><th>Записей</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($types as $t): ?>
      <tr>
        <form method="post">
        <td><input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>"><input class="input" name="name" value="<?php echo h($t['name']); ?>"></td>
        <td><input class="input" name="unit" value="<?php echo h($t['unit']); ?>" style="width:70px"></td>
        <td><input class="input" name="description" value="<?php echo h($t['description']); ?>"></td>
        <td style="text-align:center"><input type="checkbox" name="is_active" <?php echo $t['is_active'] ? 'checked' : ''; ?>></td>
        <td><?php echo (int)$t['used']; ?></td>
        <td style="white-space:nowrap"><button class="btn secondary" type="submit">Сохранить</button> <?php if (!(int)$t['used']): ?><button class="btn secondary" name="delete" type="submit">Удалить</button><?php endif; ?></td>
        </form>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="card">
  <h3>Добавить вид действия</h3>
  <form method="post" class="grid">
    <div><label class="label">Название</label><input class="input" name="name" required></div>
    <div><label class="label">Единица измерения</label><input class="input" name="unit" value="шт."></div>
    <div><label class="label">Пояснение</label><input class="input" name="description"></div>
    <div style="align-self:end"><label><input type="checkbox" name="is_active" checked> активен</label> <button class="btn" type="submit">Добавить</button></div>
  </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
