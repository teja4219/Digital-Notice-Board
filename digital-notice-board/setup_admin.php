<?php
/**
 * setup_admin.php — One-time administrator account creator.
 *
 * USAGE:
 *   1. Place this file in the project root (it's already there).
 *   2. Open http://localhost/digital-notice-board/setup_admin.php in your browser.
 *   3. Fill in the form and submit to create an admin account.
 *   4. DELETE this file immediately after creating the admin!
 *
 * SECURITY: Never leave this file on a production server.
 */

// Simple protection: only allow from localhost
$allowed = ['127.0.0.1', '::1', 'localhost'];
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowed, true)) {
    http_response_code(403);
    die('Access denied. Run this script only from localhost.');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$message = '';
$type    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (!$name || !$email || !$password) {
        $message = 'All fields are required.';
        $type    = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $type    = 'error';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $type    = 'error';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
        $type    = 'error';
    } else {
        $pdo  = getPDO();
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (:name, :email, :hash)');
            $stmt->execute([':name' => $name, ':email' => $email, ':hash' => $hash]);
            $message = 'Admin account created! Email: ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '. DELETE this file now!';
            $type    = 'success';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $message = 'An admin with this email already exists.';
            } else {
                $message = 'Database error. Check your configuration.';
            }
            $type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Setup Admin — Digital Notice Board</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/digital-notice-board/assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </div>
        <div class="auth-logo-text">
            <h2>Admin Setup</h2>
            <span>One-time configuration — delete after use</span>
        </div>
    </div>

    <div class="alert alert-warning" style="margin-bottom:20px">
        ⚠️ <strong>Security Warning:</strong> Delete this file immediately after creating your admin account.
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $type ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($type !== 'success'): ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" required placeholder="Administrator Name">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required placeholder="admin@institution.edu">
        </div>
        <div class="form-group">
            <label for="password">Password (min 8 chars)</label>
            <input type="password" id="password" name="password" class="form-control" required minlength="8">
        </div>
        <div class="form-group">
            <label for="confirm">Confirm Password</label>
            <input type="password" id="confirm" name="confirm" class="form-control" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary w-full btn-lg">Create Admin Account</button>
    </form>
    <?php else: ?>
        <a href="/digital-notice-board/login.php" class="btn btn-primary w-full btn-lg">Go to Login</a>
    <?php endif; ?>
</div>
</body>
</html>
