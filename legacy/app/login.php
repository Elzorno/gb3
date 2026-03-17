<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ui.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/csrf.php';

gb2_db_init();

$kid = gb2_kid_current();
if ($kid) {
  header('Location: /app/dashboard.php');
  exit;
}

$cfg = gb2_config();
$pinMin = (int)($cfg['session']['pin_min_len'] ?? 6);
$pinMax = (int)($cfg['session']['pin_max_len'] ?? 6);
$pinDesc = ($pinMin === $pinMax) ? "{$pinMin}-digit" : "{$pinMin}–{$pinMax} digits";

function gb2_pin_wait_label(int $seconds): string {
  if ($seconds < 60) return $seconds . 's';
  $m = intdiv($seconds, 60);
  $s = $seconds % 60;
  if ($m < 60) return $s > 0 ? ($m . 'm ' . $s . 's') : ($m . 'm');
  $h = intdiv($m, 60);
  $m2 = $m % 60;
  return $m2 > 0 ? ($h . 'h ' . $m2 . 'm') : ($h . 'h');
}

$err = '';
$mode = 'login';
$kidId = trim((string)($_POST['kid_id'] ?? ($_GET['kid_id'] ?? '')));
$kids = [];
$kidRow = null;

try {
  $pdo = gb2_pdo();
  $kids = $pdo->query("SELECT id, name FROM kids ORDER BY sort_order ASC, name COLLATE NOCASE ASC")
              ->fetchAll(PDO::FETCH_ASSOC);

  if ($kidId !== '' && ctype_digit($kidId)) {
    $st = $pdo->prepare("SELECT id, name, pin_hash FROM kids WHERE id=?");
    $st->execute([(int)$kidId]);
    $kidRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($kidRow && trim((string)($kidRow['pin_hash'] ?? '')) === '') {
      $mode = 'setpin';
    }
  }
} catch (Throwable $e) {
  $kids = [];
  $err = 'Login is temporarily unavailable. Please tell a parent/guardian.';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  gb2_csrf_verify();

  $kidId = trim((string)($_POST['kid_id'] ?? ''));

  if ($kidId === '' || !ctype_digit($kidId)) {
    $err = 'Please choose your name.';
  } else {
    try {
      $pdo = gb2_pdo();
      $st = $pdo->prepare("SELECT id, name, pin_hash FROM kids WHERE id=?");
      $st->execute([(int)$kidId]);
      $kidRow = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
      $kidRow = null;
      $err = 'Login is temporarily unavailable. Please tell a parent/guardian.';
    }

    if (!$kidRow) {
      $err = 'Please choose your name.';
    } else {
      $existingHash = trim((string)($kidRow['pin_hash'] ?? ''));

      if ($existingHash === '') {
        $mode = 'setpin';
        $new1 = trim((string)($_POST['new_pin'] ?? ''));
        $new2 = trim((string)($_POST['confirm_pin'] ?? ''));

        if ($new1 === '' || $new2 === '') {
          $err = 'Please enter and confirm your new PIN.';
        } elseif (!ctype_digit($new1) || !ctype_digit($new2)) {
          $err = 'PIN must be numbers only.';
        } elseif (!gb2_pin_policy_ok($new1)) {
          $err = "PIN must be {$pinDesc}.";
        } elseif ($new1 !== $new2) {
          $err = 'Those did not match. Please try again.';
        } else {
          try {
            $hash = password_hash($new1, PASSWORD_ARGON2ID);
            $up = $pdo->prepare("UPDATE kids SET pin_hash=? WHERE id=?");
            $up->execute([$hash, (int)$kidRow['id']]);

            if (gb2_kid_login((int)$kidRow['id'], $new1)) {
              header('Location: /app/dashboard.php');
              exit;
            }

            $err = 'PIN saved, but login failed. Please tell a parent/guardian.';
          } catch (Throwable $e) {
            $err = 'Could not save your PIN. Please tell a parent/guardian.';
          }
        }
      } else {
        $mode = 'login';
        $pin = trim((string)($_POST['pin'] ?? ''));
        $wait = gb2_kid_pin_rate_remaining((int)$kidRow['id']);

        if ($wait > 0) {
          $err = 'Too many PIN attempts. Please wait ' . gb2_pin_wait_label($wait) . ' and try again.';
        } elseif ($pin === '') {
          $err = 'Please enter your PIN.';
        } elseif (!ctype_digit($pin)) {
          $err = 'PIN must be numbers only.';
        } elseif (!gb2_pin_policy_ok($pin)) {
          $err = "PIN must be {$pinDesc}.";
        } else {
          if (gb2_kid_login((int)$kidRow['id'], $pin)) {
            header('Location: /app/dashboard.php');
            exit;
          }
          $waitAfter = gb2_kid_pin_rate_remaining((int)$kidRow['id']);
          if ($waitAfter > 0) {
            $err = 'Too many PIN attempts. Please wait ' . gb2_pin_wait_label($waitAfter) . ' and try again.';
          } else {
            $err = 'That did not match. Please try again.';
          }
        }
      }
    }
  }
}

