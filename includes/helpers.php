<?php
function h($s){return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');}
function url($path){
  $cfg_file = __DIR__ . '/config.php';
  if (!file_exists($cfg_file)) $cfg_file = __DIR__ . '/config.sample.php';
  $cfg = require $cfg_file;
  $base = rtrim($cfg['app']['base_url'] ?? '', '/');
  $path = ltrim($path, '/');
  return $base . '/' . $path;
}
