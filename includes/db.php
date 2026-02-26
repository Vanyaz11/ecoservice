<?php
function db() {
  static $pdo;
  if ($pdo) return $pdo;
  $cfg_file = __DIR__ . '/config.php';
  if (!file_exists($cfg_file)) {
    $cfg_file = __DIR__ . '/config.sample.php';
  }
  $cfg = require $cfg_file;
  $dsn = "mysql:host={$cfg['db']['host']};dbname={$cfg['db']['name']};charset={$cfg['db']['charset']}";
  try {
    $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  } catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
  }
  return $pdo;
}
