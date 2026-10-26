<?php
// admin/edit-notice.php — Edit Existing Notice

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();
$pdo = getPDO();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) redirect('/digital-notice-board/admin/manage-notices.php');

$stmt = $pdo->prepare('SELECT * FROM notices WHERE id = :id');
$stmt->execute([':id' => $id]);
$notice = $stmt->fetch();
if (!$notice) {
    setFlash('error', 'Notice not found.');
    redirect('/digital-notice-board/admin/manage-notices.php');
}

$errors = [];
$values = $notice; // Pre-populate

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $values['title']       = trim($_POST['title']       ?? '');
    $values['description'] = trim($_POST['description'] ?? '');
    $values['category']    = trim($_POST['category']    ?? 'general');
    $values['priority']    = trim($_POST['priority']    ?? 'normal');
    $values['status']      = trim($_POST['status']      ?? 'draft');
    $values['is_pinned']   = isset($_POST['is_pinned']) ? 1 : 0;
    $values['published_at']= trim($_POST['published_at'] ?? '');
    $values['expires_at']  = trim($_POST['expires_at']  ?? '');
    $removeAttachment      = isset($_POST['remove_attachment']);

    // Validate
    if ($values['title'] === '')       $errors[] = 'Title is required.';
    if (strlen($values['title']) > 300) $errors[] = 'Title must be 300 characters or less.';
    if ($values['description'] === '') $errors[] = 'Description is required.';

    $allowed_cats  = ['general','examination','academic','holiday','placement','event'];
    $allowed_prios = ['low','normal','high','urgent'];
    $allowed_stats = ['draft','published','archived'];
    if (!in_array($values['category'], $allowed_cats,  true)) $errors[] = 'Invalid category.';
    if (!in_array($values['priority'], $allowed_prios, true)) $errors[] = 'Invalid priority.';
    if (!in_array($values['status'],   $allowed_stats, true)) $errors[] = 'Invalid status.';

    $publishedAt = $values['published_at'] !== '' ? date('Y-m-d H:i:s', strtotime($values['published_at'])) : date('Y-m-d H:i:s');
    $expiresAt   = $values['expires_at']   !== '' ? date('Y-m-d H:i:s', strtotime($values['expires_at']))   : null;

    if ($expiresAt && $expiresAt <= $publishedAt) {
        $errors[] = 'Expiry date must be after the publication date.';
    }

    // Handle attachment
    $currentAttachment = $notice['attachment_path'];
    if ($removeAttachment) {
        deleteUpload($currentAttachment);
        $currentAttachment = null;
    }
    if (!empty($_FILES['attachment']['name'])) {
        try {
            $currentAttachment = handleUpload('attachment', $currentAttachment);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE notices SET
                title = :title, description = :description, category = :category, priority = :priority,
                attachment_path = :attachment, is_pinned = :pinned, status = :status,
                published_at = :pub, expires_at = :exp, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':title'       => $values['title'],
            ':description' => $values['description'],
            ':category'    => $values['category'],
            ':priority'    => $values['priority'],
            ':attachment'  => $currentAttachment,
            ':pinned'      => $values['is_pinned'],
            ':status'      => $values['status'],
            ':pub'         => $publishedAt,
            ':exp'         => $expiresAt,
            ':id'          => $id,
        ]);

        setFlash('success', 'Notice updated successfully.');
        redirect('/digital-notice-board/admin/manage-notices.php');
    }
}

// Format datetime-local value
function localDt(?string $dt): string {
    return $dt ? date('Y-m-d\TH:i', strtotime($dt)) : '';
}

$pageTitle = 'Edit Notice';
$isAdmin   = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-topbar">
  <div>
    <h1>Edit Notice</h1>
    <div class="breadcrumb"><a href="/digital-notice-board/admin/dashboard.php">Dashboard</a> › <a href="/digital-notice-board/admin/manage-notices.php">Manage</a> › Edit</div>
  </div>
  <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-sm">← Back to Manage</a>
