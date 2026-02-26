<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo "Некорректный запрос"; exit(); }

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) { http_response_code(404); echo "Файл не найден"; exit(); }

$path = __DIR__ . '/../' . $m['file_path'];
if (!file_exists($path)) { http_response_code(404); echo "Файл отсутствует на сервере"; exit(); }

$filename = $m['original_name'] ?: ('material_' . $id);
$mime = $m['mime_type'] ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
header('X-Content-Type-Options: nosniff');

readfile($path);
exit();
