<?php
require_once __DIR__ . '/helpers.php';

/**
 * Хлебные крошки.
 *
 * Исправления:
 * - корректно учитываем base_url (если сайт развёрнут в подпапке, например /eco_service/public)
 * - не показываем технические сегменты вроде /public
 * - для страниц вида /folder/index.php показываем только «folder» (без лишней «Главная» в конце)
 * - все подписи только на русском (за счёт расширенной карты маршрутов)
 */

// Берём base_url из конфигурации (если есть)
$cfg_file = __DIR__ . '/config.php';
if (!file_exists($cfg_file)) $cfg_file = __DIR__ . '/config.sample.php';
$cfg = require $cfg_file;
$baseUrl = rtrim((string)($cfg['app']['base_url'] ?? ''), '/');

// Карта маршрутов (файлы/папки -> русские названия)
$map = [
  // root
  '' => 'Главная',
  'index.php' => 'Главная',

  // основные страницы
  'about.php' => 'О проекте',
  'learning.php' => 'Обучение',
  'learning_view.php' => 'Тема',
  'courses.php' => 'Курсы',
  'eco_diary.php' => 'Эко‑дневник',
  'events.php' => 'События',
  'export_events.php' => 'Экспорт событий',
  'materials.php' => 'Материалы',
  'material_download.php' => 'Скачивание материала',
  'achievements.php' => 'Достижения',
  'reports.php' => 'Отчёты',
  'contacts.php' => 'Контакты',
  'library.php' => 'Библиотека',
  'news.php' => 'Новости',
  'eco_map.php' => 'Эко‑карта',
  'initiatives.php' => 'Инициативы',

  // инфо‑страницы (если используются)
  'info1.php' => 'Информация',
  'info2.php' => 'Информация',
  'info3.php' => 'Информация',
  'info4.php' => 'Информация',

  // личный кабинет / авторизация
  'dashboard.php' => 'Личный кабинет',
  'profile.php' => 'Профиль',
  'badges.php' => 'Бейджи',
  'register.php' => 'Регистрация',
  'login.php' => 'Вход',
  'logout.php' => 'Выход',

  // разделы/папки (если доступны через веб)
  'guides' => 'Руководства',
  'guides/index.php' => 'Руководства',
  'files' => 'Файлы',
  'files/index.php' => 'Файлы',
  'docs' => 'Документы',
  'docs/generate.php' => 'Генератор документов',
  'teacher' => 'Преподаватель',
  'admin' => 'Администрирование',
  'student' => 'Студент',

  // технические сегменты (не должны отображаться)
  'public' => '',
];

// Разбор текущего URL (только путь)
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = (string)parse_url($uri, PHP_URL_PATH);

// Учитываем base_url: если URL начинается с base_url, убираем его префикс
if ($baseUrl !== '' && strpos($path, $baseUrl) === 0) {
  $path = substr($path, strlen($baseUrl));
  if ($path === false) $path = '/';
}

$segments = array_values(array_filter(explode('/', trim($path, '/')), static fn($s) => $s !== ''));

// Убираем технический сегмент /public (часто встречается при разработке)
$segments = array_values(array_filter($segments, static fn($s) => $s !== 'public'));

// Если путь вида /folder/index.php — не показываем index.php как отдельную крошку
if (count($segments) >= 2 && strtolower((string)end($segments)) === 'index.php') {
  array_pop($segments);
}

// Определяем DOCUMENT_ROOT, чтобы подбирать корректные href для папок (/x -> /x/index.php)
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$docRootReal = $docRoot ? realpath($docRoot) : false;

// Собираем крошки
$crumbs = [];
$crumbs[] = ['label' => $map['index.php'], 'href' => url('index.php')];

$accum = [];
for ($i = 0; $i < count($segments); $i++) {
  $seg = (string)$segments[$i];
  $accum[] = $seg;
  $rel = implode('/', $accum);

  // Название
  $label = null;
  if (isset($map[$rel]) && $map[$rel] !== '') {
    $label = $map[$rel];
  } elseif (isset($map[$seg]) && $map[$seg] !== '') {
    $label = $map[$seg];
  }

  // Технические сегменты пропускаем
  if ($label === null && ((isset($map[$seg]) && $map[$seg] === '') || (isset($map[$rel]) && $map[$rel] === ''))) {
    continue;
  }

  // Если названия нет в карте — не выводим английские/технические слова
  if ($label === null) {
    $label = 'Страница';
  }

  // Ссылка: для папок стараемся вести на index.php, если он существует
  $hrefRel = $rel;
  $isPhp = (bool)preg_match('~\\.php$~i', $hrefRel);
  if (!$isPhp) {
    $candidate = rtrim($hrefRel, '/') . '/index.php';
    if ($docRootReal) {
      $fsCandidate = $docRootReal . '/' . ltrim($candidate, '/');
      if (is_file($fsCandidate)) {
        $hrefRel = $candidate;
      }
    }
  }

  $crumbs[] = ['label' => $label, 'href' => url($hrefRel)];
}

// Удаляем дубли (например, если уже на главной)
$crumbs = array_values(array_unique($crumbs, SORT_REGULAR));
?>

<nav class="breadcrumbs" aria-label="Хлебные крошки" itemscope itemtype="https://schema.org/BreadcrumbList">
  <?php foreach ($crumbs as $i => $c): ?>
    <span class="crumb" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
      <?php if ($i < count($crumbs) - 1): ?>
        <a href="<?php echo h($c['href']); ?>" itemprop="item"><span itemprop="name"><?php echo h($c['label']); ?></span></a>
      <?php else: ?>
        <span aria-current="page" itemprop="name"><?php echo h($c['label']); ?></span>
      <?php endif; ?>
      <meta itemprop="position" content="<?php echo $i+1; ?>" />
    </span>
    <?php if ($i < count($crumbs) - 1): ?><span class="sep">/</span><?php endif; ?>
  <?php endforeach; ?>
</nav>
