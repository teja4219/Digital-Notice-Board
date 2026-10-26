<?php
// admin/add-notice.php — Create New Notice

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();
$pdo = getPDO();

$errors = [];
$values = [
    'title'        => '',
    'description'  => '',
    'category'     => 'general',
    'priority'     => 'normal',
    'status'       => 'published',
    'is_pinned'    => 0,
    'published_at' => date('Y-m-d\TH:i'),
    'expires_at'   => '',
];

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

    // Validate
    if ($values['title'] === '') $errors[] = 'Title is required.';
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

    // File upload
    $attachmentPath = null;
    if (!empty($_FILES['attachment']['name'])) {
        try {
            $attachmentPath = handleUpload('attachment');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO notices (title, description, category, priority, attachment_path, is_pinned, status, published_at, expires_at, created_by, created_at, updated_at)
            VALUES (:title, :description, :category, :priority, :attachment, :pinned, :status, :pub, :exp, :creator, NOW(), NOW())
        ");
        $stmt->execute([
            ':title'       => $values['title'],
            ':description' => $values['description'],
            ':category'    => $values['category'],
            ':priority'    => $values['priority'],
            ':attachment'  => $attachmentPath,
            ':pinned'      => $values['is_pinned'],
            ':status'      => $values['status'],
            ':pub'         => $publishedAt,
            ':exp'         => $expiresAt,
            ':creator'     => $_SESSION['admin_id'],
        ]);

        setFlash('success', 'Notice "' . $values['title'] . '" has been created successfully.');
        redirect('/digital-notice-board/admin/manage-notices.php');
    }
}

$pageTitle = 'Add Notice';
$isAdmin   = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-topbar">
  <div>
    <h1>Add Notice</h1>
    <div class="breadcrumb"><a href="/digital-notice-board/admin/dashboard.php">Dashboard</a> › Add Notice</div>
  </div>
  <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-sm">← Back to Manage</a>
</div>

<div class="admin-main">
  <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div>
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
      </div>
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
                 maxlength="300" value="<?= e($values['title']) ?>" placeholder="Enter notice title…">
          <div class="invalid-feedback">Title is required.</div>
        </div>

        <div class="form-group">
          <label for="description">Description <span class="required">*</span></label>
          <textarea id="description" name="description" class="form-control" required
                    rows="8" placeholder="Enter full notice content…" data-max="5000"><?= e($values['description']) ?></textarea>
          <div class="invalid-feedback">Description is required.</div>
        </div>
      </div>

      <!-- Categorization -->
      <div class="form-section">
        <div class="form-section-title">Categorization</div>
        <div class="form-row">
          <div class="form-group">
            <label for="category">Category <span class="required">*</span></label>
            <select id="category" name="category" class="form-control" required>
              <option value="general"     <?= $values['category']==='general'     ? 'selected':'' ?>>General</option>
              <option value="examination" <?= $values['category']==='examination' ? 'selected':'' ?>>Examination</option>
              <option value="academic"    <?= $values['category']==='academic'    ? 'selected':'' ?>>Academic</option>
              <option value="holiday"     <?= $values['category']==='holiday'     ? 'selected':'' ?>>Holiday</option>
              <option value="placement"   <?= $values['category']==='placement'   ? 'selected':'' ?>>Placement</option>
              <option value="event"       <?= $values['category']==='event'       ? 'selected':'' ?>>Event</option>
            </select>
          </div>
          <div class="form-group">
            <label for="priority">Priority <span class="required">*</span></label>
            <select id="priority" name="priority" class="form-control" required>
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
            <label for="status">Status <span class="required">*</span></label>
            <select id="status" name="status" class="form-control" required>
              <option value="draft"     <?= $values['status']==='draft'     ? 'selected':'' ?>>Draft (not visible to public)</option>
              <option value="published" <?= $values['status']==='published' ? 'selected':'' ?>>Published (visible to public)</option>
              <option value="archived"  <?= $values['status']==='archived'  ? 'selected':'' ?>>Archived</option>
            </select>
          </div>
          <div class="form-group">
            <label>
              <span style="visibility:hidden">placeholder</span>
            </label>
            <label class="check-label" style="margin-top:10px">
              <input type="checkbox" name="is_pinned" <?= $values['is_pinned'] ? 'checked' : '' ?>>
              📌 Pin this notice (displays at top)
            </label>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="published_at">Publish Date &amp; Time</label>
            <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                   value="<?= e($values['published_at']) ?>">
          </div>
          <div class="form-group">
            <label for="expires_at">Expiry Date &amp; Time <span class="text-muted">(optional)</span></label>
            <input type="datetime-local" id="expires_at" name="expires_at" class="form-control"
                   value="<?= e($values['expires_at']) ?>">
            <div class="form-hint">Leave blank for no expiry.</div>
          </div>
        </div>
      </div>

      <!-- Attachment -->
      <div class="form-section">
        <div class="form-section-title">Attachment</div>
        <div class="form-group">
          <label for="attachment">PDF Attachment <span class="text-muted">(optional, max 5 MB)</span></label>
          <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,application/pdf">
          <div class="form-hint">Only PDF files allowed. Uploaded files cannot be executed.</div>
        </div>
      </div>

      <!-- Submit -->
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save Notice
        </button>
        <a href="/digital-notice-board/admin/manage-notices.php" class="btn btn-secondary btn-lg">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
