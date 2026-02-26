<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php
session_start();
$user_role = $_SESSION['role'] ?? 'student';
$user_id = (int)($_SESSION['user_id'] ?? 0);

$root = __DIR__ . '/../uploads';
$home = $user_role === 'admin' ? $root : $root . '/u_' . $user_id;

$req = $_GET['p'] ?? '';
$rel = trim($req, '/');
$fs_path = realpath($home . '/' . $rel);
if ($fs_path === false || strpos($fs_path, realpath($home)) !== 0 || !is_file($fs_path)) {
  http_response_code(404); exit('Not found');
}
$basename = basename($fs_path);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'. rawurlencode($basename) .'"');
readfile($fs_path);

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
