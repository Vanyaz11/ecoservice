<?php require_once __DIR__ . '/../includes/header.php'; ?>
<?php
session_start();
/**
 * Генерация документов (.docx, .xlsx) и простых альтернатив (.rtf, .csv) без внешних зависимостей.
 * Для полноценного .docx/.xlsx рекомендуется установить:
 *   composer require phpoffice/phpword phpoffice/phpspreadsheet
 * Скрипт автоматически использует библиотеки, если они доступны.
 */

$data = [
  ['Студент','Раздел','Баллы','Дата'],
  ['Иванов И.И.','Эко‑дневник',85,date('Y-m-d')],
  ['Петров П.П.','Курс «Раздельный сбор»',92,date('Y-m-d')],
  ['Сидорова А.А.','События',74,date('Y-m-d')],
];

$type = $_GET['type'] ?? 'docx'; // docx|xlsx|rtf|csv
$title = 'Отчет по экосервису';

if ($type === 'docx') {
  if (class_exists('\PhpOffice\PhpWord\PhpWord')) {
    $pw = new \PhpOffice\PhpWord\PhpWord();
    $sec = $pw->addSection();
    $sec->addTitle($title, 1);
    $sec->addText('Сформировано: '.date('d.m.Y H:i'));
    $table = $sec->addTable(['borderSize'=>6,'borderColor'=>'88cc88']);
    foreach ($data as $i=>$row) {
      $table->addRow();
      foreach ($row as $cell) $table->addCell(3000)->addText((string)$cell);
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="report.docx"');
    $pw->save('php://output','Word2007'); exit;
  } else {
    // Фолбэк: RTF (открывается в Word)
    $type = 'rtf';
  }
}

if ($type === 'xlsx') {
  if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
    $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $ss->getActiveSheet();
    $sheet->setTitle('Отчет');
    foreach ($data as $r=>$row)
      foreach ($row as $c=>$val)
        $sheet->setCellValueByColumnAndRow($c+1,$r+1,$val);
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="report.xlsx"');
    $writer->save('php://output'); exit;
  } else {
    // Фолбэк: CSV (открывается в Excel)
    $type = 'csv';
  }
}

if ($type === 'rtf') {
  $rtf = "{\rtf1\ansi\deff0\fs20 ".
         "\b ". addslashes($title) ."\b0\par ".
         "Сформировано: ". date('d.m.Y H:i') ."\par ";
  $rtf .= "\par";
  foreach ($data as $row) {
    $rtf .= implode(" \tab ", array_map(fn($x)=> addslashes((string)$x), $row)) . " \par ";
  }
  $rtf .= "}";
  header('Content-Type: application/rtf');
  header('Content-Disposition: attachment; filename="report.rtf"');
  echo $rtf; exit;
}

if ($type === 'csv') {
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="report.csv"');
  $out = fopen('php://output', 'w');
  // BOM для Excel
  fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
  foreach ($data as $row) fputcsv($out, $row, ';');
  fclose($out); exit;
}

http_response_code(400);
echo "Неверный тип формата";

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
