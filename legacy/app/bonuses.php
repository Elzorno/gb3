<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ui.php';
require_once __DIR__ . '/../lib/bonuses.php';
require_once __DIR__ . '/../lib/ledger.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

gb2_db_init();
$kid = gb2_kid_require();

$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$weekStart = gb2_bonus_week_start($today);

$pdo = gb2_pdo();
$st = $pdo->prepare("SELECT COUNT(*) as c FROM bonus_instances WHERE week_start=?");
$st->execute([$weekStart]);
$c = (int)($st->fetch()['c'] ?? 0);
if ($c === 0) {
  gb2_bonus_reset_week($weekStart);
}

$list = gb2_bonus_list_week($weekStart);

$kidId = (int)($kid['kid_id'] ?? 0);
$earnedWeek = gb2_ledger_sum_cents_for_kid($kidId, 'bonus_reward', $weekStart);
$earnedAll  = gb2_ledger_sum_cents_for_kid($kidId, 'bonus_reward', null);
$recentEarn = gb2_ledger_list_for_kid($kidId, 10, 'bonus_reward');

gb2_page_start('Bonuses', $kid);
?>
<div class="card">
  <div class="page-intro">
    <div class="muted-strong">Bonuses</div>
    <div class="h1">This week</div>
    <div class="h2">Week starting <?= gb2_h($weekStart) ?></div>
  </div>

  <?php gb2_flash_render(); ?>

  <div class="summary-grid two" style="margin-top:12px">
    <div class="summary-card">
      <div class="summary-title">This week</div>
      <div class="kpi"><?= gb2_h(gb2_money($earnedWeek)) ?></div>
      <div class="kpi-sub">Approved this week</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">Total earned</div>
      <div class="kpi"><?= gb2_h(gb2_money($earnedAll)) ?></div>
      <div class="kpi-sub">All approved bonuses</div>
    </div>
  </div>

  <div class="summary-card" style="margin-top:12px">
    <div class="summary-title">Recent approvals</div>
    <?php if ($recentEarn): ?>
      <ul class="simple-list" style="margin-top:10px">
        <?php foreach ($recentEarn as $e): ?>
          <li>
            <div class="row wrap" style="justify-content:space-between; gap:10px">
              <div>
                <div class="entry-title" style="font-size:16px"><?= gb2_h((string)($e['note'] ?? 'Bonus')) ?></div>
                <div class="entry-copy"><?= gb2_h(substr((string)$e['ts'], 0, 10)) ?></div>
              </div>
              <div class="status approved"><?= gb2_h(gb2_money((int)$e['amount_cents'])) ?></div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="entry-copy" style="margin-top:8px">No bonus earnings yet. Claim a bonus, do the work, and submit proof.</div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="h1">Available jobs</div>
  <div class="h2">First-come. Reset every Monday.</div>

  <div class="section-stack" style="margin-top:12px">
    <?php foreach ($list as $b): ?>
      <?php
        $title   = (string)($b['title'] ?? 'Bonus');
        $status  = (string)($b['status'] ?? 'available');
        $instId  = (int)($b['instance_id'] ?? 0);
        $rewardCents = (int)($b['reward_cents'] ?? 0);
        $claimedBy = (int)($b['claimed_by_kid'] ?? 0);
      ?>
      <div class="entry-card">
        <div class="row wrap" style="justify-content:space-between; gap:12px">
          <div class="kv">
            <div class="entry-title"><?= gb2_h($title) ?></div>
            <div class="entry-copy">
              Reward:
              <?php if ($rewardCents > 0): ?>
                <?= gb2_h(gb2_money($rewardCents)) ?>
              <?php else: ?>
                no cash reward configured
              <?php endif; ?>
            </div>
          </div>
          <div class="status <?= gb2_h($status) ?>"><?= gb2_h($status) ?></div>
        </div>

        <?php if ($status === 'available'): ?>
          <form method="post" action="/api/claim_bonus.php" style="margin-top:12px">
            <input type="hidden" name="_csrf" value="<?= gb2_h(gb2_csrf_token()) ?>">
            <input type="hidden" name="instance_id" value="<?= (int)$instId ?>">
            <button class="btn ok block" type="submit">Claim this bonus</button>
          </form>

        <?php elseif ($status === 'claimed' && $claimedBy === $kidId): ?>
          <div class="entry-copy" style="margin-top:12px">Choose a photo source below. The proof submits automatically after you pick a photo.</div>

          <form method="post" action="/api/submit_proof.php" enctype="multipart/form-data" style="margin-top:12px">
            <input type="hidden" name="_csrf" value="<?= gb2_h(gb2_csrf_token()) ?>">
            <input type="hidden" name="kind" value="bonus">
            <input type="hidden" name="week_start" value="<?= gb2_h($weekStart) ?>">
            <input type="hidden" name="instance_id" value="<?= (int)$instId ?>">

            <div class="photo-actions two">
              <label class="upload-btn primary block">
                Take photo
                <input type="file" name="photo_camera" accept="image/*" capture="environment" onchange="this.form.submit()">
              </label>
              <label class="upload-btn block">
                Choose photo
                <input type="file" name="photo_library" accept="image/*" onchange="this.form.submit()">
              </label>
            </div>
          </form>

          <form method="post" action="/api/submit_proof.php" style="margin-top:10px">
            <input type="hidden" name="_csrf" value="<?= gb2_h(gb2_csrf_token()) ?>">
            <input type="hidden" name="kind" value="bonus">
            <input type="hidden" name="week_start" value="<?= gb2_h($weekStart) ?>">
            <input type="hidden" name="instance_id" value="<?= (int)$instId ?>">
            <input type="hidden" name="no_photo" value="1">
            <button class="btn ghost block" type="submit">Verify without photo</button>
          </form>
        <?php elseif ($status === 'claimed'): ?>
          <div class="entry-copy" style="margin-top:12px">This bonus has already been claimed.</div>
        <?php elseif ($status === 'pending'): ?>
          <div class="entry-copy" style="margin-top:12px">Your proof is waiting for parent/guardian review.</div>
        <?php elseif ($status === 'approved'): ?>
          <div class="entry-copy" style="margin-top:12px">Approved. Nice work.</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php gb2_nav('bonuses'); gb2_page_end(); ?>
