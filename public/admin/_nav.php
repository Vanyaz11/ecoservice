<?php
// Вкладки панели администратора; подключается после header.php
$adminTabs = [
  'index.php' => 'Обзор', 'users.php' => 'Пользователи', 'feedback.php' => 'Обращения',
  'badges.php' => 'Бейджи', 'action_types.php' => 'Виды действий', 'events.php' => 'Мероприятия',
];
$cur = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="card">
  <h2>Панель администратора</h2>
  <nav class="nav" aria-label="Разделы администрирования" style="gap:6px">
    <?php foreach ($adminTabs as $f => $t): ?>
      <a class="btn <?php echo $f === $cur ? '' : 'secondary'; ?>" href="<?php echo url('admin/' . $f); ?>"><?php echo h($t); ?></a>
    <?php endforeach; ?>
  </nav>
</div>
