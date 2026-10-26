<?php
// admin/dashboard.php — Admin Dashboard

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();
$pdo = getPDO();

// Stats
$total     = (int) $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
$published = (int) $pdo->query("SELECT COUNT(*) FROM notices WHERE status='published'")->fetchColumn();
$expired   = (int) $pdo->query("SELECT COUNT(*) FROM notices WHERE expires_at IS NOT NULL AND expires_at < NOW()")->fetchColumn();
$urgent    = (int) $pdo->query("SELECT COUNT(*) FROM notices WHERE priority='urgent' AND status='published'")->fetchColumn();
$draft     = (int) $pdo->query("SELECT COUNT(*) FROM notices WHERE status='draft'")->fetchColumn();

// Recent 8 notices
$recent = $pdo->query("SELECT n.*, a.name AS author_name FROM notices n LEFT JOIN admins a ON n.created_by = a.id ORDER BY n.created_at DESC LIMIT 8")->fetchAll();

$pageTitle = 'Dashboard';
$isAdmin   = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-topbar">
  <div>
    <h1>Dashboard</h1>
    <div class="breadcrumb">Welcome back, <?= adminName() ?>!</div>
  </div>
  <a href="/digital-notice-board/admin/add-notice.php" class="btn btn-primary btn-sm">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Notice
  </a>
</div>

<div class="admin-main">
  <?php renderFlash(); ?>

  <!-- Stat Cards -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon blue">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= $total ?></div>
        <div class="stat-label">Total Notices</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon green">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= $published ?></div>
        <div class="stat-label">Published</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon orange">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= $expired ?></div>
        <div class="stat-label">Expired</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon red">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= $urgent ?></div>
        <div class="stat-label">Urgent Notices</div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions mb-4">
    <a href="/digital-notice-board/admin/add-notice.php" class="btn btn-primary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Create Notice
    </a>
    <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      Manage All
    </a>
    <a href="/digital-notice-board/" target="_blank" class="btn btn-outline">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      View Public Board
    </a>
    <?php if ($draft > 0): ?>
    <a href="/digital-notice-board/admin/manage-notices.php?status=draft" class="btn btn-secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/></svg>
      <?= $draft ?> Draft<?= $draft !== 1 ? 's' : '' ?>
    </a>
    <?php endif; ?>
  </div>

  <!-- Recent Notices Table -->
  <div class="table-card">
    <div class="table-header">
      <h3>Recent Notices</h3>
      <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <?php if (empty($recent)): ?>
      <div class="empty-state" style="padding:40px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <h3>No notices yet</h3>
        <p>Create your first notice to get started.</p>
      </div>
    <?php else: ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $n): ?>
        <tr>
          <td style="max-width:260px">
            <strong style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($n['title']) ?></strong>
            <?php if ($n['is_pinned']): ?><span class="card-pinned" style="font-size:.68rem">📌 Pinned</span><?php endif; ?>
          </td>
          <td><span class="badge-cat"><?= e(categoryLabel($n['category'])) ?></span></td>
          <td><span class="badge <?= priorityClass($n['priority']) ?>"><?= e(ucfirst($n['priority'])) ?></span></td>
          <td>
            <?php
            $expired = isExpired($n['expires_at']);
            if ($expired && $n['status'] === 'published') {
                echo '<span class="status-badge status-expired">Expired</span>';
            } else {
                $cls = 'status-' . $n['status'];
                echo '<span class="status-badge ' . $cls . '">' . ucfirst($n['status']) . '</span>';
            }
            ?>
          </td>
          <td class="text-muted text-small"><?= formatDateOnly($n['created_at']) ?></td>
          <td>
            <div class="td-actions">
              <a href="/digital-notice-board/admin/edit-notice.php?id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline" title="Edit">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </a>
              <button class="btn btn-sm btn-danger" data-delete-id="<?= (int)$n['id'] ?>" data-delete-title="<?= e($n['title']) ?>" title="Delete">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
  <div class="modal">
    <h3 class="modal-title" id="deleteModalTitle">Delete Notice?</h3>
    <div class="modal-body">
      Are you sure you want to delete "<strong id="deleteTargetName"></strong>"?
      This action cannot be undone.
    </div>
    <form method="POST" action="/digital-notice-board/admin/delete-notice.php" id="deleteForm">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="">
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
        <button type="submit" class="btn btn-danger" id="confirmDelete">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
          Delete
        </button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
