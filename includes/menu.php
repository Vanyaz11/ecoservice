<?php
/**
 * Меню сайта. Возвращает массив пунктов для текущей роли.
 * Каждый пункт: [название, путь относительно public/].
 * Гостю доступно 11 открытых разделов, авторизованным — дополнительно разделы своей роли.
 */
function site_menu(?string $role): array {
  $guest = [
    ['Главная', 'index.php'],
    ['О проекте', 'about.php'],
    ['Курсы', 'courses.php'],
    ['События', 'events.php'],
    ['Новости', 'news.php'],
    ['Библиотека', 'library.php'],
    ['Эко-карта', 'eco_map.php'],
    ['Инициативы', 'initiatives.php'],
    ['Эко-привычки', 'info1.php'],
    ['Раздельный сбор', 'info2.php'],
    ['Контакты', 'contacts.php'],
    ['Справка', 'help.php'],
  ];
  if ($role === null) {
    return array_merge($guest, [['Регистрация', 'register.php'], ['Вход', 'login.php']]);
  }
  $common = [['Личный кабинет', 'dashboard.php'], ['Материалы', 'materials.php'], ['Отчёты', 'reports.php']];
  if ($role === 'student') {
    $own = [['Эко-дневник', 'eco_diary.php'], ['Достижения', 'achievements.php']];
  } elseif ($role === 'teacher') {
    $own = [['Мои курсы', 'teacher/dashboard.php'], ['Мои события', 'teacher/events.php'], ['Студенты', 'teacher/students.php']];
  } else {
    $own = [['Мои курсы', 'teacher/dashboard.php'], ['Студенты', 'teacher/students.php'], ['Администрирование', 'admin/index.php']];
  }
  return array_merge($guest, $common, $own, [['Выход', 'logout.php']]);
}
