<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ui.php';
require_once __DIR__ . '/../lib/rotation.php';
require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/auth.php';

gb2_db_init();
$kid = gb2_kid_require();

$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$dObj = new DateTimeImmutable($today);
$prettyDay = (new DateTimeImmutable('now'))->format('l, M j');

$items = [];
if (gb2_is_weekday($dObj)) {
  gb2_rotation_generate_for_day($today);
  $items = gb2_assignments_for_kid_day((int)$kid['kid_id'], $today);
}

gb2_page_start('Today', $kid);
?>
<div class="card">
  <div class="page-intro">
    <div class="muted-strong">Today</div>
    <div class="h1"><?= gb2_h($prettyDay) ?></div>
    <div class="h2"><?= gb2_h($today) ?></div>
  </div>

  <?php gb2_flash_render(); ?>

  <?php if (!gb2_is_weekday($dObj)): ?>
    <div class="empty-state" style="margin-top:12px">
      <div class="entry-title">Weekend mode</div>
      <div class="entry-copy">There is no base rotation today. Check Bonuses for optional jobs.</div>
    </div>
    <div class="page-actions" style="margin-top:12px">
      <a class="btn primary block" href="/app/bonuses.php">Open Bonuses</a>
    </div>
  <?php elseif (!$items): ?>
    <div class="empty-state" style="margin-top:12px">
      <div class="entry-title">No chores assigned</div>
      <div class="entry-copy">You do not have any base chores assigned for today.</div>
    </div>
  <?php else: ?>
    <div class="section-stack" style="margin-top:12px">
      <?php foreach ($items as $it): ?>
        <?php
          $slotTitle = (string)($it['slot_title'] ?? 'Chore');
          $status    = (string)($it['status'] ?? 'open');
          $slotId    = (int)($it['slot_id'] ?? 0);
        ?>
        <div class="entry-card">
          <div class="row wrap" style="justify-content:space-between; gap:12px">
            <div class="kv">
              <div class="entry-title"><?= gb2_h($slotTitle) ?></div>
              <div class="entry-copy">Submit proof with a photo, or use no-photo verification if needed.</div>
            </div>
            <div class="status <?= gb2_h($status) ?>"><?= gb2_h($status) ?></div>
          </div>

          <form method="post" action="/api/submit_proof.php" enctype="multipart/form-data" style="margin-top:12px">
            <input type="hidden" name="_csrf" value="<?= gb2_h(gb2_csrf_token()) ?>">
            <input type="hidden" name="kind" value="base">
            <input type="hidden" name="day" value="<?= gb2_h($today) ?>">
            <input type="hidden" name="slot_id" value="<?= (int)$slotId ?>">

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
            <input type="hidden" name="kind" value="base">
            <input type="hidden" name="day" value="<?= gb2_h($today) ?>">
            <input type="hidden" name="slot_id" value="<?= (int)$slotId ?>">
            <input type="hidden" name="no_photo" value="1">
            <button class="btn ghost block" type="submit">Verify without photo</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php gb2_nav('today'); gb2_page_end(); ?>
