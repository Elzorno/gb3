<?php
declare(strict_types=1);

function gb2_load_php_array_file(string $path): array {
  if (!is_file($path)) return [];
  $loaded = require $path;
  return is_array($loaded) ? $loaded : [];
}

function gb2_array_merge_assoc_recursive(array $base, array $overlay): array {
  foreach ($overlay as $key => $value) {
    if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
      $base[$key] = gb2_array_merge_assoc_recursive($base[$key], $value);
    } else {
      $base[$key] = $value;
    }
  }
  return $base;
}

function gb2_write_php_array_file(string $path, array $cfg, string $comment = 'GB2 local runtime config (not committed).'): void {
  $dir = dirname($path);
  if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
    throw new RuntimeException('Failed creating config directory');
  }

  $tmp = $path . '.tmp';
  $php = "<?php
"
       . "declare(strict_types=1);

"
       . "/**
 * {$comment}
 */

"
       . 'return ' . var_export($cfg, true) . ";
";

  if (file_put_contents($tmp, $php, LOCK_EX) === false) {
    throw new RuntimeException('Failed writing config');
  }

  @chmod($tmp, 0640);
  if (!@rename($tmp, $path)) {
    @unlink($tmp);
    throw new RuntimeException('Failed saving config');
  }
}

function gb2_config(): array {
  static $cfg = null;
  if ($cfg !== null) return $cfg;

  $fallback = function(): array {
    $cfg = require __DIR__ . '/../config.sample.php';
    $cfg['admin_password_hash'] = '';
    return $cfg;
  };

  $path = __DIR__ . '/../config.php';
  if (!file_exists($path)) {
    $cfg = $fallback();
    return $cfg;
  }

  // config.php should normally be a PHP file that returns an array.
  // Some zip tools flatten symlinks into a text file containing the target path,
  // e.g. "/var/www/data/config.php". Support that safely.
  $loaded = require $path;

  if (is_array($loaded)) {
    $cfg = $loaded;
  } elseif (is_string($loaded)) {
    $candidate = trim($loaded);
    if ($candidate !== '' && file_exists($candidate)) {
      $loaded2 = require $candidate;
      if (is_array($loaded2)) {
        $cfg = $loaded2;
      }
    }
  }

  if (!is_array($cfg)) {
    // Last-resort: fall back to sample config to avoid fatal type errors.
    $cfg = $fallback();
  }

  // Local overrides always live under data_dir/config.local.php.
  $dataDir = (string)($cfg['data_dir'] ?? (__DIR__ . '/../data'));
  $localPath = rtrim($dataDir, '/') . '/config.local.php';
  $local = gb2_load_php_array_file($localPath);
  if ($local) {
    $cfg = gb2_array_merge_assoc_recursive($cfg, $local);
  }

  return $cfg;
}

function gb2_data_dir(): string {
  $cfg = gb2_config();
  return (string)($cfg['data_dir'] ?? (__DIR__ . '/../data'));
}

function gb2_is_https(): bool {
  $https = $_SERVER['HTTPS'] ?? '';
  if (is_string($https) && $https !== '' && strtolower($https) !== 'off') return true;

  $port = (string)($_SERVER['SERVER_PORT'] ?? '');
  if ($port === '443') return true;

  $xfp = (string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
  if (strtolower($xfp) === 'https') return true;

  $xfs = (string)($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '');
  if (strtolower($xfs) === 'on') return true;

  return false;
}

function gb2_cookie_secure(): bool {
  $cfg = gb2_config();
  $secure = $cfg['session']['cookie_secure'] ?? null;
  if (is_bool($secure)) return $secure;
  return gb2_is_https();
}

function gb2_session_start(): void {
  $cfg = gb2_config();
  $name = (string)($cfg['session']['cookie_name'] ?? 'gb2_sess');
  if (session_status() === PHP_SESSION_ACTIVE) return;

  session_name($name);
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => gb2_cookie_secure(),
    'samesite' => 'Strict',
  ]);
  session_start();
}

function gb2_secure_headers(): void {
  header('X-Frame-Options: DENY');
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: no-referrer');
  header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');

  // Conservative CSP; designed to work with current GB2 (external JS, some inline styles).
  header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'");
}

function gb2_h(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

function gb2_now_iso(): string { return gmdate('c'); }

function gb2_client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? ''; }

function gb2_user_agent(): string { return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300); }
