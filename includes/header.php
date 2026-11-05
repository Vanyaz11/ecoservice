<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/menu.php';
$u = current_user();
$role = $u['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo h($pageTitle ?? 'Эко-сервис'); ?> — Эко-сервис МУИВ</title>
  <link rel="stylesheet" href="<?php echo url('style.css'); ?>">
</head>
<body>
<header class="topbar">
  <div class="container">
    <div class="header-top">
      <a class="header-logo" href="<?php echo url('index.php'); ?>" aria-label="На главную">
        <img src="<?php echo url('logo-muiv-white.svg'); ?>" alt="Московский университет имени С. Ю. Витте">
      </a>
      <div class="brand"><a href="<?php echo url('index.php'); ?>">Сервис формирования <br> экологической культуры обучающихся</a></div>
    </div>
    <nav class="nav" aria-label="Главное меню">
      <?php foreach (site_menu($role) as [$title, $path]): ?>
        <a href="<?php echo url($path); ?>"<?php echo basename($_SERVER['SCRIPT_NAME']) === basename($path) ? ' class="active"' : ''; ?>><?php echo h($title); ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<main class="container">
<?php include_once __DIR__ . "/breadcrumbs.php"; ?>

