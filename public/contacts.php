<?php
$pageTitle = 'Контакты';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
$u = current_user();
$errors = []; $sent = false;
$form = ['name' => $u['name'] ?? '', 'email' => $u['email'] ?? '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($form as $k => $v) $form[$k] = trim($_POST[$k] ?? '');
  // Серверная валидация: все поля обязательны, e-mail проверяется фильтром, длина ограничена
  if (mb_strlen($form['name']) < 2 || mb_strlen($form['name']) > 100) $errors[] = 'Укажите имя (от 2 до 100 символов)';
  if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Укажите корректный e-mail';
  if (mb_strlen($form['message']) < 10) $errors[] = 'Сообщение должно содержать не менее 10 символов';
  if (mb_strlen($form['message']) > 4000) $errors[] = 'Сообщение слишком длинное (до 4000 символов)';
  // Простая защита от ботов: скрытое поле должно остаться пустым
  if (!empty($_POST['website'])) $errors[] = 'Ошибка отправки';
  if (!$errors) {
    $st = $pdo->prepare("INSERT INTO feedback (user_id, name, email, message) VALUES (?,?,?,?)");
    $st->execute([$u['id'] ?? null, $form['name'], $form['email'], $form['message']]);
    $_SESSION['flash_contact'] = true;
    header('Location: ' . url('contacts.php'));
    exit;
  }
}
if (!empty($_SESSION['flash_contact'])) { $sent = true; unset($_SESSION['flash_contact']); }
?>
<div class="card">
  <h2>Контакты</h2>
  <div class="grid">
    <div class="card">
      <h3>Научно-образовательный центр устойчивого развития</h3>
      <p>Московский университет имени С. Ю. Витте<br>115432, г. Москва, 2-й Кожуховский проезд, д. 12, стр. 1<br>Главный корпус, кабинет НОЦУР</p>
      <p class="small">Приём обращений: понедельник–пятница, 10:00–17:00</p>
    </div>
    <div class="card">
      <h3>По вопросам сервиса</h3>
      <p>Техническая поддержка и предложения по развитию — через форму ниже. Срок ответа — до трёх рабочих дней. Обращения поступают администратору сервиса и сотруднику НОЦУР.</p>
    </div>
  </div>
</div>
<div class="card">
  <h3>Форма обратной связи</h3>
  <?php if ($sent): ?><div class="alert success">Спасибо! Ваше обращение зарегистрировано, ответ придёт на указанный e-mail.</div><?php endif; ?>
  <?php foreach ($errors as $e): ?><div class="alert error"><?php echo h($e); ?></div><?php endforeach; ?>
  <form method="post" novalidate>
    <label class="label" for="fb-name">Имя *</label>
    <input class="input" id="fb-name" name="name" required maxlength="100" value="<?php echo h($form['name']); ?>">
    <label class="label" for="fb-email">E-mail *</label>
    <input class="input" id="fb-email" type="email" name="email" required maxlength="190" value="<?php echo h($form['email']); ?>">
    <label class="label" for="fb-msg">Сообщение *</label>
    <textarea class="input" id="fb-msg" name="message" rows="5" required minlength="10" maxlength="4000"><?php echo h($form['message']); ?></textarea>
    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
    <p><button class="btn" type="submit">Отправить</button></p>
  </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
