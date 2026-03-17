<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/ui.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/db.php';

gb2_db_init();
gb2_admin_require();

$pdo = gb2_pdo();

$pending = $pdo->query(
  "SELECT s.*, k.name
   FROM submissions s
   JOIN kids k ON k.id = s.kid_id
   WHERE s.status='pending'
   ORDER BY s.submitted_at ASC
   LIMIT 50"
)->fetchAll();

// Count pending by kind
$kindCounts = [];
foreach ($pending as $p) {
  $kind = (string)($p['kind'] ?? 'unknown');
  $kindCounts[$kind] = ($kindCounts[$kind] ?? 0) + 1;
}

// Count no-photo submissions
$noPhotoCount = 0;
foreach ($pending as $p) {
  if ((string)$p['photo_path'] === 'uploads/NO_PHOTO') {
    $noPhotoCount++;
  }
}

gb2_page_start('Review', null);
$tok = gb2_csrf_token();
?>

<div class="card">
  <div class="row">
    <div class="kv">
      <div class="h1">Review</div>
      <div class="h2">Approve or reject submitted proofs</div>
    </div>
  </div>

  <?php gb2_flash_render(); ?>

  <?php if (!$pending): ?>
    <div class="status approved" style="margin-top:12px">✓ Nothing pending 🎉</div>
    <div class="note" style="margin-top:10px">When a kid submits a photo, it will appear here until you approve or reject it.</div>
  <?php else: ?>
    <!-- Pending Stats -->
    <div class="summary-grid two" style="margin-top:14px">
      <div class="summary-card">
        <div class="summary-title">📋 Total Pending</div>
        <div class="kpi"><?= count($pending) ?></div>
        <div class="kpi-sub">Photos awaiting your decision</div>
      </div>
      <div class="summary-card">
        <div class="summary-title">⏱ No Photo Submissions</div>
        <div class="kpi"><?= (int)$noPhotoCount ?></div>
        <div class="kpi-sub">Approve quickly if verified</div>
      </div>
    </div>

    <!-- Submission type breakdown -->
    <?php if (!empty($kindCounts)): ?>
      <div style="margin-top:12px; padding:12px; background:rgba(255,255,255,.02); border-radius:12px; border:1px solid var(--border)">
        <div class="small" style="margin-bottom:8px">By submission type:</div>
        <div class="row" style="gap:10px; flex-wrap:wrap">
          <?php foreach ($kindCounts as $kind => $count): ?>
            <div class="badge">
              <?= gb2_h($kind) ?>: <strong><?= (int)$count ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if ($pending): ?>
<div style="margin-top:12px">
  <?php foreach ($pending as $p): ?>
    <div class="card entry-card" style="margin-bottom:12px">
      <div class="row" style="justify-content:space-between; align-items:flex-start">
        <div class="kv" style="flex:1">
          <div class="h1" style="margin-bottom:4px"><?= gb2_h((string)$p['name']) ?></div>
          <div class="small">
            📝 <?= gb2_h((string)$p['kind']) ?> • ⏰ <?= gb2_h((string)$p['submitted_at']) ?>
          </div>
        </div>
        <div class="status pending">⏳ Pending</div>
      </div>

      <div style="margin-top:10px">
        <?php if ((string)$p['photo_path'] === 'uploads/NO_PHOTO'): ?>
          <div class="note">✓ Submitted without photo</div>
        <?php else: ?>
          <a class="btn" href="/admin/photo.php?sub_id=<?= (int)$p['id'] ?>" target="_blank" rel="noopener">🖼️ View photo</a>
        <?php endif; ?>
      </div>

      <form method="post" action="/api/approve.php" style="margin-top:12px" class="grid two">
        <input type="hidden" name="_csrf" value="<?= gb2_h($tok) ?>">
        <input type="hidden" name="sub_id" value="<?= (int)$p['id'] ?>">

        <input class="input" name="note" placeholder="Optional note (for your records)">

        <div class="row" style="justify-content:flex-end; gap:10px">
          <button class="btn bad" name="decision" value="rejected" type="submit">✗ Reject</button>
          <button class="btn ok"  name="decision" value="approved" type="submit">✓ Approve</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php gb2_nav('review'); gb2_page_end(); ?>
