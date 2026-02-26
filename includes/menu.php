<?php
// Глобальное меню сайта веб‑сервиса экологической культуры
// Поддерживает верхнее и боковое отображение (рендерится компонентами).
return [
  ['key'=>'dashboard','title'=>'Главная','url'=>'/index.php','icon'=>'home'],
  ['key'=>'courses','title'=>'Курсы','url'=>'/courses/index.php','icon'=>'layers'],
  ['key'=>'diary','title'=>'Эко‑дневник','url'=>'/diary/index.php','icon'=>'edit','roles'=>['student']],
  ['key'=>'events','title'=>'События','url'=>'/events/index.php','icon'=>'calendar'],
  ['key'=>'materials','title'=>'Материалы','url'=>'/materials/index.php','icon'=>'file-text'],
  ['key'=>'badges','title'=>'Бейджи','url'=>'/badges/index.php','icon'=>'award','roles'=>['student']],
  ['key'=>'community','title'=>'Сообщество','url'=>'/community/index.php','icon'=>'users'],
  ['key'=>'stats','title'=>'Статистика','url'=>'/stats/index.php','icon'=>'bar-chart'],
  ['key'=>'guides','title'=>'Руководства','url'=>'/guides/index.php','icon'=>'help-circle'],
  ['key'=>'profile','title'=>'Личный кабинет','url'=>'/profile/index.php','icon'=>'user'],
  ['key'=>'admin','title'=>'Администрирование','url'=>'/admin/index.php','icon'=>'settings', 'roles'=>['admin','teacher']],
];
