<?php
// Образец конфигурации. Скопируйте в includes/config.php и укажите свои параметры,
// либо задайте переменные окружения (приоритетнее файла): DB_HOST, DB_PORT, DB_NAME,
// DB_USER, DB_PASS (или одну DATABASE_URL), BASE_URL, APP_AUTHOR, API_TOKEN.
return [
  'db' => [
    'host' => 'localhost',   // адрес сервера PostgreSQL
    'port' => 5432,          // порт PostgreSQL
    'name' => 'ecoservice',  // имя базы данных
    'user' => 'ecoservice',  // пользователь БД
    'pass' => '',            // пароль пользователя БД
  ],
  'app' => [
    'base_url' => '',                         // например '/ecoservice/public', если сайт в подкаталоге
    'author'   => 'Басов В. В.',              // ФИО автора работы (выводится в подвале и в справке)
    'title'    => 'Эко-сервис',               // название сервиса
    'api_token'=> 'change-me',                // токен для REST-обмена с ЭИОС
  ],
];
