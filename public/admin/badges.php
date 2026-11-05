<?php
$pageTitle = 'Бейджи';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_admin();
require __DIR__ . '/_nav.php';
$pdo = db();
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $code = trim($_POST['code'] ?? ''); $name = trim($_POST['name'] ?? ''); $desc = trim($_POST['description'] ?? '');
  if (isset($_POST['delete']) && $id) {
    $pdo->prepare("DELETE FROM badges WHERE id = ?")->execute([$id]);
  } elseif (!preg_match('/^[a-z0-9_]{3,64}$/', $code) || $name === '') {
    $err = 'Код — латиница, цифры и подчёркивание (3–64 символа); название обязательно';
  } elseif ($id) {
    $pdo->prepare("UPDATE badges SET code=?, name=?, description=? WHERE id=?")->execute([$code, $name, $desc, $id]);
  } else {
    $pdo->prepare("INSERT INTO badges (code, name, description) VALUES (?,?,?) ON CONFLICT (code) DO UPDATE SET name = EXCLUDED.name, description = EXCLUDED.description")->execute([$code, $name, $desc]);
  }
  if (!$err) { header('Location: ' . url('admin/badges.php')); exit; }
}
$badges = $pdo->query("SELECT b.*, (SELECT count(*) FROM user_badges ub WHERE ub.badge_id=b.id) AS holders FROM badges b ORDER BY b.id")->fetchAll();
?>
<div class="card">
  <h3>Справочник бейджей</h3>
  <p class="small">Код бейджа используется в правилах начисления (includes/badges.php); не меняйте код уже выданных бейджей.</p>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <table class="table">
    <thead><tr><th>Код</th><th>Название</th><th>Условие</th><th>Получили</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($badges as $b): ?>
      <tr>
        <form method="post">
        <td><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>"><input class="input" name="code" value="<?php echo h($b['code']); ?>"></td>
        <td><input class="input" name="name" value="<?php echo h($b['name']); ?>"></td>
        <td><input class="input" name="description" value="<?php echo h($b['description']); ?>"></td>
        <td><?php echo (int)$b['holders']; ?></td>
        <td style="white-space:nowrap"><button class="btn secondary" type="submit">Сохранить</button> <button class="btn secondary" name="delete" type="submit" onclick="return confirm('Удалить бейдж?')">Удалить</button></td>
        </form>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="card">
  <h3>Добавить бейдж</h3>
  <form method="post" class="grid">
    <div><label class="label">Код</label><input class="input" name="code" placeholder="paper_collector" required></div>
    <div><label class="label">Название</label><input class="input" name="name" required></div>
    <div><label class="label">Условие получения</label><input class="input" name="description"></div>
    <div style="align-self:end"><button class="btn" type="submit">Добавить</button></div>
  </form>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
