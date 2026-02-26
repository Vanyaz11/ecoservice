<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
$u = current_user();
$role = $u['role'] ?? 'student';
$pdo = db();

function table_exists(PDO $pdo, string $table): bool {
  try {
    $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
    return true;
  } catch (Exception $e) {
    return false;
  }
}

function column_exists(PDO $pdo, string $table, string $column): bool {
  try {
    $st = $pdo->prepare("SELECT COUNT(*) AS c
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = ?
                          AND COLUMN_NAME = ?");
    $st->execute([$table, $column]);
    return (int)($st->fetch()['c'] ?? 0) > 0;
  } catch (Exception $e) {
    return false;
  }
}

$hasBadges = table_exists($pdo, 'badges') && table_exists($pdo, 'user_badges');
$hasMaterials = table_exists($pdo, 'materials');
$hasEco = table_exists($pdo, 'eco_entries');
$hasEnroll = table_exists($pdo, 'enrollments');
$hasEventReg = table_exists($pdo, 'event_registrations');
$hasUsers = table_exists($pdo, 'users');
$hasCourses = table_exists($pdo, 'courses');

?>

<div class="card">
  <h2>Отчёты</h2>
  <p class="muted">Сводные данные формируются автоматически на основе активности пользователя в системе.</p>
</div>

<?php if ($role === 'student'): ?>
  <?php
  // --- Student report ---
  $uid = (int)$u['id'];

  $ecoTotal = 0;
  $ecoByType = [];
  if ($hasEco) {
    $st = $pdo->prepare("SELECT COUNT(*) AS c FROM eco_entries WHERE user_id = ?");
    $st->execute([$uid]);
    $ecoTotal = (int)($st->fetch()['c'] ?? 0);

    $st = $pdo->prepare("SELECT type, COUNT(*) AS cnt, COALESCE(SUM(value),0) AS total
                         FROM eco_entries
                         WHERE user_id = ?
                         GROUP BY type
                         ORDER BY cnt DESC, type ASC");
    $st->execute([$uid]);
    $ecoByType = $st->fetchAll();
  }

  $eventsTotal = 0;
  if ($hasEventReg) {
    $st = $pdo->prepare("SELECT COUNT(*) AS c FROM event_registrations WHERE user_id = ?");
    $st->execute([$uid]);
    $eventsTotal = (int)($st->fetch()['c'] ?? 0);
  }

  $coursesTotal = 0;
  if ($hasEnroll) {
    $st = $pdo->prepare("SELECT COUNT(*) AS c FROM enrollments WHERE user_id = ?");
    $st->execute([$uid]);
    $coursesTotal = (int)($st->fetch()['c'] ?? 0);
  }

  $badgesTotal = null;
  if ($hasBadges) {
    $st = $pdo->prepare("SELECT COUNT(*) AS c FROM user_badges WHERE user_id = ?");
    $st->execute([$uid]);
    $badgesTotal = (int)($st->fetch()['c'] ?? 0);
  }
  ?>

  <div class="card">
    <h3>Отчёт студента</h3>
    <div class="grid">
      <div class="card"><h4>Курсы</h4><p><b><?php echo (int)$coursesTotal; ?></b> записей на курсы</p><p><a class="btn" href="<?php echo url('courses.php'); ?>">Открыть курсы</a></p></div>
      <div class="card"><h4>Эко‑дневник</h4><p><b><?php echo (int)$ecoTotal; ?></b> записей</p><p><a class="btn" href="<?php echo url('eco_diary.php'); ?>">Открыть дневник</a></p></div>
      <div class="card"><h4>События</h4><p><b><?php echo (int)$eventsTotal; ?></b> регистраций</p><p><a class="btn" href="<?php echo url('events.php'); ?>">Открыть события</a></p></div>
      <div class="card"><h4>Достижения</h4><p><b><?php echo $badgesTotal === null ? '—' : (int)$badgesTotal; ?></b> получено</p><p><a class="btn" href="<?php echo url('achievements.php'); ?>">Открыть</a></p></div>
    </div>

    <h4 style="margin-top:16px;">Статистика эко‑дневника</h4>
    <?php if (!$hasEco): ?>
      <p class="muted">Таблица <code>eco_entries</code> не найдена.</p>
    <?php elseif (!$ecoByType): ?>
      <p class="muted">Пока нет записей. Добавьте действие в эко‑дневник — и статистика появится.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Тип действия</th>
            <th>Количество</th>
            <th>Суммарное значение</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ecoByType as $row): ?>
            <tr>
              <td><?php echo h($row['type']); ?></td>
              <td><?php echo (int)$row['cnt']; ?></td>
              <td><?php echo number_format((float)$row['total'], 2, '.', ''); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php else: ?>

  <?php
  // --- Teacher/Admin report ---
  $uid = (int)$u['id'];
  $isAdmin = ($role === 'admin');

  // Courses: support both schemas (with or without author_id)
  $hasAuthor = $hasCourses && column_exists($pdo, 'courses', 'author_id');

  $courseRows = [];
  $coursesCount = 0;
  $enrollmentsTotal = 0;

  if ($hasCourses) {
    if ($hasAuthor) {
      $st = $pdo->prepare(
        "SELECT c.id, c.title, COUNT(e.user_id) AS enrolled
         FROM courses c
         LEFT JOIN enrollments e ON e.course_id = c.id
         WHERE c.author_id = ?
         GROUP BY c.id
         ORDER BY c.id DESC"
      );
      $st->execute([$uid]);
    } else {
      // If no author_id, show all courses (common for old DB dumps)
      $st = $pdo->query(
        "SELECT c.id, c.title, COUNT(e.user_id) AS enrolled
         FROM courses c
         LEFT JOIN enrollments e ON e.course_id = c.id
         GROUP BY c.id
         ORDER BY c.id DESC"
      );
    }
    $courseRows = $st->fetchAll();
    $coursesCount = count($courseRows);
    foreach ($courseRows as $r) $enrollmentsTotal += (int)($r['enrolled'] ?? 0);
  }

  $materialsCount = null;
  if ($hasMaterials) {
    $st = $pdo->prepare("SELECT COUNT(*) AS c FROM materials WHERE uploaded_by = ?");
    $st->execute([$uid]);
    $materialsCount = (int)($st->fetch()['c'] ?? 0);
  }

  $eventRegsTotal = null;
  if ($hasEventReg) {
    $st = $pdo->query("SELECT COUNT(*) AS c FROM event_registrations");
    $eventRegsTotal = (int)($st->fetch()['c'] ?? 0);
  }

  // Admin-only: global summary
  $usersByRole = [];
  $topStudents = [];
  if ($isAdmin && $hasUsers) {
    $st = $pdo->query("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role ORDER BY role");
    $usersByRole = $st->fetchAll();

    if ($hasEco) {
      $st = $pdo->query(
        "SELECT u.id, u.name, u.email, COUNT(e.id) AS entries
         FROM users u
         LEFT JOIN eco_entries e ON e.user_id = u.id
         WHERE u.role = 'student'
         GROUP BY u.id
         ORDER BY entries DESC, u.id ASC
         LIMIT 10"
      );
      $topStudents = $st->fetchAll();
    }
  }
  ?>

  <div class="card">
    <h3><?php echo $isAdmin ? 'Отчёт администратора' : 'Отчёт преподавателя'; ?></h3>

    <div class="grid">
      <div class="card"><h4>Курсы</h4><p><b><?php echo (int)$coursesCount; ?></b> <?php echo $hasAuthor ? 'ваших' : 'в системе'; ?> курсов</p><p class="muted">Записей студентов: <b><?php echo (int)$enrollmentsTotal; ?></b></p><p><a class="btn" href="<?php echo url('teacher/dashboard.php'); ?>">Открыть</a></p></div>
      <div class="card"><h4>Материалы</h4><p><b><?php echo $materialsCount === null ? '—' : (int)$materialsCount; ?></b> загружено вами</p><p><a class="btn" href="<?php echo url('materials.php'); ?>">Открыть</a></p></div>
      <div class="card"><h4>События</h4><p><b><?php echo $eventRegsTotal === null ? '—' : (int)$eventRegsTotal; ?></b> регистраций (всего)</p><p><a class="btn" href="<?php echo url('teacher/events.php'); ?>">Управление</a></p></div>
      <div class="card"><h4>Студенты</h4><p>Список студентов и подписок на курсы</p><p><a class="btn" href="<?php echo url('teacher/students.php'); ?>">Открыть</a></p></div>
    </div>

    <h4 style="margin-top:16px;">Курсы и подписки</h4>
    <?php if (!$hasCourses): ?>
      <p class="muted">Таблица <code>courses</code> не найдена.</p>
    <?php elseif (!$courseRows): ?>
      <p class="muted"><?php echo $hasAuthor ? 'У вас пока нет созданных курсов.' : 'Пока нет курсов.'; ?></p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Курс</th>
            <th>Подписок</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courseRows as $r): ?>
            <tr>
              <td><a href="<?php echo url('learning_view.php?id='.(int)$r['id']); ?>"><?php echo h($r['title']); ?></a></td>
              <td><?php echo (int)$r['enrolled']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <?php if ($isAdmin): ?>
    <div class="card">
      <h4>Сводка по системе</h4>

      <?php if ($usersByRole): ?>
        <table class="table">
          <thead><tr><th>Роль</th><th>Количество пользователей</th></tr></thead>
          <tbody>
            <?php foreach ($usersByRole as $r): ?>
              <tr><td><?php echo h($r['role']); ?></td><td><?php echo (int)$r['cnt']; ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="muted">Нет данных по пользователям.</p>
      <?php endif; ?>

      <h4 style="margin-top:16px;">Топ студентов по эко‑активности</h4>
      <?php if (!$hasEco): ?>
        <p class="muted">Таблица <code>eco_entries</code> не найдена.</p>
      <?php elseif (!$topStudents): ?>
        <p class="muted">Пока нет данных для рейтинга.</p>
      <?php else: ?>
        <table class="table">
          <thead><tr><th>Студент</th><th>Email</th><th>Записей в эко‑дневнике</th></tr></thead>
          <tbody>
            <?php foreach ($topStudents as $s): ?>
              <tr>
                <td><?php echo h($s['name']); ?></td>
                <td><?php echo h($s['email']); ?></td>
                <td><?php echo (int)$s['entries']; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
