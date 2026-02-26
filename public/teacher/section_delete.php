<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';

require_login();
require_teacher();

$pdo = db();
$uid = (int)current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . url('teacher/dashboard.php', true));
  exit;
}

$id = (int)($_POST['id'] ?? 0);
$course_id = (int)($_POST['course_id'] ?? 0);

// Load section
$st = $pdo->prepare("SELECT id, course_id FROM course_sections WHERE id = ?");
$st->execute([$id]);
$section = $st->fetch();

if (!$section) {
  header('Location: ' . url('teacher/edit_course.php?id=' . $course_id, true));
  exit;
}

$course_id = (int)$section['course_id'];

// If schema has courses.author_id then enforce ownership (admins may edit any)
$cst = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$cst->execute([$course_id]);
$course = $cst->fetch();

if ($course && array_key_exists('author_id', $course) && user_role() !== 'admin') {
  if ((int)$course['author_id'] !== $uid) {
    http_response_code(403);
    echo "Нет доступа";
    exit;
  }
}

$pdo->prepare("DELETE FROM course_sections WHERE id = ?")->execute([$id]);

header('Location: ' . url('teacher/edit_course.php?id=' . $course_id, true));
exit;
