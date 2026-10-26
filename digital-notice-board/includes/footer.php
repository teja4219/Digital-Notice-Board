<?php
// includes/footer.php
$isAdmin = $isAdmin ?? false;
$base    = '/digital-notice-board';
?>
<?php if ($isAdmin): ?>
</div><!-- .admin-wrapper -->
<?php else: ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-brand">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    <span><?= e(APP_NAME) ?></span>
                </div>
                <p>A centralized digital platform for institutional announcements, academic schedules, examinations, and events.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= $base ?>/">Notice Board</a></li>
                    <li><a href="<?= $base ?>/?category=examination">Examinations</a></li>
                    <li><a href="<?= $base ?>/?category=event">Events</a></li>
                    <li><a href="<?= $base ?>/?category=placement">Placements</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <ul>
                    <li><a href="<?= $base ?>/?category=general">General</a></li>
                    <li><a href="<?= $base ?>/?category=academic">Academic</a></li>
                    <li><a href="<?= $base ?>/?category=holiday">Holidays</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <address>
                    <p>📍 Institution Campus, City - 000 000</p>
                    <p>📧 <a href="mailto:admin@institution.edu">admin@institution.edu</a></p>
                    <p>📞 +91 00000 00000</p>
                </address>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
            <p><a href="<?= $base ?>/login.php">Administrator Login</a></p>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="<?= $base ?>/assets/js/script.js"></script>
</body>
</html>
