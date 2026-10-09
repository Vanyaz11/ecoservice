<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
$u = current_user();
$role = $u['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Эко‑сервис</title>
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
      <a href="<?php echo url('index.php'); ?>">Главная</a>
      <a href="<?php echo url('about.php'); ?>">О проекте</a>
      <?php if ($u): ?>
        <?php if ($role === 'student'): ?>
          <a href="<?php echo url('courses.php'); ?>">Курсы</a>
          <a href="<?php echo url('eco_diary.php'); ?>">Эко‑дневник</a>
          <a href="<?php echo url('events.php'); ?>">События</a>
          <a href="<?php echo url('materials.php'); ?>">Материалы</a>
          <a href="<?php echo url('achievements.php'); ?>">Достижения</a>
          <a href="<?php echo url('reports.php'); ?>">Отчёты</a>
        <?php else: ?>
          <a href="<?php echo url('teacher/dashboard.php'); ?>">Курсы</a>
          <a href="<?php echo url('teacher/events.php'); ?>">События</a>
          <a href="<?php echo url('materials.php'); ?>">Материалы</a>
          <a href="<?php echo url('teacher/students.php'); ?>">Студенты</a>
          <a href="<?php echo url('reports.php'); ?>">Отчёты</a>
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($u): ?>
        <a href="<?php echo url('dashboard.php'); ?>">Личный кабинет</a>
        <a href="<?php echo url('logout.php'); ?>">Выход</a>
      <?php else: ?>
        <a href="<?php echo url('register.php'); ?>">Регистрация</a>
        <a href="<?php echo url('login.php'); ?>">Вход</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
<?php include_once __DIR__ . "/breadcrumbs.php"; ?>

