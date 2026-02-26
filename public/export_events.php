<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/export.php';
require_login();

$pdo = db();
$type = $_GET['type'] ?? 'xlsx';

$fname = 'events_' . date('Ymd_His');
if ($type === 'docx') {
  $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fname . '.docx';
  if (!export_events_docx($pdo, $path)) { echo '<div class="alert error">Не удалось сформировать DOCX</div>'; exit; }
  header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
  header('Content-Disposition: attachment; filename="'.$fname.'.docx"');
  readfile($path); unlink($path); exit;
} else {
  $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fname . '.xlsx';
  if (!export_events_xlsx($pdo, $path)) { echo '<div class="alert error">Не удалось сформировать XLSX</div>'; exit; }
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment; filename="'.$fname.'.xlsx"');
  readfile($path); unlink($path); exit;
}
