<?php
declare(strict_types=1);

/**
 * Runtime config loader (NOT committed).
 *
 * Prefer /var/www/data/config.local.php (secrets) and fall back to repo sample.
 */

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
  $cfg = require $local;
  if (!is_array($cfg)) {
    throw new RuntimeException('config.local.php must return an array');
  }
  return $cfg;
}

$sample = __DIR__ . '/../config.sample.php';
$cfg = require $sample;
if (!is_array($cfg)) {
  throw new RuntimeException('config.sample.php must return an array');
}
return $cfg;
