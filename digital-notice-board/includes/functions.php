<?php
// includes/functions.php

/**
 * Escape output to prevent XSS.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Return a CSS class name for a given priority.
 */
function priorityClass(string $priority): string {
    return match($priority) {
        'urgent' => 'badge-urgent',
        'high'   => 'badge-high',
        'normal' => 'badge-normal',
        'low'    => 'badge-low',
        default  => 'badge-normal',
    };
}

/**
 * Return a display label for a priority.
 */
function priorityLabel(string $priority): string {
    return match($priority) {
        'urgent' => '🔴 Urgent',
        'high'   => '🟠 High',
        'normal' => '🔵 Normal',
        'low'    => '🟢 Low',
        default  => $priority,
    };
}

/**
 * Return a display label for a category.
 */
function categoryLabel(string $category): string {
    return match($category) {
        'general'     => 'General',
        'examination' => 'Examination',
        'academic'    => 'Academic',
        'holiday'     => 'Holiday',
        'placement'   => 'Placement',
        'event'       => 'Event',
        default       => ucfirst($category),
    };
}

/**
 * Format a datetime string for display.
 */
function formatDate(?string $date): string {
    if (!$date) return '—';
    return date('d M Y, h:i A', strtotime($date));
}

/**
 * Format a date (date only).
 */
function formatDateOnly(?string $date): string {
    if (!$date) return '—';
    return date('d M Y', strtotime($date));
}

/**
 * Return true if a notice is expired.
 */
function isExpired(?string $expires_at): bool {
    if (!$expires_at) return false;
    return strtotime($expires_at) < time();
}

/**
 * Redirect to a URL.
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Set a flash message in session.
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear a flash message.
 */
function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render flash message HTML if present.
 */
function renderFlash(): void {
    $flash = getFlash();
    if ($flash) {
        $cls = $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'error' ? 'alert-error' : 'alert-info');
        echo '<div class="alert ' . $cls . '" role="alert">' . e($flash['message']) . '</div>';
    }
}

/**
 * Handle PDF file upload. Returns relative path or null.
 */
function handleUpload(string $fieldName, ?string $existingPath = null): ?string {
    if (empty($_FILES[$fieldName]['name'])) {
        return $existingPath; // No new file uploaded
    }

    $file      = $_FILES[$fieldName];
    $uploadDir = UPLOAD_DIR;

    // Validate size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('File exceeds maximum size of 5 MB.');
    }

    // Validate MIME type
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, ALLOWED_TYPES, true)) {
        throw new RuntimeException('Only PDF files are allowed.');
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        throw new RuntimeException('Only PDF files are allowed.');
    }

    // Generate safe random filename
    $newName  = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = $uploadDir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Failed to save uploaded file.');
    }

    // Delete old file if replacing
    if ($existingPath) {
        $oldFile = $uploadDir . basename($existingPath);
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }

    return $newName;
}

/**
 * Safely delete an uploaded file by its stored name.
 */
function deleteUpload(?string $filename): void {
    if (!$filename) return;
    $path = UPLOAD_DIR . basename($filename);
    if (file_exists($path)) {
        @unlink($path);
    }
}

/**
 * Fetch paginated published notices for public board.
 */
function getPublicNotices(PDO $pdo, int $page = 1, int $perPage = 10, string $search = '', string $category = ''): array {
    $offset = ($page - 1) * $perPage;
    $where  = ["n.status = 'published'", "(n.expires_at IS NULL OR n.expires_at > NOW())"];
    $params = [];

    if ($search !== '') {
        $where[]          = "(n.title LIKE :search OR n.description LIKE :search2)";
        $params[':search']  = '%' . $search . '%';
        $params[':search2'] = '%' . $search . '%';
    }
    if ($category !== '') {
        $where[]           = "n.category = :category";
        $params[':category'] = $category;
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    // Count total
    $countSQL = "SELECT COUNT(*) FROM notices n $whereSQL";
    $stmt = $pdo->prepare($countSQL);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    // Fetch rows
    $sql = "SELECT n.*, a.name AS author_name
            FROM notices n
            LEFT JOIN admins a ON n.created_by = a.id
            $whereSQL
            ORDER BY n.is_pinned DESC, n.published_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    return ['rows' => $rows, 'total' => $total, 'pages' => (int) ceil($total / $perPage)];
}
