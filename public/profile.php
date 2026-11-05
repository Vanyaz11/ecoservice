<?php
$pageTitle = 'Профиль';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_login();
$pdo = db();
$u = current_user();
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['save_name'])) {
    $name = trim($_POST['name'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $msg = ['error', 'Имя должно содержать от 2 до 100 символов'];
    else {
      $pdo->prepare("UPDATE users SET name = ? WHERE id = ?")->execute([$name, $u['id']]);
      $_SESSION['user']['name'] = $name; $u = current_user();
      $msg = ['success', 'Имя сохранено'];
    }
  } elseif (isset($_POST['change_password'])) {
    $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?"); $st->execute([$u['id']]);
    $hash = $st->fetchColumn();
    $old = $_POST['old'] ?? ''; $new = $_POST['new'] ?? ''; $rep = $_POST['rep'] ?? '';
    if (!password_verify($old, $hash)) $msg = ['error', 'Текущий пароль указан неверно'];
    elseif (strlen($new) < 6) $msg = ['error', 'Новый пароль должен быть не короче 6 символов'];
    elseif ($new !== $rep) $msg = ['error', 'Пароли не совпадают'];
    else {
      $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
      $msg = ['success', 'Пароль изменён'];
    }
  }
}
$st = $pdo->prepare("SELECT u.created_at,
  (SELECT count(*) FROM eco_entries e WHERE e.user_id = u.id) AS entries,
  (SELECT count(*) FROM enrollments en WHERE en.user_id = u.id) AS courses,
  (SELECT count(*) FROM event_registrations r WHERE r.user_id = u.id) AS events,
  (SELECT count(*) FROM user_badges b WHERE b.user_id = u.id) AS badges
  FROM users u WHERE u.id = ?");
$st->execute([$u['id']]); $info = $st->fetch();
$roles = ['student' => 'студент', 'teacher' => 'преподаватель', 'admin' => 'администратор'];
?>
<div class="card">
  <h2>Профиль</h2>
  <?php if ($msg): ?><div class="alert <?php echo $msg[0]; ?>"><?php echo h($msg[1]); ?></div><?php endif; ?>
  <div class="grid">
    <div class="card">
      <h3>Учётная запись</h3>
      <p><b>E-mail:</b> <?php echo h($u['email']); ?><br><b>Роль:</b> <?php echo $roles[$u['role']] ?? h($u['role']); ?><br><b>Зарегистрирован:</b> <?php echo date('d.m.Y', strtotime($info['created_at'])); ?></p>
      <form method="post">
        <label class="label" for="p-name">Имя</label>
        <input class="input" id="p-name" name="name" value="<?php echo h($u['name']); ?>" required>
        <p><button class="btn secondary" name="save_name" type="submit">Сохранить имя</button></p>
      </form>
    </div>
    <div class="card">
      <h3>Моя активность</h3>
      <p>Записей в эко-дневнике: <b><?php echo (int)$info['entries']; ?></b><br>Курсов: <b><?php echo (int)$info['courses']; ?></b><br>Мероприятий: <b><?php echo (int)$info['events']; ?></b><br>Бейджей: <b><?php echo (int)$info['badges']; ?></b></p>
      <p><a class="btn secondary" href="<?php echo url('reports.php'); ?>">Подробный отчёт</a></p>
    </div>
    <div class="card">
      <h3>Смена пароля</h3>
      <form method="post">
        <label class="label">Текущий пароль</label><input class="input" type="password" name="old" required>
        <label class="label">Новый пароль</label><input class="input" type="password" name="new" minlength="6" required>
        <label class="label">Повторите новый пароль</label><input class="input" type="password" name="rep" minlength="6" required>
        <p><button class="btn" name="change_password" type="submit">Изменить пароль</button></p>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
