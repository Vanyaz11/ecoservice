<?php
$pageTitle = 'Пользователи';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_admin();
require __DIR__ . '/_nav.php';
$pdo = db();
$me = (int)current_user()['id'];
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  if (isset($_POST['set_role']) && in_array($_POST['role'] ?? '', ['student','teacher','admin'], true)) {
    if ($id === $me && $_POST['role'] !== 'admin') {
      $msg = ['error', 'Нельзя снять роль администратора с самого себя'];
    } else {
      $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$_POST['role'], $id]);
      $msg = ['success', 'Роль обновлена, вступит в силу при следующем входе пользователя'];
    }
  } elseif (isset($_POST['reset_password'])) {
    // Сброс пароля на временный; пользователю сообщают его лично
    $tmp = bin2hex(random_bytes(4));
    $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([password_hash($tmp, PASSWORD_DEFAULT), $id]);
    $msg = ['success', "Временный пароль пользователя #$id: $tmp"];
  } elseif (isset($_POST['delete']) && $id !== $me) {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    $msg = ['success', 'Пользователь и связанные с ним данные удалены'];
  }
}
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare("SELECT u.*, (SELECT count(*) FROM eco_entries e WHERE e.user_id=u.id) AS entries,
  (SELECT count(*) FROM event_registrations r WHERE r.user_id=u.id) AS regs,
  (SELECT count(*) FROM user_badges b WHERE b.user_id=u.id) AS badges
  FROM users u WHERE (? = '' OR u.name ILIKE ? OR u.email ILIKE ?) ORDER BY u.id");
$st->execute([$q, "%$q%", "%$q%"]);
$users = $st->fetchAll();
?>
<div class="card">
  <h3>Пользователи и роли</h3>
  <?php if ($msg): ?><div class="alert <?php echo $msg[0]; ?>"><?php echo h($msg[1]); ?></div><?php endif; ?>
  <form method="get" style="display:flex;gap:8px;margin-bottom:12px">
    <input class="input" name="q" placeholder="Поиск по имени или e-mail" value="<?php echo h($q); ?>">
    <button class="btn secondary" type="submit">Найти</button>
  </form>
  <div style="overflow-x:auto">
  <table class="table">
    <thead><tr><th>ID</th><th>Имя</th><th>E-mail</th><th>Роль</th><th>Записей</th><th>Мероприятий</th><th>Бейджей</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?php echo (int)$u['id']; ?></td><td><?php echo h($u['name']); ?></td><td><?php echo h($u['email']); ?></td>
        <td>
          <form method="post" style="display:flex;gap:4px">
            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
            <select class="input" name="role" style="width:auto">
              <?php foreach (['student'=>'студент','teacher'=>'преподаватель','admin'=>'администратор'] as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo $u['role']===$k?'selected':''; ?>><?php echo $v; ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn secondary" name="set_role" type="submit">Сохранить</button>
          </form>
        </td>
        <td><?php echo (int)$u['entries']; ?></td><td><?php echo (int)$u['regs']; ?></td><td><?php echo (int)$u['badges']; ?></td>
        <td>
          <form method="post" style="display:inline"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"><button class="btn secondary" name="reset_password" type="submit">Сбросить пароль</button></form>
          <?php if ((int)$u['id'] !== $me): ?>
          <form method="post" style="display:inline" onsubmit="return confirm('Удалить пользователя и все его данные?')"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"><button class="btn secondary" name="delete" type="submit">Удалить</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
