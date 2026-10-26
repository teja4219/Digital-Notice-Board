<?php
// admin/delete-notice.php — POST-only: delete or toggle status

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/digital-notice-board/admin/manage-notices.php');
}

validateCsrf();

$pdo    = getPDO();
$id     = (int) ($_POST['id'] ?? 0);
$action = trim($_POST['action'] ?? 'delete');

if ($id <= 0) {
    setFlash('error', 'Invalid notice ID.');
    redirect('/digital-notice-board/admin/manage-notices.php');
}

// Fetch the notice to verify it exists and to get attachment path
$stmt = $pdo->prepare('SELECT id, title, attachment_path FROM notices WHERE id = :id');
$stmt->execute([':id' => $id]);
$notice = $stmt->fetch();

if (!$notice) {
    setFlash('error', 'Notice not found.');
    redirect('/digital-notice-board/admin/manage-notices.php');
}

if ($action === 'delete') {
    // Delete the notice
    $stmt = $pdo->prepare('DELETE FROM notices WHERE id = :id');
    $stmt->execute([':id' => $id]);

    // Delete the attachment file if present
    deleteUpload($notice['attachment_path']);

    setFlash('success', 'Notice "' . $notice['title'] . '" has been deleted.');

} elseif ($action === 'toggle_status') {
    $currentStatus = trim($_POST['current_status'] ?? 'draft');
    $newStatus     = $currentStatus === 'published' ? 'draft' : 'published';
    $newPublished  = $newStatus === 'published' ? ', published_at = NOW()' : '';

    $stmt = $pdo->prepare("UPDATE notices SET status = :status$newPublished, updated_at = NOW() WHERE id = :id");
    $stmt->execute([':status' => $newStatus, ':id' => $id]);

    $label = $newStatus === 'published' ? 'published' : 'unpublished (set to draft)';
    setFlash('success', 'Notice "' . $notice['title'] . '" has been ' . $label . '.');
}

redirect('/digital-notice-board/admin/manage-notices.php');
