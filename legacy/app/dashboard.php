<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ui.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/rotation.php';
require_once __DIR__ . '/../lib/privileges.php';
require_once __DIR__ . '/../lib/ledger.php';

gb2_db_init();
$kid = gb2_kid_require();

$todayObj = new DateTimeImmutable('today');
$todayStr = $todayObj->format('Y-m-d');
$todayNice = (new DateTimeImmutable('now'))->format('l, M j');

$assignments = [];
if (function_exists('gb2_is_weekday') && gb2_is_weekday($todayObj)) {
  if (function_exists('gb2_rotation_generate_for_day')) {
    gb2_rotation_generate_for_day($todayStr);
  }
  if (function_exists('gb2_assignments_for_kid_day')) {
    $assignments = gb2_assignments_for_kid_day((int)$kid['kid_id'], $todayStr);
  }
}

$priv = gb2_priv_get_for_kid((int)$kid['kid_id']);

$kidId = (int)$kid['kid_id'];
$weekStart = gb2_week_start_monday(new DateTimeImmutable('today'))->format('Y-m-d');
$earnedWeek = gb2_ledger_sum_cents_for_kid($kidId, 'bonus_reward', $weekStart);
$earnedAll  = gb2_ledger_sum_cents_for_kid($kidId, 'bonus_reward', null);

function gb2_until_ts_dash(string $iso): int {
  if ($iso === '') return 0;
  $t = strtotime($iso);
  return $t ? (int)$t : 0;
}

$phoneUntilIso = (string)($priv['phone_locked_until'] ?? '');
$gamesUntilIso = (string)($priv['games_locked_until'] ?? '');
$otherUntilIso = (string)($priv['other_locked_until'] ?? '');

$phoneUntilTs = gb2_until_ts_dash($phoneUntilIso);
$gamesUntilTs = gb2_until_ts_dash($gamesUntilIso);
$otherUntilTs = gb2_until_ts_dash($otherUntilIso);

$todayCount = count($assignments);
$todaySummary = $todayCount === 0
  ? 'No weekday chores are assigned for today.'
  : ($todayCount === 1 ? 'You have 1 chore to submit today.' : 'You have '.$todayCount.' chores to submit today.');

