<?php
// includes/export.php
// Minimal XLSX and DOCX generators without external libraries.
// Usage examples:
//   export_events_xlsx($pdo, '/mnt/tmp/events.xlsx');
//   export_events_docx($pdo, '/mnt/tmp/events.docx');

require_once __DIR__ . '/helpers.php';

function fetch_events_for_export(PDO $pdo) {
  // Try several common column name sets for compatibility
  $queries = [
    "SELECT id, title, starts_at, location, description FROM events ORDER BY starts_at ASC",
    "SELECT id, name AS title, starts_at, location, description FROM events ORDER BY starts_at ASC",
    "SELECT id, title, date AS starts_at, location, description FROM events ORDER BY date ASC",
  ];
  foreach ($queries as $sql) {
    try {
      $stmt = $pdo->query($sql);
      if ($stmt !== false) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows !== false) return $rows;
      }
    } catch (Throwable $e) {
      // try next variant
    }
  }
  return [];
}

function export_events_xlsx(PDO $pdo, string $filepath): bool {
  $rows = fetch_events_for_export($pdo);
  // Create minimal OpenXML parts
  $sheetRows = "";
  $r = 1;
  // Header
  $headers = ['ID','Название','Дата/время','Место','Описание'];
  $sheetRows .= '<row r="1">';
  foreach ($headers as $i => $h) {
    $col = chr(ord('A') + $i);
    $sheetRows .= '<c r="'.$col.$r.'" t="inlineStr"><is><t>'.htmlspecialchars($h).'</t></is></c>';
  }
  $sheetRows .= '</row>';
  $r = 2;
  foreach ($rows as $row) {
    $sheetRows .= '<row r="'.$r.'">';
    $cols = [
      (string)($row['id'] ?? ''),
      (string)($row['title'] ?? ''),
      (string)($row['starts_at'] ?? ''),
      (string)($row['location'] ?? ''),
      (string)($row['description'] ?? ''),
    ];
    foreach ($cols as $i => $val) {
      $col = chr(ord('A') + $i);
      $sheetRows .= '<c r="'.$col.$r.'" t="inlineStr"><is><t>'.htmlspecialchars($val).'</t></is></c>';
    }
    $sheetRows .= '</row>';
    $r++;
  }

  $content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    .'<Default Extension="xml" ContentType="application/xml"/>'
    .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
    .'</Types>';

  $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    .'</Relationships>';

  $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
    .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    .'<sheets><sheet name="Events" sheetId="1" r:id="rId1"/></sheets></workbook>';

  $wb_rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
    .'</Relationships>';

  $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"/>';

  $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
    .'<sheetData>'.$sheetRows.'</sheetData>'
    .'</worksheet>';

  // Build zip
  $zip = new ZipArchive();
  if ($zip->open($filepath, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
    return false;
  }
  $zip->addFromString('[Content_Types].xml', $content_types);
  $zip->addFromString('_rels/.rels', $rels);
  $zip->addFromString('xl/workbook.xml', $workbook);
  $zip->addFromString('xl/_rels/workbook.xml.rels', $wb_rels);
  $zip->addFromString('xl/styles.xml', $styles);
  $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
  $zip->close();
  return true;
}

function export_events_docx(PDO $pdo, string $filepath): bool {
  $rows = fetch_events_for_export($pdo);

  $doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" '
    .'xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" '
    .'xmlns:o="urn:schemas-microsoft-com:office:office" '
    .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
    .'xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" '
    .'xmlns:v="urn:schemas-microsoft-com:vml" '
    .'xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" '
    .'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
    .'xmlns:w10="urn:schemas-microsoft-com:office:word" '
    .'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
    .'xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" '
    .'xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" '
    .'xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" '
    .'xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" '
    .'mc:Ignorable="w14 wp14"><w:body>';

  $doc .= '<w:p><w:r><w:t>Список событий</w:t></w:r></w:p>';
  // Simple table
  $doc .= '<w:tbl>';
  $headers = ['ID','Название','Дата/время','Место','Описание'];
  $doc .= '<w:tr>';
  foreach ($headers as $h) {
    $doc .= '<w:tc><w:p><w:r><w:t>'.htmlspecialchars($h).'</w:t></w:r></w:p></w:tc>';
  }
  $doc .= '</w:tr>';
  foreach ($rows as $row) {
    $cells = [
      (string)($row['id'] ?? ''),
      (string)($row['title'] ?? ''),
      (string)($row['starts_at'] ?? ''),
      (string)($row['location'] ?? ''),
      (string)($row['description'] ?? ''),
    ];
    $doc .= '<w:tr>';
    foreach ($cells as $c) {
      $doc .= '<w:tc><w:p><w:r><w:t>'.htmlspecialchars($c).'</w:t></w:r></w:p></w:tc>';
    }
    $doc .= '</w:tr>';
  }
  $doc .= '</w:tbl>';
  $doc .= '<w:sectPr/></w:body></w:document>';

  $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    .'</Relationships>';

  $content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    .'<Default Extension="xml" ContentType="application/xml"/>'
    .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    .'</Types>';

  $zip = new ZipArchive();
  if ($zip->open($filepath, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
    return false;
  }
  $zip->addFromString('[Content_Types].xml', $content_types);
  $zip->addFromString('_rels/.rels', $rels);
  $zip->addFromString('word/document.xml', $doc);
  $zip->close();
  return true;
}
