<?php
// notice.php — Single Notice Detail Page

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$id  = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(404);
    redirect('/digital-notice-board/');
}

// Only fetch published, non-expired notices for public view
// Admins can preview any notice
$where = isLoggedIn()
    ? 'WHERE n.id = :id'
    : "WHERE n.id = :id AND n.status = 'published' AND (n.expires_at IS NULL OR n.expires_at > NOW())";

$stmt = $pdo->prepare("
    SELECT n.*, a.name AS author_name
    FROM notices n
    LEFT JOIN admins a ON n.created_by = a.id
    $where
");
$stmt->execute([':id' => $id]);
$notice = $stmt->fetch();

if (!$notice) {
    http_response_code(404);
    $pageTitle = '404 — Notice Not Found';
    $isAdmin   = false;
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container notice-detail"><div class="empty-state" style="padding:80px 20px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:64px;height:64px;margin:0 auto 16px;opacity:.3"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <h3>Notice Not Found</h3>
        <p>This notice may have expired or been removed.</p>
        <a href="/digital-notice-board/" class="btn btn-primary mt-4">Back to Board</a>
    </div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $notice['title'];
$isAdmin   = false;
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
  <div class="container">
    <div class="notice-detail">
      <a class="back-link" href="/digital-notice-board/">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Notice Board
      </a>

      <article class="detail-card">
        <!-- Header -->
        <div class="detail-header">
          <div class="detail-badges">
            <?php if ($notice['is_pinned']): ?>
              <span class="card-pinned">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                Pinned
              </span>
            <?php endif; ?>
            <span class="badge <?= priorityClass($notice['priority']) ?>"><?= priorityLabel($notice['priority']) ?></span>
            <span class="badge-cat"><?= e(categoryLabel($notice['category'])) ?></span>
            <?php if (isExpired($notice['expires_at'])): ?>
              <span class="badge badge-expired" style="background:#fee2e2;color:#b91c1c">Expired</span>
            <?php endif; ?>
          </div>
          <h1><?= e($notice['title']) ?></h1>
          <div class="detail-meta">
            <span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Published: <?= formatDate($notice['published_at'] ?? $notice['created_at']) ?>
            </span>
            <?php if ($notice['expires_at']): ?>
            <span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Expires: <?= formatDate($notice['expires_at']) ?>
            </span>
            <?php endif; ?>
            <?php if ($notice['author_name']): ?>
            <span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Posted by: <?= e($notice['author_name']) ?>
            </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Body -->
        <div class="detail-body">
          <?= nl2br(e($notice['description'])) ?>
        </div>

        <!-- Attachment -->
        <?php if ($notice['attachment_path']): ?>
        <div class="detail-attachment">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gray-400)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
          <a class="attachment-btn"
             href="/digital-notice-board/uploads/<?= e(basename($notice['attachment_path'])) ?>"
             target="_blank" rel="noopener noreferrer" download>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download Attachment (PDF)
          </a>
          <span class="text-muted text-small">PDF document</span>
        </div>
        <?php endif; ?>

      </article>

      <?php if (isLoggedIn()): ?>
      <div class="mt-4 flex-center gap-2" style="justify-content:flex-start">
        <a href="/digital-notice-board/admin/edit-notice.php?id=<?= (int)$notice['id'] ?>" class="btn btn-outline btn-sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Notice
        </a>
        <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-sm">Manage All Notices</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
