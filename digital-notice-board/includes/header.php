<?php
// includes/header.php
// Requires $pageTitle to be set by the including file.
// Requires $isAdmin (bool) to switch between public and admin nav.

if (session_status() === PHP_SESSION_NONE) session_start();
$isAdmin   = $isAdmin   ?? false;
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME;
$base      = '/digital-notice-board';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e(APP_NAME) ?> — Official institutional notice board for announcements, examinations, and events.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= $isAdmin ? 'admin-layout' : 'public-layout' ?>">

<?php if ($isAdmin): ?>
<!-- ===== ADMIN NAV ===== -->
<nav class="admin-nav">
    <div class="admin-nav__brand">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        <span><?= e(APP_NAME) ?> <small>Admin</small></span>
    </div>
    <button class="admin-nav__toggle" id="adminNavToggle" aria-label="Toggle menu">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <ul class="admin-nav__links" id="adminNavLinks">
        <li><a href="<?= $base ?>/admin/dashboard.php" <?= str_contains($_SERVER['PHP_SELF']??'', 'dashboard') ? 'class="active"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard</a></li>
        <li><a href="<?= $base ?>/admin/manage-notices.php" <?= str_contains($_SERVER['PHP_SELF']??'', 'manage') ? 'class="active"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Manage Notices</a></li>
        <li><a href="<?= $base ?>/admin/add-notice.php" <?= str_contains($_SERVER['PHP_SELF']??'', 'add-') ? 'class="active"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            Add Notice</a></li>
        <li><a href="<?= $base ?>/" target="_blank">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            View Board</a></li>
        <li><a href="<?= $base ?>/logout.php" class="nav-logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout</a></li>
    </ul>
    <div class="admin-nav__user">
        <div class="admin-nav__avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
        <span><?= adminName() ?></span>
    </div>
</nav>
<div class="admin-wrapper">

<?php else: ?>
<!-- ===== PUBLIC NAV ===== -->
<header class="site-header">
    <nav class="site-nav container">
        <a class="site-nav__brand" href="<?= $base ?>/">
            <div class="brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            </div>
            <div class="brand-text">
                <span class="brand-name"><?= e(APP_NAME) ?></span>
                <span class="brand-sub">Official Portal</span>
            </div>
        </a>
        <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <ul class="site-nav__links" id="siteNavLinks">
            <li><a href="<?= $base ?>/" class="<?= basename($_SERVER['PHP_SELF'] ?? '') === 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= $base ?>/?category=examination">Examinations</a></li>
            <li><a href="<?= $base ?>/?category=event">Events</a></li>
            <li><a href="<?= $base ?>/?category=placement">Placements</a></li>
            <?php if (isLoggedIn()): ?>
            <li><a href="<?= $base ?>/admin/dashboard.php" class="btn btn-outline-nav">Dashboard</a></li>
            <?php else: ?>
            <li><a href="<?= $base ?>/login.php" class="btn btn-outline-nav">Admin Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<?php endif; ?>
