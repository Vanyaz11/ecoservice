<?php
$pageTitle = 'Инициативы';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
// Сводная статистика активности — показывает гостю, что сервис живой
$stat = $pdo->query("SELECT
  (SELECT count(*) FROM users WHERE role = 'student') AS students,
  (SELECT count(*) FROM eco_entries) AS entries,
  (SELECT coalesce(sum(value),0) FROM eco_entries WHERE type = 'Сортировка отходов') AS sorted_kg,
  (SELECT count(*) FROM event_registrations) AS regs,
  (SELECT count(*) FROM user_badges) AS badges")->fetch();
$top = $pdo->query("SELECT type, count(*) AS cnt, sum(value) AS total FROM eco_entries GROUP BY type ORDER BY cnt DESC LIMIT 5")->fetchAll();
?>
<div class="card">
  <h2>Студенческие экологические инициативы</h2>
  <p>НОЦУР поддерживает инициативы обучающихся: от акций по сбору вторсырья до исследовательских проектов. Ниже — действующие направления и то, как к ним присоединиться.</p>
</div>
<div class="grid">
  <div class="card"><h3>«Сдай батарейку»</h3><p>Постоянный сбор батареек и мелкой электроники в холле главного корпуса. Каждые 10 батареек — запись в эко-дневнике и шаг к бейджу «Сортировщик-10».</p></div>
  <div class="card"><h3>«Бумажный бум»</h3><p>Соревнование учебных групп по сбору макулатуры дважды в семестр. Итоги публикуются в новостях, группа-победитель высаживает дерево на аллее выпускников.</p></div>
  <div class="card"><h3>«Кампус без пластика»</h3><p>Отказ от одноразовой посуды в буфетах, многоразовые бутылки и фонтанчики. Три отказа от пластика — бейдж «Меньше пластика».</p></div>
  <div class="card"><h3>Субботники и посадки</h3><p>Весенние и осенние субботники на территории университета. Регистрация через раздел «События», участие засчитывается автоматически.</p></div>
  <div class="card"><h3>Эко-лекторий</h3><p>Открытые лекции преподавателей и приглашённых экспертов по энергосбережению, обращению с отходами, климату. Записи — в библиотеке.</p></div>
  <div class="card"><h3>Исследовательские проекты</h3><p>Студенческие НИР по экологии и устойчивому развитию под руководством сотрудников НОЦУР с выходом на конференции университета.</p></div>
</div>
<div class="card">
  <h3>Результаты сообщества</h3>
  <div class="grid">
    <div class="card"><div class="small">Участников</div><h2 style="margin:4px 0"><?php echo (int)$stat['students']; ?></h2></div>
    <div class="card"><div class="small">Записей в эко-дневниках</div><h2 style="margin:4px 0"><?php echo (int)$stat['entries']; ?></h2></div>
    <div class="card"><div class="small">Отсортировано отходов, кг</div><h2 style="margin:4px 0"><?php echo number_format((float)$stat['sorted_kg'], 1, ',', ' '); ?></h2></div>
    <div class="card"><div class="small">Регистраций на мероприятия</div><h2 style="margin:4px 0"><?php echo (int)$stat['regs']; ?></h2></div>
    <div class="card"><div class="small">Выдано достижений</div><h2 style="margin:4px 0"><?php echo (int)$stat['badges']; ?></h2></div>
  </div>
  <?php if ($top): ?>
  <h4>Самые частые экологические действия</h4>
  <table class="table"><thead><tr><th>Вид действия</th><th>Записей</th><th>Суммарно</th></tr></thead><tbody>
  <?php foreach ($top as $t): ?><tr><td><?php echo h($t['type']); ?></td><td><?php echo (int)$t['cnt']; ?></td><td><?php echo number_format((float)$t['total'], 1, ',', ' '); ?></td></tr><?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div>
<div class="card">
  <h3>Предложить инициативу</h3>
  <p>Опишите идею через <a href="<?php echo url('contacts.php'); ?>">форму обратной связи</a>: цель, участники, что нужно от университета. Сотрудник НОЦУР ответит в течение трёх рабочих дней.</p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
