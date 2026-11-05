<?php
function h($s){return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');}
function url($path){
  require_once __DIR__ . '/db.php';
  $cfg = app_config();
  $base = rtrim($cfg['app']['base_url'] ?? '', '/');
  $path = ltrim($path, '/');
  return $base . '/' . $path;
}
