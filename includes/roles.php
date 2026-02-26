<?php
require_once __DIR__ . '/auth.php';

function user_role() {
  $u = current_user();
  return $u['role'] ?? null;
}

function is_teacher() {
  $r = user_role();
  return $r === 'teacher' || $r === 'admin';
}

function require_teacher() {
  if (!is_teacher()) {
    http_response_code(403);
    echo "<div class='container'><div class='card'>Доступ только для преподавателей</div></div>";
    exit;
  }
}
