<?php
// includes/nav.php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/roles.php';


$ROUTES = [
  'home'        => ['title' => 'Главная',           'path' => 'index.php'],
  'about'       => ['title' => 'О проекте',         'path' => 'about.php',          'parent' => 'home'],
  'learning'    => ['title' => 'Обучение',          'path' => 'learning.php',       'parent' => 'home'],
  'courses'     => ['title' => 'Курсы',             'path' => 'courses.php',        'parent' => 'learning'],
  'eco_diary'   => ['title' => 'Эко‑дневник',       'path' => 'eco_diary.php',      'parent' => 'home'],
  'events'      => ['title' => 'События',           'path' => 'events.php',         'parent' => 'home'],
  'library'     => ['title' => 'Библиотека',        'path' => 'library.php',        'parent' => 'home'],
  'news'        => ['title' => 'Новости',           'path' => 'news.php',           'parent' => 'home'],
  'eco_map'     => ['title' => 'Эко‑карта',         'path' => 'eco_map.php',        'parent' => 'home'],
  'initiatives' => ['title' => 'Инициативы',        'path' => 'initiatives.php',    'parent' => 'home'],
  'reports'     => ['title' => 'Отчёты',            'path' => 'reports.php',        'parent' => 'home'],
  'contacts'    => ['title' => 'Контакты',          'path' => 'contacts.php',       'parent' => 'home'],
  // Auth and profile
  'register'    => ['title' => 'Регистрация',       'path' => 'register.php',       'parent' => 'home'],
  'login'       => ['title' => 'Вход',              'path' => 'login.php',          'parent' => 'home'],
  'dashboard'   => ['title' => 'Личный кабинет',    'path' => 'dashboard.php',      'parent' => 'home'],
  // Subsections (examples; adjust if paths differ)
  'student'     => ['title' => 'Студент',           'path' => 'student/index.php',  'parent' => 'dashboard'],
  'teacher'     => ['title' => 'Преподаватель',     'path' => 'teacher/dashboard.php','parent'=> 'dashboard'],
  'admin'       => ['title' => 'Администрирование', 'path' => 'admin/dashboard.php', 'parent' => 'dashboard'],
];

$MAIN_NAV = [
  'home','about','eco_diary','events','library','news','eco_map','reports','contacts'
];

/**
 * Resolve current route name from executing script path.
 */
function current_route_name(array $ROUTES): ?string {
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $script = ltrim(str_replace(['\\'], ['/'], $script), '/');
  $parts = explode('/', $script);
  // try relative to /public if project uses /public as docroot
  $candidate = end($parts);
  foreach ($ROUTES as $name => $meta) {
    $p = ltrim(str_replace(['\\'], ['/'], $meta['path']), '/');
    if ($p === $candidate || $script.endsWith($p)) {
      return $name;
    }
  }
  // Fallback by basename match
  foreach ($ROUTES as $name => $meta) {
    if (basename($script) === basename($meta['path'])) return $name;
  }
  return null;
}

// polyfill for endsWith since PHP<8 does not have str_ends_with
if (!function_exists('str_ends_with')) {
  function str_ends_with(string $haystack, string $needle): bool {
    if ($needle === '') return true;
    $len = strlen($needle);
    return substr($haystack, -$len) === $needle;
  }
}

/**
 * Render main navigation.
 */
function render_main_nav(array $MAIN_NAV, array $ROUTES): void {
  $curr = current_route_name($ROUTES);
  echo '<nav class="nav" role="navigation" aria-label="Главное меню">';
  foreach ($MAIN_NAV as $routeName) {
    if (!isset($ROUTES[$routeName])) continue;
    $meta = $ROUTES[$routeName];
    $href = url($meta['path']);
    $label = h($meta['title']);
    $active = ($routeName === $curr) ? ' class="active" aria-current="page"' : '';
    echo "<a href=\"{$href}\"{$active}>{$label}</a>";
  }
  echo '</nav>';
}

/**
 * Build breadcrumbs chain from current route up to root.
 */
function build_breadcrumbs(array $ROUTES): array {
  $name = current_route_name($ROUTES);
  if (!$name) $name = 'home';
  $chain = [];
  $visited = [];
  while ($name && isset($ROUTES[$name]) && !isset($visited[$name])) {
    $visited[$name] = true;
    $meta = $ROUTES[$name];
    array_unshift($chain, ['title' => $meta['title'], 'path' => $meta['path'], 'name' => $name]);
    $name = $meta['parent'] ?? null;
  }
  return $chain;
}

/**
 * Render breadcrumbs on every page.
 */
function render_breadcrumbs(array $ROUTES): void {
  $crumbs = build_breadcrumbs($ROUTES);
  echo '<nav class="breadcrumbs" aria-label="Хлебные крошки">';
  $last = count($crumbs) - 1;
  foreach ($crumbs as $i => $c) {
    $isLast = $i === $last;
    $label = h($c['title']);
    if ($isLast) {
      echo "<span aria-current=\"page\">{$label}</span>";
    } else {
      $href = url($c['path']);
      echo "<a href=\"{$href}\">{$label}</a><span class=\"sep\" aria-hidden=\"true\">/</span>";
    }
  }
  echo '</nav>';
}
