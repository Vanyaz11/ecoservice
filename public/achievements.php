<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/badges.php';

require_login();

$u = current_user();

// Achievements are a student-only feature in this project.
if (($u['role'] ?? '') !== 'student') {
  http_response_code(403);
  echo '<div class="card"><h2>Достижения</h2><p>Раздел доступен только студентам.</p></div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

$pdo = db();

/**
 * Seed a minimal set of achievements so the catalog is not empty.
 * (If you already filled the badges table — this code will just skip existing ones.)
 */
$catalog = [
  ['first_entry',   'Первый шаг',         'Первая запись в эко‑дневнике'],
  ['five_entries',  'Пять поступков',     'Сделано 5 записей в эко‑дневник'],
  ['ten_entries',   'Эко‑активист',       'Сделано 10 записей в эко‑дневник'],
  ['plastic_free',  'Меньше пластика',    'Три и более действий по отказу от одноразового пластика'],
  ['first_event',   'Вместе на природе',  'Участие хотя бы в одном эко‑событии'],
  ['first_course',  'Начал обучение',     'Запись хотя бы на один курс'],
  ['three_courses', 'Любознательный',     'Запись на 3 курса'],
];
foreach ($catalog as $b) {
  ensure_badge($b[0], $b[1], $b[2]);
}

// Fetch all achievements + user status
$st = $pdo->prepare(
  "SELECT b.id, b.code, b.name, b.description,
          ub.awarded_at
   FROM badges b
   LEFT JOIN user_badges ub
     ON ub.badge_id = b.id AND ub.user_id = ?
   ORDER BY (ub.awarded_at IS NULL) ASC, ub.awarded_at DESC, b.id ASC"
);
$st->execute([$u['id']]);
$items = $st->fetchAll();

// Small progress metrics
$st2 = $pdo->prepare("SELECT COUNT(*) FROM eco_entries WHERE user_id=?");
$st2->execute([$u['id']]);
$entries = (int)$st2->fetchColumn();

$st3 = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id=?");
$st3->execute([$u['id']]);
$events = (int)$st3->fetchColumn();

$st4 = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id=?");
$st4->execute([$u['id']]);
$courses = (int)$st4->fetchColumn();

$earned = 0;
foreach ($items as $it) if ($it['awarded_at']) $earned++;
$total = count($items);
?>
<div class="card">
  <h2>Достижения</h2>
  <p class="muted">Достижения открываются за активность: эко‑дневник, участие в событиях и обучение.</p>

  <div class="grid">
    <div class="card">
      <h3>Прогресс</h3>
      <p class="badge">Открыто: <?php echo (int)$earned; ?> / <?php echo (int)$total; ?></p>
      <p class="muted">Записей в дневнике: <?php echo (int)$entries; ?><br>
      Участий в событиях: <?php echo (int)$events; ?><br>
      Курсов: <?php echo (int)$courses; ?></p>
      <p style="margin-top:10px">
        <a class="btn secondary" href="<?php echo url('eco_diary.php'); ?>">В эко‑дневник</a>
        <a class="btn secondary" href="<?php echo url('events.php'); ?>">К событиям</a>
        <a class="btn secondary" href="<?php echo url('courses.php'); ?>">К курсам</a>
      </p>
    </div>

    <div class="card">
      <h3>Как получать достижения</h3>
      <ul>
        <li>Добавляйте записи в эко‑дневник (минимум 1 / 5 / 10).</li>
        <li>Регистрируйтесь на мероприятия.</li>
        <li>Записывайтесь на курсы.</li>
      </ul>
      <p class="muted">Подсказка: достижения выдаются автоматически после действия.</p>
    </div>
  </div>
</div>

<div class="card">
  <h3>Каталог достижений</h3>

  <?php if (!$items): ?>
    <p class="muted">Пока нет достижений в каталоге. Добавьте записи в таблицу <code>badges</code> или используйте автозаполнение на этой странице.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($items as $b): ?>
        <?php $isEarned = !empty($b['awarded_at']); ?>
        <div class="card" style="opacity: <?php echo $isEarned ? '1' : '0.65'; ?>;">
          <h4><?php echo h($b['name']); ?></h4>
          <p><?php echo h($b['description']); ?></p>
          <?php if ($isEarned): ?>
            <span class="badge">Открыто: <?php echo h($b['awarded_at']); ?></span>
          <?php else: ?>
            <span class="badge">Не открыто</span>
          <?php endif; ?>
          <div class="muted" style="margin-top:8px">Код: <?php echo h($b['code']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
