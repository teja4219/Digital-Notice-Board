<?php
// includes/csrf.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate (or retrieve existing) CSRF token for the session.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field.
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate submitted CSRF token. Die on failure.
 */
function validateCsrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $submitted)) {
        http_response_code(403);
        die('<h2 style="font-family:sans-serif;text-align:center;margin-top:10vh">403 — Invalid CSRF Token</h2><p style="text-align:center"><a href="javascript:history.back()">Go back</a></p>');
    }
}
