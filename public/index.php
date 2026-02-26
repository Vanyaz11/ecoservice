<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="card">
  <h1>Экологическая культура обучающихся</h1>
  <p>Веб‑сервис для обучения и развития экологически ответственного поведения студентов. Пройдите курсы, ведите эко‑дневник и участвуйте в событиях.</p>
  <p>
    <a class="btn" href="<?php echo url('learning.php'); ?>">Начать обучение</a>
    <a class="btn secondary" href="<?php echo url('eco_diary.php'); ?>">Открыть эко‑дневник</a>
  </p>
</div>
<div class="grid">
  <div class="card"><h3>Обучение</h3><p>Курсы и тесты по экологии и устойчивому развитию.</p></div>
  <div class="card"><h3>Эко‑дневник</h3><p>Фиксируйте эко‑практики и набирайте баллы.</p></div>
  <div class="card"><h3>События</h3><p>Субботники, лекции, конкурсы. Регистрируйтесь онлайн.</p></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