// Get today's submission status
$pdo = gb2_pdo();
$st = $pdo->prepare("
  SELECT COUNT(*) as approved, SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
  FROM submissions
  WHERE kid_id=? AND kind='base' AND submitted_at LIKE ?
");
$st->execute([$kidId, $todayStr . '%']);
$subStats = $st->fetch(PDO::FETCH_ASSOC);
$submittedToday = (int)($subStats['approved'] ?? 0);
$pendingToday = (int)($subStats['pending'] ?? 0);
$progressPercent = $todayCount > 0 ? (int)(($submittedToday / $todayCount) * 100) : 0;

gb2_page_start('Dashboard', $kid);
?>
<div class="card">
  <div class="page-intro">
    <div class="muted-strong">Today</div>
    <div class="h1"><?= gb2_h($todayNice) ?></div>
    <div class="h2"><?= gb2_h($todaySummary) ?></div>
  </div>

  <div class="action-grid two" style="margin-top:14px">
    <a class="action-card primary" href="/app/today.php">
      <div class="action-title">Open Today</div>
      <div class="action-copy">Submit chores, take photos, or verify without a photo.</div>
    </a>
    <a class="action-card" href="/app/bonuses.php">
      <div class="action-title">Bonuses</div>
      <div class="action-copy">Claim bonus jobs and track money earned.</div>
    </a>
    <a class="action-card" href="/app/history.php">
      <div class="action-title">History</div>
      <div class="action-copy">See recent submissions and infraction review status.</div>
    </a>
    <a class="action-card" href="/app/rules.php">
      <div class="action-title">Rules</div>
      <div class="action-copy">Check house rules, chores, and infraction definitions.</div>
    </a>
  </div>
</div>

<div class="card">
  <div class="h1">Your chores</div>
  <div class="h2">What needs attention today</div>

  <!-- Progress Bar -->
  <?php if ($todayCount > 0): ?>
    <div style="margin-top:12px">
      <div class="row" style="justify-content:space-between; margin-bottom:6px">
        <div class="small">Progress</div>
        <div class="small"><?= (int)$submittedToday ?> of <?= (int)$todayCount ?> done</div>
      </div>
      <div style="height:8px; background:rgba(255,255,255,.1); border-radius:4px; overflow:hidden">
        <div style="height:100%; background:linear-gradient(90deg, var(--ok), var(--accent)); width:<?= (int)$progressPercent ?>%; transition:width 0.3s ease"></div>
      </div>
      <?php if ($pendingToday > 0): ?>
        <div class="note" style="margin-top:8px">⏳ <?= (int)$pendingToday ?> waiting for approval</div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if (!$assignments): ?>
    <div class="empty-state" style="margin-top:12px">
      <div class="entry-title">Nothing assigned right now</div>
      <div class="entry-copy">If it is a weekend, check Bonuses for optional work. Otherwise, your chores for today are already done or not assigned.</div>
    </div>
  <?php else: ?>
    <ul class="simple-list" style="margin-top:12px">
      <?php foreach ($assignments as $a): ?>
        <?php $label = (string)($a['slot_title'] ?? $a['slot_label'] ?? $a['chore_title'] ?? 'Chore'); ?>
        <li>
          <div class="entry-title" style="font-size:16px"><?= gb2_h($label) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<div class="card">
  <div class="h1">Privileges</div>
  <div class="h2">Current access</div>

  <div class="summary-grid three compact" style="margin-top:12px">
    <div class="summary-card">
      <div class="summary-title">Phone</div>
      <div class="tag-row">
        <span class="status <?= ((int)$priv['phone_locked'] === 1) ? 'rejected' : 'approved' ?>"><?= ((int)$priv['phone_locked'] === 1) ? 'Locked' : 'Allowed' ?></span>
      </div>
      <?php if ((int)$priv['phone_locked'] === 1 && $phoneUntilIso !== '' && $phoneUntilTs > 0): ?>
        <div class="summary-copy" style="margin-top:8px">Until <?= gb2_h($phoneUntilIso) ?></div>
        <div class="lock-countdown" data-gb2-until="<?= (int)$phoneUntilTs ?>"></div>
      <?php endif; ?>
    </div>

    <div class="summary-card">
      <div class="summary-title">Games</div>
      <div class="tag-row">
        <span class="status <?= ((int)$priv['games_locked'] === 1) ? 'rejected' : 'approved' ?>"><?= ((int)$priv['games_locked'] === 1) ? 'Locked' : 'Allowed' ?></span>
      </div>
      <?php if ((int)$priv['games_locked'] === 1 && $gamesUntilIso !== '' && $gamesUntilTs > 0): ?>
        <div class="summary-copy" style="margin-top:8px">Until <?= gb2_h($gamesUntilIso) ?></div>
        <div class="lock-countdown" data-gb2-until="<?= (int)$gamesUntilTs ?>"></div>
      <?php endif; ?>
    </div>

    <div class="summary-card">
      <div class="summary-title">Other</div>
      <div class="tag-row">
        <span class="status <?= ((int)$priv['other_locked'] === 1) ? 'rejected' : 'approved' ?>"><?= ((int)$priv['other_locked'] === 1) ? 'Locked' : 'Allowed' ?></span>
      </div>
      <?php if ((int)$priv['other_locked'] === 1 && $otherUntilIso !== '' && $otherUntilTs > 0): ?>
        <div class="summary-copy" style="margin-top:8px">Until <?= gb2_h($otherUntilIso) ?></div>
        <div class="lock-countdown" data-gb2-until="<?= (int)$otherUntilTs ?>"></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="h1">Bonus earnings</div>
  <div class="h2">Cash-only rewards</div>

  <div class="summary-grid two" style="margin-top:12px">
    <div class="summary-card">
      <div class="summary-title">This week</div>
      <div class="kpi"><?= gb2_h(gb2_money($earnedWeek)) ?></div>
      <div class="kpi-sub">Week of <?= gb2_h($weekStart) ?></div>
    </div>
    <div class="summary-card">
      <div class="summary-title">Total earned</div>
      <div class="kpi"><?= gb2_h(gb2_money($earnedAll)) ?></div>
      <div class="kpi-sub">All approved bonuses</div>
    </div>
  </div>

  <div class="summary-copy" style="margin-top:12px">
    Need help? A parent/guardian can unlock privileges from Parent Login.
  </div>
</div>

<?php gb2_nav('dashboard'); gb2_page_end(); ?>
