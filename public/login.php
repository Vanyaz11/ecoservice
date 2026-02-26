<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (!$email || !$password) {
    $err = 'Введите email и пароль';
  } else {
    $err = login_user($email, $password);
    if (!$err) { header('Location: dashboard.php'); exit(); }
  }
}
?>
<div class="card">
  <h2>Вход</h2>
  <?php if ($err): ?><div class="alert error"><?php echo h($err); ?></div><?php endif; ?>
  <form method="post">
    <label class="label">Email</label>
    <input class="input" type="email" name="email" required>
    <label class="label">Пароль</label>
    <input class="input" type="password" name="password" required>
    <p style="margin-top:12px"><button class="btn" type="submit">Войти</button></p>
  </form>
  <span class="badge">teacher@mail.ru / 12345</span><br>
  <span class="badge">student@mail.ru / 12345</span>
  <p>Нет аккаунта? <a href="<?php echo url('register.php'); ?>">Регистрация</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
