<?php
/**
 * Подключение к базе данных PostgreSQL через PDO.
 * Параметры берутся из переменных окружения (удобно для хостинга),
 * при их отсутствии — из includes/config.php, затем — из config.sample.php.
 */
function app_config(): array {
  static $cfg;
  if ($cfg !== null) return $cfg;
  $file = __DIR__ . '/config.php';
  if (!file_exists($file)) $file = __DIR__ . '/config.sample.php';
  $cfg = require $file;

  // DATABASE_URL вида postgres://user:pass@host:5432/dbname (формат многих хостингов)
  $url = getenv('DATABASE_URL');
  if ($url && ($u = parse_url($url))) {
    $cfg['db']['host'] = $u['host'] ?? $cfg['db']['host'];
    $cfg['db']['port'] = $u['port'] ?? $cfg['db']['port'];
    $cfg['db']['name'] = ltrim($u['path'] ?? '', '/') ?: $cfg['db']['name'];
    $cfg['db']['user'] = $u['user'] ?? $cfg['db']['user'];
    $cfg['db']['pass'] = isset($u['pass']) ? urldecode($u['pass']) : $cfg['db']['pass'];
  }
  foreach (['host' => 'DB_HOST', 'port' => 'DB_PORT', 'name' => 'DB_NAME', 'user' => 'DB_USER', 'pass' => 'DB_PASS'] as $k => $env) {
    $v = getenv($env);
    if ($v !== false && $v !== '') $cfg['db'][$k] = $v;
  }
  foreach (['base_url' => 'BASE_URL', 'author' => 'APP_AUTHOR', 'api_token' => 'API_TOKEN'] as $k => $env) {
    $v = getenv($env);
    if ($v !== false && $v !== '') $cfg['app'][$k] = $v;
  }
  return $cfg;
}

function db(): PDO {
  static $pdo;
  if ($pdo) return $pdo;
  $cfg = app_config();
  $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['db']['host'], (int)$cfg['db']['port'], $cfg['db']['name']);
  try {
    $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET client_encoding TO 'UTF8'");
  } catch (PDOException $e) {
    http_response_code(500);
    error_log('DB connection failed: ' . $e->getMessage());
    die('Database connection failed. Проверьте настройки подключения к PostgreSQL.');
  }
  return $pdo;
}
