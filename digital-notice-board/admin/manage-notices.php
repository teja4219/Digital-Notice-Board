<?php
// admin/manage-notices.php — Manage All Notices (search, filter, paginate, CRUD)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();
$pdo = getPDO();

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status']   ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 15;
$offset   = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($search !== '') {
    $where[]           = "(title LIKE :search OR description LIKE :search2)";
    $params[':search']  = '%' . $search . '%';
    $params[':search2'] = '%' . $search . '%';
}
if ($category !== '') {
    $where[]           = "category = :category";
    $params[':category'] = $category;
}
if ($status !== '') {
    $where[]         = "status = :status";
    $params[':status'] = $status;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);
$countSQL = "SELECT COUNT(*) FROM notices $whereSQL";
$stmt     = $pdo->prepare($countSQL);
$stmt->execute($params);
$total    = (int) $stmt->fetchColumn();
$pages    = (int) ceil($total / $perPage);

$sql  = "SELECT n.*, a.name AS author_name FROM notices n LEFT JOIN admins a ON n.created_by = a.id $whereSQL ORDER BY n.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$notices = $stmt->fetchAll();

$pageTitle = 'Manage Notices';
$isAdmin   = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-topbar">
  <div>
    <h1>Manage Notices</h1>
    <div class="breadcrumb"><a href="/digital-notice-board/admin/dashboard.php">Dashboard</a> › Manage Notices</div>
  </div>
  <a href="/digital-notice-board/admin/add-notice.php" class="btn btn-primary btn-sm">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add Notice
  </a>
</div>

<div class="admin-main">
  <?php renderFlash(); ?>

  <div class="table-card">
    <!-- Search & Filter Bar -->
    <div class="table-header">
      <h3>All Notices <span class="text-muted text-small">(<?= $total ?>)</span></h3>
      <form method="GET" action="" class="admin-search">
        <input type="text" name="search" placeholder="Search title…" value="<?= e($search) ?>" id="searchInput">
        <select name="category" class="form-control" style="max-width:150px">
          <option value="">All Categories</option>
          <option value="general"     <?= $category==='general'     ? 'selected':'' ?>>General</option>
          <option value="examination" <?= $category==='examination' ? 'selected':'' ?>>Examination</option>
          <option value="academic"    <?= $category==='academic'    ? 'selected':'' ?>>Academic</option>
          <option value="holiday"     <?= $category==='holiday'     ? 'selected':'' ?>>Holiday</option>
          <option value="placement"   <?= $category==='placement'   ? 'selected':'' ?>>Placement</option>
          <option value="event"       <?= $category==='event'       ? 'selected':'' ?>>Event</option>
        </select>
        <select name="status" class="form-control" style="max-width:140px">
          <option value="">All Status</option>
          <option value="draft"     <?= $status==='draft'     ? 'selected':'' ?>>Draft</option>
          <option value="published" <?= $status==='published' ? 'selected':'' ?>>Published</option>
          <option value="archived"  <?= $status==='archived'  ? 'selected':'' ?>>Archived</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <?php if ($search || $category || $status): ?>
          <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-sm">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <?php if (empty($notices)): ?>
      <div class="empty-state" style="padding:48px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <h3>No notices found</h3>
        <p>Try clearing the filters or create a new notice.</p>
      </div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Pinned</th>
          <th>Published</th>
          <th>Expires</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($notices as $n): ?>
        <tr>
          <td class="text-muted text-small"><?= (int)$n['id'] ?></td>
          <td style="max-width:220px">
            <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($n['title']) ?></div>
            <div class="text-muted text-small">by <?= e($n['author_name'] ?? '—') ?></div>
          </td>
          <td><span class="badge-cat"><?= e(categoryLabel($n['category'])) ?></span></td>
          <td><span class="badge <?= priorityClass($n['priority']) ?>"><?= e(ucfirst($n['priority'])) ?></span></td>
          <td>
            <?php
            $exp = isExpired($n['expires_at']);
            if ($exp && $n['status'] === 'published') {
                echo '<span class="status-badge status-expired">Expired</span>';
            } else {
                echo '<span class="status-badge status-' . $n['status'] . '">' . ucfirst($n['status']) . '</span>';
            }
            ?>
          </td>
          <td><?= $n['is_pinned'] ? '<span title="Pinned">📌</span>' : '—' ?></td>
          <td class="text-small text-muted"><?= formatDateOnly($n['published_at']) ?></td>
          <td class="text-small text-muted <?= isExpired($n['expires_at']) ? 'text-danger' : '' ?>">
            <?= $n['expires_at'] ? formatDateOnly($n['expires_at']) : '—' ?>
          </td>
          <td>
            <div class="td-actions">
              <a href="/digital-notice-board/notice.php?id=<?= (int)$n['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="View">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a href="/digital-notice-board/admin/edit-notice.php?id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline" title="Edit">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <!-- Quick publish/unpublish toggle -->
              <form method="POST" action="/digital-notice-board/admin/delete-notice.php" style="display:inline">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                <input type="hidden" name="current_status" value="<?= e($n['status']) ?>">
                <button type="submit" class="btn btn-sm <?= $n['status']==='published' ? 'btn-warning' : 'btn-success' ?>"
                        title="<?= $n['status']==='published' ? 'Unpublish' : 'Publish' ?>">
                  <?= $n['status']==='published' ? '⏸' : '▶' ?>
                </button>
              </form>
              <button class="btn btn-sm btn-danger"
                      data-delete-id="<?= (int)$n['id'] ?>"
                      data-delete-title="<?= e($n['title']) ?>"
                      title="Delete">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div style="padding:16px 22px;border-top:1px solid var(--gray-100)">
      <nav class="pagination">
        <?php
        $baseQ = http_build_query(array_filter(['search'=>$search,'category'=>$category,'status'=>$status]));
        ?>
        <a href="?<?= $baseQ ?>&page=<?= $page - 1 ?>" class="<?= $page<=1 ? 'disabled' : '' ?>">&#8249;</a>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="?<?= $baseQ ?>&page=<?= $i ?>" class="<?= $i===$page ? 'current' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="?<?= $baseQ ?>&page=<?= $page + 1 ?>" class="<?= $page>=$pages ? 'disabled' : '' ?>">&#8250;</a>
      </nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true">
  <div class="modal">
    <h3 class="modal-title">Delete Notice?</h3>
    <div class="modal-body">
      Are you sure you want to permanently delete "<strong id="deleteTargetName"></strong>"?
      This action cannot be undone and any attached file will also be removed.
    </div>
    <form method="POST" action="/digital-notice-board/admin/delete-notice.php" id="deleteForm">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="">
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" id="cancelDelete">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
          Delete Permanently
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  .text-danger { color: var(--error) !important; }
  .btn-warning { background: var(--warning-bg); color: var(--warning); }
  .btn-warning:hover { background: var(--warning); color: var(--white); }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
