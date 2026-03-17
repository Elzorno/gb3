<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

/**
 * Render flash messages from querystring (?ok=... or ?err=...).
 */
function gb2_flash_render(): void {
  $ok  = isset($_GET['ok'])  ? trim((string)$_GET['ok'])  : '';
  $err = isset($_GET['err']) ? trim((string)$_GET['err']) : '';

  if ($ok !== '') {
    echo '<div class="flash ok">'.gb2_h($ok).'</div>';
  }
  if ($err !== '') {
    echo '<div class="flash err">'.gb2_h($err).'</div>';
  }
}

function gb2_nav_logout_action(bool $isAdmin): string {
  return $isAdmin ? '/admin/logout.php' : '/app/logout.php';
}

/**
 * Keys used by pages:
 * - Kid: dashboard, today, bonuses, rules, history, parentlogin, logout
 * - Admin: admindash, family, grounding, kidview, reviews, infractions, definitions, setup, branding, logout
 * - Logged out: login
 */
function gb2_nav(string $active): void {
  $kid   = gb2_kid_current();
  $admin = gb2_admin_current();
  $req   = (string)($_SERVER['REQUEST_URI'] ?? '/');
  $nextEnc = rawurlencode($req);

  // Keep nav highlighting stable for legacy page keys.
  $activeAliases = [
    'review' => 'reviews',
    'bonus_defs' => 'definitions',
    'infraction_defs' => 'definitions',
    'inf_review' => 'infractions',
  ];
  if (isset($activeAliases[$active])) {
    $active = $activeAliases[$active];
  }

  if ($admin) {
    $parentPrimary = [
      ['key'=>'admindash','href'=>'/admin/dashboard.php','label'=>'Dashboard'],
      ['key'=>'family','href'=>'/admin/family.php','label'=>'Family'],
      ['key'=>'reviews','href'=>'/admin/reviews.php','label'=>'Reviews'],
      ['key'=>'grounding','href'=>'/admin/grounding.php','label'=>'Privileges'],
      ['key'=>'kidview','href'=>'/admin/kidview.php','label'=>'Kid View'],
    ];
    $parentSecondary = [
      ['key'=>'definitions','href'=>'/admin/definitions.php','label'=>'Definitions'],
      ['key'=>'infractions','href'=>'/admin/infractions.php','label'=>'Infractions'],
      ['key'=>'branding','href'=>'/admin/branding.php','label'=>'Branding'],
      ['key'=>'setup','href'=>'/admin/setup.php','label'=>'Setup'],
      ['key'=>'rules','href'=>'/app/rules.php','label'=>'Rules'],
      ['key'=>'logout','href'=>gb2_nav_logout_action(true),'label'=>'Logout'],
    ];

    echo '<nav class="parent-nav" role="navigation" aria-label="Parent navigation">';
    echo '<div class="parent-nav-row parent-nav-primary">';
    foreach ($parentPrimary as $it) {
      $cls = ($it['key'] === $active) ? 'parent-nav-item active' : 'parent-nav-item';
      echo '<a class="'.$cls.'" href="'.gb2_h((string)$it['href']).'">'.gb2_h((string)$it['label']).'</a>';
    }
    echo '</div>';

    echo '<div class="parent-nav-row parent-nav-secondary">';
    foreach ($parentSecondary as $it) {
      $cls = ($it['key'] === $active) ? 'parent-nav-item active' : 'parent-nav-item';
      if (($it['key'] ?? '') === 'logout') {
        echo '<form class="parent-nav-form" method="post" action="'.gb2_h((string)$it['href']).'">';
        echo '<input type="hidden" name="_csrf" value="'.gb2_h(gb2_csrf_token()).'">';
        echo '<button type="submit" class="'.$cls.'">'.gb2_h((string)$it['label']).'</button>';
        echo '</form>';
      } else {
        echo '<a class="'.$cls.'" href="'.gb2_h((string)$it['href']).'">'.gb2_h((string)$it['label']).'</a>';
      }
    }
    echo '</div>';
    echo '</nav>';
    return;
  }

  if ($kid) {
    $kidTabs = [
      ['key'=>'dashboard','href'=>'/app/dashboard.php','label'=>'Home'],
      ['key'=>'today','href'=>'/app/today.php','label'=>'Today'],
      ['key'=>'bonuses','href'=>'/app/bonuses.php','label'=>'Bonus'],
      ['key'=>'rules','href'=>'/app/rules.php','label'=>'Rules'],
      ['key'=>'history','href'=>'/app/history.php','label'=>'History'],
    ];

    echo '<div class="kid-nav-utility">';
    echo '<a class="kid-utility-link" href="/admin/login.php?next='.$nextEnc.'">Parent Login</a>';
    echo '<form class="kid-utility-form" method="post" action="'.gb2_h(gb2_nav_logout_action(false)).'">';
    echo '<input type="hidden" name="_csrf" value="'.gb2_h(gb2_csrf_token()).'">';
    echo '<button type="submit" class="kid-utility-link">Log out</button>';
    echo '</form>';
    echo '</div>';

    echo '<nav class="kid-tabbar" role="navigation" aria-label="Kid navigation">';
    foreach ($kidTabs as $it) {
      $cls = ($it['key'] === $active) ? 'kid-tab active' : 'kid-tab';
      echo '<a class="'.$cls.'" href="'.gb2_h((string)$it['href']).'">';
      echo '<span class="kid-tab-label">'.gb2_h((string)$it['label']).'</span>';
      echo '</a>';
    }
    echo '</nav>';
    return;
  }

  echo '<nav class="guest-nav" role="navigation" aria-label="Guest navigation">';
  echo '<a class="guest-nav-link'.($active === 'login' ? ' active' : '').'" href="/app/login.php">Login</a>';
  echo '</nav>';
}

