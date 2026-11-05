<?php
require_once __DIR__ . '/auth.php';

/** Роль текущего пользователя или null для гостя. */
function user_role() {
  $u = current_user();
  return $u['role'] ?? null;
}

function is_teacher(): bool {
  $r = user_role();
  return $r === 'teacher' || $r === 'admin';
}

function is_admin(): bool {
  return user_role() === 'admin';
}

/** Вывод страницы с сообщением об отказе в доступе (код 403). */
function deny_access(string $msg): void {
  http_response_code(403);
  echo "<div class='card'><h2>Доступ запрещён</h2><p>" . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . "</p></div>";
  if (file_exists(__DIR__ . '/footer.php')) require __DIR__ . '/footer.php';
  exit;
}

/** Страницы преподавателя доступны преподавателю и администратору. */
function require_teacher(): void {
  require_login();
  if (!is_teacher()) deny_access('Раздел доступен только преподавателям и администратору.');
}

/** Страницы администрирования доступны только администратору. */
function require_admin(): void {
  require_login();
  if (!is_admin()) deny_access('Раздел доступен только администратору.');
}
