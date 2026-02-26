<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php
session_start();
include __DIR__ . '/../includes/breadcrumbs.php';
include __DIR__ . '/../components/navbar.php';
echo render_breadcrumbs(['guides'=>'Руководства']);
?>
<main style="padding:16px;">
  <h1>Руководства</h1>
  <p>Здесь публикуются инструкции пользователя и администратора.</p>
  <ul>
    <li><a href="/docs/generate.php?type=docx">Скачать пример отчета (.docx)</a></li>
    <li><a href="/docs/generate.php?type=xlsx">Скачать пример отчета (.xlsx)</a></li>
    <li><a href="/docs/generate.php?type=rtf">Скачать пример отчета (.rtf)</a></li>
    <li><a href="/docs/generate.php?type=csv">Скачать пример отчета (.csv)</a></li>
  </ul>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