/**
 * Shared page chrome. Uses sitewide CSS at /assets/css/app.css.
 */
function gb2_page_start(string $title, ?array $kid = null): void {
  gb2_secure_headers();
  gb2_session_start();

  $cfg = gb2_config();
  $brand = (string)($cfg['branding']['brand'] ?? 'GB2');
  $family = trim((string)($cfg['branding']['family'] ?? ''));

  $admin = gb2_admin_current();
  $brandHref = $admin ? '/admin/dashboard.php' : '/app/dashboard.php';

  $who = '';
  $isImpersonating = false;
  if ($admin) {
    $who = 'Parent/Guardian';
    if ($kid && (int)($kid['kid_id'] ?? 0) > 0 && !empty($kid['impersonating'])) {
      $isImpersonating = true;
      $who = 'Parent/Guardian • Viewing: ' . (string)($kid['name'] ?? ('Kid #' . (int)$kid['kid_id']));
    }
  } elseif ($kid) {
    $who = (string)($kid['name'] ?? 'Kid');
  }

  $req = (string)($_SERVER['REQUEST_URI'] ?? '/');
  $next = $req;
  if (strpos($req, '/admin/login.php') === 0) {
    $nextCandidate = isset($_GET['next']) ? trim((string)$_GET['next']) : '';
    if ($nextCandidate !== '' && strpos($nextCandidate, '/admin/login.php') !== 0) {
      $next = $nextCandidate;
    } else {
      $next = '/admin/setup.php';
    }
  }
  $nextEnc = rawurlencode($next);

  echo '<!doctype html><html><head><meta charset="utf-8">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
  echo '<title>'.gb2_h($title).'</title>';
  echo '<link rel="stylesheet" href="/assets/css/app.css">';
  echo '</head><body><div class="container">';

  echo '<div class="topbar">';
  echo '<div class="brand"><a href="'.gb2_h($brandHref).'">'.gb2_h($brand).'</a></div>';
  if ($family !== '') {
    echo '<div class="brand family">'.gb2_h($family).'</div>';
  }
  echo '<div class="pagetitle">'.gb2_h($title).'</div>';

  if (!$admin) {
    echo '<div class="spacer"></div>';
    echo '<a class="btn parent-login" href="/admin/login.php?next='.$nextEnc.'">Parent</a>';
  } else {
    echo '<div class="spacer"></div>';
    if ($who) {
      echo '<div class="badge">'.gb2_h($who).'</div>';
    }
    if ($isImpersonating) {
      echo '<a class="btn" style="height:36px; padding:0 12px; display:flex; align-items:center; margin-left:10px" href="/admin/kidview.php">Switch kid</a>';
    }
  }

  echo '</div>';
}

function gb2_page_end(): void {
  echo '</div><script src="/assets/js/app.js"></script></body></html>';
}
