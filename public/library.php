<?php
$pageTitle = 'Библиотека';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
$pdo = db();
// Открытая часть библиотеки: ссылки и видео. Загруженные файлы доступны после входа (раздел «Материалы»).
$items = $pdo->query("SELECT m.id, m.title, m.description, m.type, m.url, c.title AS course FROM materials m LEFT JOIN courses c ON c.id = m.course_id WHERE m.type IN ('link','video') ORDER BY m.created_at DESC")->fetchAll();
$files = (int)$pdo->query("SELECT count(*) FROM materials WHERE type = 'file'")->fetchColumn();
?>
<div class="card">
  <h2>Библиотека экологических материалов</h2>
  <p>Подборка открытых источников по экологии и устойчивому развитию, которые используются в курсах сервиса. Ссылки проверены преподавателями НОЦУР. Загруженные файлы (<?php echo $files; ?>) доступны авторизованным пользователям в разделе «Материалы».</p>
</div>
<div class="card">
  <h3>Материалы курсов</h3>
  <table class="table">
    <thead><tr><th>Название</th><th>Описание</th><th>Курс</th><th>Тип</th></tr></thead>
    <tbody>
    <?php foreach ($items as $m): ?>
      <tr>
        <td><a href="<?php echo h($m['url']); ?>" target="_blank" rel="noopener"><?php echo h($m['title']); ?></a></td>
        <td><?php echo h($m['description']); ?></td>
        <td><?php echo h($m['course'] ?? '—'); ?></td>
        <td><?php echo $m['type'] === 'video' ? 'видео' : 'ссылка'; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="card">
  <h3>Рекомендуемые открытые ресурсы</h3>
  <ul>
    <li><a href="https://www.un.org/sustainabledevelopment/ru/" target="_blank" rel="noopener">Цели в области устойчивого развития ООН</a> — официальное описание 17 целей и показателей.</li>
    <li><a href="https://www.mnr.gov.ru/" target="_blank" rel="noopener">Минприроды России</a> — государственные доклады о состоянии окружающей среды.</li>
    <li><a href="https://rpn.gov.ru/" target="_blank" rel="noopener">Росприроднадзор</a> — нормативные документы по обращению с отходами.</li>
    <li><a href="https://ecologyofrussia.ru/" target="_blank" rel="noopener">Национальный проект «Экологическое благополучие»</a> — федеральные программы и показатели.</li>
    <li><a href="https://www.consultant.ru/document/cons_doc_LAW_34823/" target="_blank" rel="noopener">Федеральный закон «Об охране окружающей среды»</a> — базовый закон отрасли.</li>
  </ul>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
