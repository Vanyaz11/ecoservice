<?php
$pageTitle = 'Достижения и правила';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
$badges = db()->query("SELECT b.code, b.name, b.description, (SELECT count(*) FROM user_badges ub WHERE ub.badge_id = b.id) AS holders FROM badges b ORDER BY b.id")->fetchAll();
?>
<div class="card">
  <h2>Система достижений</h2>
  <p>За регулярную экологическую активность сервис автоматически начисляет бейджи. Условия прозрачны и одинаковы для всех; каждый бейдж выдаётся один раз. Проверка условий происходит сразу после записи в эко-дневник, записи на курс или мероприятие.</p>
</div>
<div class="card">
  <h3>Перечень бейджей</h3>
  <table class="table">
    <thead><tr><th>Бейдж</th><th>Условие получения</th><th>Получили</th></tr></thead>
    <tbody>
    <?php foreach ($badges as $b): ?>
      <tr><td><b><?php echo h($b['name']); ?></b></td><td><?php echo h($b['description']); ?></td><td><?php echo (int)$b['holders']; ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="card">
  <h3>Правила честного учёта</h3>
  <ul>
    <li>Записывайте только реально выполненные действия: цель дневника — изменить привычки, а не набрать баллы.</li>
    <li>Одно действие — одна запись. Регулярность важнее разовых больших значений.</li>
    <li>Сотрудники НОЦУР видят сводную статистику и могут уточнить сомнительные записи.</li>
    <li>Достижения учитываются при подведении итогов студенческих инициатив и в портфолио обучающегося.</li>
  </ul>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
