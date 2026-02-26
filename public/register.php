<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
$err = $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (!$name || !$email || !$password) {
    $err = 'Заполните все поля';
  } else {
    $err = register_user($name, $email, $password);
    if (!$err) $ok = 'Регистрация успешна. Теперь войдите.';
  }
}
?>
<div class="card">
  <h2>Регистрация</h2>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="alert success"><?php echo h($ok); ?></div><?php endif; ?>
  <form method="post">
    <label class="label">Имя</label>
    <input class="input" type="text" name="name" required>
    <label class="label">Email</label>
    <input class="input" type="email" name="email" required>
    <label class="label">Пароль</label>
    <input class="input" type="password" name="password" required>
    <p style="margin-top:12px"><button class="btn" type="submit">Зарегистрироваться</button></p>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