gb2_page_start('Kid Login', null);
?>
<div class="card">
  <div class="page-intro">
    <div class="muted-strong">Kid login</div>
    <div class="h1">
      <?php if ($mode === 'setpin' && $kidRow): ?>
        Set a new PIN for <?= gb2_h((string)$kidRow['name']) ?>
      <?php else: ?>
        Choose your name and enter your PIN
      <?php endif; ?>
    </div>
    <div class="h2">
      <?php if ($mode === 'setpin' && $kidRow): ?>
        Create a PIN you can remember.
      <?php else: ?>
        Ask a parent/guardian if you forgot your PIN.
      <?php endif; ?>
    </div>
  </div>

  <?php if ($err !== ''): ?>
    <div class="flash err" style="margin-top:12px"><?= gb2_h($err) ?></div>
  <?php endif; ?>

  <form method="post" action="/app/login.php" style="margin-top:14px" autocomplete="off">
    <input type="hidden" name="_csrf" value="<?= gb2_h(gb2_csrf_token()) ?>">

    <div class="field">
      <label class="field-label" for="kid_id">Your name</label>
      <select class="input" id="kid_id" name="kid_id" required onchange="if (this.value) { window.location.href='/app/login.php?kid_id=' + encodeURIComponent(this.value); }">
        <option value="">— Select —</option>
        <?php foreach ($kids as $k): ?>
          <?php
            $id = (string)$k['id'];
            $nm = (string)$k['name'];
            $sel = ($id === $kidId) ? ' selected' : '';
          ?>
          <option value="<?= gb2_h($id) ?>"<?= $sel ?>><?= gb2_h($nm) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if ($mode === 'setpin' && $kidId !== ''): ?>
      <div class="stack" style="margin-top:12px">
        <div class="field">
          <label class="field-label" for="new_pin">New PIN (<?= gb2_h($pinDesc) ?>)</label>
          <div class="pin-input-group">
            <input class="input pin-input" id="new_pin" name="new_pin" type="password" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required autofocus data-min="<?= (int)$pinMin ?>" data-max="<?= (int)$pinMax ?>">
            <div class="pin-counter"><span class="pin-count">0</span>/<span class="pin-max"><?= (int)$pinMax ?></span></div>
          </div>
        </div>

        <div class="field">
          <label class="field-label" for="confirm_pin">Confirm new PIN</label>
          <div class="pin-input-group">
            <input class="input pin-input" id="confirm_pin" name="confirm_pin" type="password" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required data-min="<?= (int)$pinMin ?>" data-max="<?= (int)$pinMax ?>">
            <div class="pin-counter"><span class="pin-count">0</span>/<span class="pin-max"><?= (int)$pinMax ?></span></div>
          </div>
        </div>
      </div>

      <button class="btn primary block" style="margin-top:14px" type="submit">Save PIN and log in</button>
      <div class="field-help">If you forgot your old PIN, ask a parent/guardian to reset it from Family Dashboard.</div>
    <?php else: ?>
      <div class="field" style="margin-top:12px">
        <label class="field-label" for="pin">PIN (<?= gb2_h($pinDesc) ?>)</label>
        <div class="pin-input-group">
          <input class="input pin-input" id="pin" name="pin" type="password" inputmode="numeric" pattern="[0-9]*" placeholder="••••••" required autofocus data-min="<?= (int)$pinMin ?>" data-max="<?= (int)$pinMax ?>">
          <div class="pin-counter"><span class="pin-count">0</span>/<span class="pin-max"><?= (int)$pinMax ?></span></div>
        </div>
      </div>

      <button class="btn primary block" style="margin-top:14px" type="submit">Log in</button>
      <div class="field-help">If you forgot your PIN, ask a parent/guardian to reset it.</div>
    <?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="h1">Parent/Guardian</div>
  <div class="h2">Open the admin side without logging the kid out.</div>
  <a class="btn block" href="/admin/login.php">Parent Login</a>
</div>

<?php gb2_nav('login'); gb2_page_end(); ?>
