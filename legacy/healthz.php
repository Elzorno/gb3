<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/common.php';
require_once __DIR__ . '/lib/db.php';

function gb2_health_response(array $payload, int $code): never {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, max-age=0');
  echo json_encode($payload, JSON_UNESCAPED_SLASHES);
  exit;
}

$checks = [
  'app' => true,
  'db' => false,
  'uploads' => false,
  'time' => gmdate('c'),
];

try {
  $pdo = gb2_pdo();
  $pdo->query('SELECT 1')->fetchColumn();
  $checks['db'] = true;
} catch (Throwable $e) {
  $checks['db_error'] = $e->getMessage();
}

$uploads = rtrim(gb2_data_dir(), '/') . '/uploads';
if (is_dir($uploads) && is_writable($uploads)) {
  $checks['uploads'] = true;
} else {
  $checks['uploads_path'] = $uploads;
}

$ok = $checks['app'] && $checks['db'] && $checks['uploads'];
$checks['status'] = $ok ? 'ok' : 'degraded';

gb2_health_response($checks, $ok ? 200 : 503);
