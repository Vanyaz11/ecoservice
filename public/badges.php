<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/badges.php';
require_login();
$u = current_user();

if (($u['role'] ?? '') !== 'student') {
  http_response_code(403);
  echo '<div class="card"><h2>Бейджи</h2><p>Раздел доступен только студентам.</p></div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}
$items = user_badges($u['id']);
?>
<div class="card">
  <h2>Мои бейджи</h2>
  <?php if (!$items): ?>
    <p class="muted">Пока нет наград. Ведите эко‑дневник, участвуйте в событиях и проходите обучение!</p>
  <?php else: ?>
  <table class="table">
    <tr><th>Бейдж</th><th>Описание</th><th>Дата</th></tr>
    <?php foreach ($items as $b): ?>
      <tr>
        <td><strong><?php echo h($b['name']); ?></strong><br><span class="muted"><?php echo h($b['code']); ?></span></td>
        <td><?php echo h($b['description']); ?></td>
        <td><?php echo h($b['awarded_at']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
