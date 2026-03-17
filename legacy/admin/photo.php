<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/common.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

gb2_db_init();
gb2_admin_require();

function gb2_admin_photo_abs_path(string $photoPath): ?string {
  $rel = ltrim($photoPath, '/');
  if ($rel === '' || $rel === 'uploads/NO_PHOTO') return null;
  if (strpos($rel, 'uploads/') !== 0) return null;
  if (str_contains($rel, '..')) return null;

  $dataDir = rtrim(gb2_data_dir(), '/');
  $base = realpath($dataDir . '/uploads');
  if ($base === false) return null;

  $abs = $dataDir . '/' . $rel;
  $real = realpath($abs);
  if ($real === false || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0) return null;
  if (!is_file($real) || !is_readable($real)) return null;

  return $real;
}

$subId = (int)($_GET['sub_id'] ?? 0);
if ($subId <= 0) {
  http_response_code(404);
  exit('Not found');
}

$pdo = gb2_pdo();
$st = $pdo->prepare('SELECT photo_path FROM submissions WHERE id=? LIMIT 1');
$st->execute([$subId]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  http_response_code(404);
  exit('Not found');
}

$abs = gb2_admin_photo_abs_path((string)($row['photo_path'] ?? ''));
if ($abs === null) {
  http_response_code(404);
  exit('Photo not available');
}

$mime = 'application/octet-stream';
if (class_exists('finfo')) {
  $fi = new finfo(FILEINFO_MIME_TYPE);
  $detected = (string)$fi->file($abs);
  if ($detected !== '') $mime = $detected;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($abs));
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . rawurlencode(basename($abs)) . '"');
readfile($abs);
exit;
