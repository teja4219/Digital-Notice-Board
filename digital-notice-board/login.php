<?php
// login.php — Administrator Login

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

// Already logged in → redirect to dashboard
if (isLoggedIn()) {
    redirect('/digital-notice-board/admin/dashboard.php');
}

$error    = '';
$email    = '';
$redirect = htmlspecialchars($_GET['redirect'] ?? '/digital-notice-board/admin/dashboard.php', ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email']= $admin['email'];

            redirect('/digital-notice-board/admin/dashboard.php');
        } else {
            $error = 'Invalid email or password. Please try again.';
            // Small delay to slow brute-force
            sleep(1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/digital-notice-board/assets/css/style.css">
</head>
<body class="auth-page">

<div class="auth-card">
  <!-- Logo -->
  <div class="auth-logo">
    <div class="auth-logo-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
    </div>
    <div class="auth-logo-text">
      <h2><?= APP_NAME ?></h2>
      <span>Administrator Portal</span>
    </div>
  </div>

  <h3>Welcome back</h3>
  <p class="auth-sub">Sign in to manage institutional notices.</p>

  <?php if ($error): ?>
    <div class="alert alert-error" role="alert">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="" class="validate-form" novalidate>
    <?= csrfField() ?>

    <div class="form-group">
      <label for="email">Email Address <span class="required">*</span></label>
      <input type="email" id="email" name="email" class="form-control"
             value="<?= e($email) ?>" placeholder="admin@institution.edu"
             required autocomplete="username">
      <div class="invalid-feedback">Please enter a valid email address.</div>
    </div>

    <div class="form-group">
      <label for="password">Password <span class="required">*</span></label>
      <div class="input-group">
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Your password" required autocomplete="current-password">
        <button type="button" class="input-group-toggle" aria-label="Toggle password visibility">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="invalid-feedback">Please enter your password.</div>
    </div>

    <button type="submit" class="btn btn-primary w-full btn-lg" style="margin-top:8px">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
      Sign In
    </button>
  </form>

  <p class="mt-4 text-small" style="text-align:center">
    <a href="/digital-notice-board/" style="color:var(--gray-500)">
      ← Return to public notice board
    </a>
  </p>
</div>

<script src="/digital-notice-board/assets/js/script.js"></script>
</body>
</html>