</div>

<div class="admin-main">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    </div>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data" class="validate-form">
    <?= csrfField() ?>

    <div class="form-card">
      <!-- Basic Info -->
      <div class="form-section">
        <div class="form-section-title">Notice Information</div>
        <div class="form-group">
          <label for="title">Title <span class="required">*</span></label>
          <input type="text" id="title" name="title" class="form-control" required
                 maxlength="300" value="<?= e($values['title']) ?>">
          <div class="invalid-feedback">Title is required.</div>
        </div>
        <div class="form-group">
          <label for="description">Description <span class="required">*</span></label>
          <textarea id="description" name="description" class="form-control" required
                    rows="8" data-max="5000"><?= e($values['description']) ?></textarea>
          <div class="invalid-feedback">Description is required.</div>
        </div>
      </div>

      <!-- Categorization -->
      <div class="form-section">
        <div class="form-section-title">Categorization</div>
        <div class="form-row">
          <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" class="form-control">
              <option value="general"     <?= $values['category']==='general'     ? 'selected':'' ?>>General</option>
              <option value="examination" <?= $values['category']==='examination' ? 'selected':'' ?>>Examination</option>
              <option value="academic"    <?= $values['category']==='academic'    ? 'selected':'' ?>>Academic</option>
              <option value="holiday"     <?= $values['category']==='holiday'     ? 'selected':'' ?>>Holiday</option>
              <option value="placement"   <?= $values['category']==='placement'   ? 'selected':'' ?>>Placement</option>
              <option value="event"       <?= $values['category']==='event'       ? 'selected':'' ?>>Event</option>
            </select>
          </div>
          <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
              <option value="low"    <?= $values['priority']==='low'    ? 'selected':'' ?>>Low</option>
              <option value="normal" <?= $values['priority']==='normal' ? 'selected':'' ?>>Normal</option>
              <option value="high"   <?= $values['priority']==='high'   ? 'selected':'' ?>>High</option>
              <option value="urgent" <?= $values['priority']==='urgent' ? 'selected':'' ?>>Urgent</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Publication -->
      <div class="form-section">
        <div class="form-section-title">Publication Settings</div>
        <div class="form-row">
          <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
              <option value="draft"     <?= $values['status']==='draft'     ? 'selected':'' ?>>Draft</option>
              <option value="published" <?= $values['status']==='published' ? 'selected':'' ?>>Published</option>
              <option value="archived"  <?= $values['status']==='archived'  ? 'selected':'' ?>>Archived</option>
            </select>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end">
            <label class="check-label">
              <input type="checkbox" name="is_pinned" <?= $values['is_pinned'] ? 'checked' : '' ?>>
              📌 Pin this notice
            </label>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="published_at">Publish Date &amp; Time</label>
            <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                   value="<?= e(localDt($values['published_at'])) ?>">
          </div>
          <div class="form-group">
            <label for="expires_at">Expiry Date &amp; Time</label>
            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control"
                   value="<?= e(localDt($values['expires_at'])) ?>">
          </div>
        </div>
      </div>

      <!-- Attachment -->
      <div class="form-section">
        <div class="form-section-title">Attachment</div>
        <?php if ($notice['attachment_path']): ?>
          <div class="alert alert-info" style="margin-bottom:14px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            Current: <strong><?= e(basename($notice['attachment_path'])) ?></strong>
            <label class="check-label" style="display:inline-flex;margin-left:16px">
              <input type="checkbox" name="remove_attachment"> Remove attachment
            </label>
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label for="attachment">Upload New PDF <span class="text-muted">(optional, max 5 MB)</span></label>
          <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,application/pdf">
          <div class="form-hint">Uploading a new file will replace the current one.</div>
        </div>
      </div>

      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Update Notice
        </button>
        <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-lg">Cancel</a>
        <a href="/digital-notice-board/notice.php?id=<?= $id ?>" target="_blank" class="btn btn-outline btn-lg">Preview</a>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
