<?php
$pageTitle = 'Новости';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
// Лента новостей формируется из ближайших мероприятий и новых курсов — без ручного наполнения
$events = $pdo->query("SELECT id, title, description, location, starts_at, created_at FROM events ORDER BY created_at DESC LIMIT 6")->fetchAll();
$courses = $pdo->query("SELECT c.id, c.title, c.short_desc, c.created_at, u.name AS author FROM courses c LEFT JOIN users u ON u.id = c.author_id ORDER BY c.created_at DESC LIMIT 4")->fetchAll();
?>
<div class="card">
  <h2>Новости сервиса</h2>
  <p>Здесь собраны последние события экологической жизни университета: анонсы мероприятий НОЦУР и новые учебные курсы. Записаться на мероприятие или курс можно после входа в личный кабинет.</p>
</div>
<div class="card">
  <h3>Анонсы мероприятий</h3>
  <?php if (!$events): ?><p class="small">Мероприятий пока не запланировано.</p><?php endif; ?>
  <div class="grid">
  <?php foreach ($events as $e): ?>
    <div class="card">
      <span class="badge"><?php echo date('d.m.Y', strtotime($e['starts_at'])); ?></span>
      <h4 style="margin:8px 0 4px"><?php echo h($e['title']); ?></h4>
      <p><?php echo h($e['description']); ?></p>
      <p class="small">Место: <?php echo h($e['location']); ?> · Начало: <?php echo date('d.m.Y H:i', strtotime($e['starts_at'])); ?></p>
      <a class="btn secondary" href="<?php echo url('events.php'); ?>">Подробнее</a>
    </div>
  <?php endforeach; ?>
  </div>
</div>
<div class="card">
  <h3>Новые курсы</h3>
  <ul>
  <?php foreach ($courses as $c): ?>
    <li><a href="<?php echo url('courses.php'); ?>"><?php echo h($c['title']); ?></a> — <?php echo h($c['short_desc']); ?>
      <span class="small">(<?php echo h($c['author'] ?? 'НОЦУР'); ?>, <?php echo date('d.m.Y', strtotime($c['created_at'])); ?>)</span></li>
  <?php endforeach; ?>
  </ul>
</div>
<div class="card">
  <h3>Как попасть в новости</h3>
  <p>Преподаватели и сотрудники НОЦУР публикуют мероприятия и курсы в своих кабинетах — они автоматически появляются в этой ленте. Студенческие инициативы можно предложить через <a href="<?php echo url('contacts.php'); ?>">форму обратной связи</a>.</p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
