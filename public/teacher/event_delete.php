<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/roles.php';
require_once __DIR__ . '/../../includes/db.php';
require_login();
if (!is_teacher()) { header('Location: ../index.php'); exit; }
$id = (int)($_POST['id'] ?? 0);
if ($id) {
  $pdo = db();
  $pdo->prepare("DELETE FROM event_registrations WHERE event_id=?")->execute([$id]);
  $pdo->prepare("DELETE FROM events WHERE id=?")->execute([$id]);
}
header('Location: ' . url('teacher/events.php'));
