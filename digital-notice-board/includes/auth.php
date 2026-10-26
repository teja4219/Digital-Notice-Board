<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require the user to be logged in as admin.
 * Redirects to login page if not authenticated.
 */
function requireAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: /digital-notice-board/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Check if current session is authenticated.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

/**
 * Return current admin's name from session.
 */
function adminName(): string {
    return htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
}
