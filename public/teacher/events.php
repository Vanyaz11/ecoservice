<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();
if (!is_teacher()) { header('Location: ../index.php'); exit; }

$pdo = db();
$events = $pdo->query("SELECT id, title, starts_at, location, seats FROM events ORDER BY starts_at DESC")->fetchAll();
?>
<div class="card">
  <div class="card-header">
    <div>
      <h2>События</h2>
      <div class="muted">Управляйте мероприятиями: создавайте, редактируйте и удаляйте</div>
    </div>
    <a class="btn" href="<?php echo url('teacher/event_new.php'); ?>">+ Создать событие</a>
  </div>

  <?php if (!$events): ?>
    <div class="empty">
      <div class="empty-title">Событий пока нет</div>
      <div class="empty-text">Создайте первое событие, чтобы студенты могли записаться и следить за расписанием.</div>
      <a class="btn" href="<?php echo url('teacher/event_new.php'); ?>">+ Создать событие</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table table-nice">
        <thead>
          <tr>
            <th>Название</th>
            <th class="col-date">Начало</th>
            <th>Место</th>
            <th class="col-seats">Мест</th>
            <th class="col-actions"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($events as $e): ?>
            <tr>
              <td>
                <div class="title-cell"><?php echo h($e['title']); ?></div>
              </td>
              <td class="muted"><?php echo h($e['starts_at']); ?></td>
              <td><?php echo h($e['location']); ?></td>
              <td class="muted"><?php echo (int)$e['seats']; ?></td>
              <td>
                <div class="actions">
                  <a class="btn secondary sm" href="<?php echo url('teacher/event_edit.php?id='.(int)$e['id']); ?>">Редактировать</a>
                  <form method="post" action="<?php echo url('teacher/event_delete.php'); ?>" onsubmit="return confirm('Удалить событие?');" style="display:inline-block">
                    <input type="hidden" name="id" value="<?php echo (int)$e['id']; ?>">
                    <button class="btn danger sm" type="submit">Удалить</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
