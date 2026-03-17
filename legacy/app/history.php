<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ui.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

gb2_db_init();
$kid = gb2_kid_require();
$pdo = gb2_pdo();

$kidId = (int)$kid['kid_id'];

function gb2_hist_json_arr(?string $json): array {
  if ($json === null) return [];
  $json = trim($json);
  if ($json === '') return [];
  $v = json_decode($json, true);
  return is_array($v) ? $v : [];
}

function gb2_hist_blocks_label(array $blocks): string {
  $on = [];
  if ((int)($blocks['phone'] ?? 0) === 1) $on[] = 'Phone';
  if ((int)($blocks['games'] ?? 0) === 1) $on[] = 'Games';
  if ((int)($blocks['other'] ?? 0) === 1) $on[] = 'Other';
  return $on ? implode(', ', $on) : 'None';
}

function gb2_hist_until_label(array $until): string {
  $parts = [];
  foreach (['phone'=>'Phone', 'games'=>'Games', 'other'=>'Other'] as $k => $label) {
    $iso = (string)($until[$k] ?? '');
    if ($iso !== '') $parts[] = "{$label}→{$iso}";
  }
  return $parts ? implode(' • ', $parts) : '—';
}

function gb2_hist_review_status(array $e): array {
  $reviewedAt = (string)($e['reviewed_at'] ?? '');
  $action = (string)($e['review_action'] ?? '');

  if ($reviewedAt === '') {
    return ['cls' => 'pending', 'text' => 'Pending review'];
  }
  if ($action === 'unlock') return ['cls' => 'approved', 'text' => 'Reviewed: unlock'];
  if ($action === 'shorten') return ['cls' => 'approved', 'text' => 'Reviewed: shorten'];
  if ($action === 'review_only') return ['cls' => 'open', 'text' => 'Reviewed: review-only'];
  return ['cls' => 'open', 'text' => 'Reviewed'];
}

$st = $pdo->prepare("SELECT * FROM submissions WHERE kid_id=? ORDER BY submitted_at DESC LIMIT 30");
$st->execute([$kidId]);
$submissions = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

$st2 = $pdo->prepare("
  SELECT
    e.id,
    e.ts,
    e.infraction_def_id,
    d.code AS def_code,
    d.label AS def_label,
    e.strike_before,
    e.strike_after,
    e.days_applied,
    e.mode,
    e.blocks_json,
    e.computed_until_json,
    e.review_on,
    e.reviewed_at,
    e.review_action,
    e.review_resolved_until_json
  FROM infraction_events e
  JOIN infraction_defs d ON d.id = e.infraction_def_id
  WHERE e.kid_id=?
  ORDER BY e.ts DESC
  LIMIT 50
");
$st2->execute([$kidId]);
$infractions = $st2->fetchAll(PDO::FETCH_ASSOC) ?: [];

gb2_page_start('History', $kid);
?>
<div class="card">
  <div class="page-intro">
    <div class="muted-strong">History</div>
    <div class="h1">Recent activity</div>
    <div class="h2">Submissions and infraction review status</div>
  </div>
  <div class="entry-copy" style="margin-top:10px">Parent/guardian notes are not shown here.</div>
</div>

<div class="card">
  <div class="h1">Recent submissions</div>
  <div class="h2">Last 30</div>

  <?php if (!$submissions): ?>
    <div class="empty-state" style="margin-top:12px">
      <div class="entry-title">No submissions yet</div>
      <div class="entry-copy">Once you submit chores or bonuses, they will show up here.</div>
    </div>
  <?php else: ?>
    <div class="section-stack" style="margin-top:12px">
      <?php foreach ($submissions as $r): ?>
        <?php
          $kind = (string)($r['kind'] ?? '');
          $kindLabel = ($kind === 'bonus') ? 'Bonus' : 'Base chore';
          $submittedAt = (string)($r['submitted_at'] ?? '');
          $status = (string)($r['status'] ?? 'open');
          $statusCls = preg_replace('/[^a-z_]/', '', strtolower($status));
          if ($statusCls === '') $statusCls = 'open';
        ?>
        <div class="entry-card">
          <div class="row wrap" style="justify-content:space-between; gap:12px">
            <div>
              <div class="entry-title"><?= gb2_h($kindLabel) ?></div>
              <div class="entry-copy"><?= gb2_h($submittedAt) ?></div>
            </div>
            <div class="status <?= gb2_h($statusCls) ?>"><?= gb2_h($status) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="h1">Infractions</div>
  <div class="h2">Last 50</div>

  <?php if (!$infractions): ?>
    <div class="empty-state" style="margin-top:12px">
      <div class="entry-title">No infractions recorded</div>
      <div class="entry-copy">This section only shows entries that have been logged in GB2.</div>
    </div>
  <?php else: ?>
    <div class="section-stack" style="margin-top:12px">
      <?php foreach ($infractions as $e): ?>
        <?php
          $ts = (string)($e['ts'] ?? '');
          $label = (string)($e['def_label'] ?? 'Infraction');
          $strikeAfter = (int)($e['strike_after'] ?? 0);
          $daysApplied = (int)($e['days_applied'] ?? 0);
          $mode = (string)($e['mode'] ?? 'set');
          if ($mode !== 'set' && $mode !== 'add') $mode = 'set';

          $blocks = gb2_hist_json_arr((string)($e['blocks_json'] ?? ''));
          $computedUntil = gb2_hist_json_arr((string)($e['computed_until_json'] ?? ''));
          $reviewOn = (string)($e['review_on'] ?? '');
          $resolvedUntil = gb2_hist_json_arr((string)($e['review_resolved_until_json'] ?? ''));
          $review = gb2_hist_review_status($e);
        ?>
        <div class="entry-card">
          <div class="row wrap" style="justify-content:space-between; gap:12px; align-items:flex-start">
            <div style="flex:1">
              <div class="entry-title"><?= gb2_h($label) ?></div>
              <div class="entry-copy"><?= gb2_h($ts) ?></div>
            </div>
            <div class="status <?= gb2_h($review['cls']) ?>"><?= gb2_h($review['text']) ?></div>
          </div>

          <!-- Strike Visualization -->
          <div style="margin-top:10px">
            <div class="small" style="margin-bottom:6px">Strike: <strong><?= (int)$strikeAfter ?></strong></div>
            <div style="display:flex; gap:4px">
              <?php for ($i = 1; $i <= 3; $i++): ?>
                <div style="flex:1; height:6px; border-radius:3px; background:<?= ($i <= $strikeAfter) ? 'var(--bad)' : 'rgba(255,255,255,.1)' ?>"></div>
              <?php endfor; ?>
            </div>
          </div>

          <div class="meta-list" style="margin-top:10px">
            <div>⏱ <b><?= (int)$daysApplied ?> days</b> • 🔒 <?= gb2_h(gb2_hist_blocks_label($blocks)) ?></div>
            <?php if (!empty($computedUntil) && array_filter($computedUntil)): ?>
              <div style="color:var(--warn)">🔐 Until: <?= gb2_h(gb2_hist_until_label($computedUntil)) ?></div>
            <?php endif; ?>
            <?php if ($reviewOn !== ''): ?>
              <div>📋 Review scheduled: <?= gb2_h($reviewOn) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php gb2_nav('history'); gb2_page_end(); ?>
