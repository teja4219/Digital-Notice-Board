<?php
// index.php — Public Notice Board

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();

$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 9;

$results = getPublicNotices($pdo, $page, $perPage, $search, $category);
$notices = $results['rows'];
$total   = $results['total'];
$pages   = $results['pages'];

$categories = ['general', 'examination', 'academic', 'holiday', 'placement', 'event'];

$pageTitle = 'Notice Board';
$isAdmin   = false;
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ── -->
<section class="hero">
  <div class="container hero-content">
    <div class="hero-badge">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
      Official Notice Board
    </div>
    <h1>Stay Updated with<br>Institution Announcements</h1>
    <p>Browse the latest notices on examinations, events, placements, academic schedules, and more.</p>

    <form class="search-bar" method="GET" action="">
      <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
      <input type="text" name="search" id="searchInput" placeholder="Search notices by title or keyword…" value="<?= e($search) ?>" aria-label="Search notices">
      <button type="submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Search
      </button>
    </form>
  </div>
</section>

<!-- ── Main Content ── -->
<main class="main-content">
  <div class="container">

    <!-- Filter Bar -->
    <div class="filter-bar">
      <span class="filter-label">Filter:</span>
      <button class="filter-btn <?= $category === '' ? 'active' : '' ?>" data-category="">All</button>
      <?php foreach ($categories as $cat): ?>
      <button class="filter-btn <?= $category === $cat ? 'active' : '' ?>" data-category="<?= e($cat) ?>"><?= e(categoryLabel($cat)) ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Section Header -->
    <div class="section-header">
      <h2 class="section-title">
        <?= $search ? 'Search Results' : ($category ? e(categoryLabel($category)) . ' Notices' : 'All Notices') ?>
      </h2>
      <span class="text-muted text-small"><?= $total ?> notice<?= $total !== 1 ? 's' : '' ?> found</span>
    </div>

    <?php if (empty($notices)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <h3>No notices found</h3>
        <p><?= $search ? 'Try different search terms or clear the search.' : 'Check back later for new announcements.' ?></p>
        <?php if ($search || $category): ?>
          <a href="/digital-notice-board/" class="btn btn-outline mt-4">Clear Filters</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="notice-grid">
        <?php foreach ($notices as $n): ?>
        <article class="notice-card <?= $n['is_pinned'] ? 'pinned' : '' ?>">
          <div class="card-top">
            <div class="card-badges">
              <?php if ($n['is_pinned']): ?>
                <span class="card-pinned">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
                  Pinned
                </span>
              <?php endif; ?>
              <span class="badge <?= priorityClass($n['priority']) ?>"><?= priorityLabel($n['priority']) ?></span>
            </div>
            <span class="badge-cat"><?= e(categoryLabel($n['category'])) ?></span>
          </div>
          <div class="card-body">
            <h3><a href="/digital-notice-board/notice.php?id=<?= (int)$n['id'] ?>"><?= e($n['title']) ?></a></h3>
            <p class="card-excerpt"><?= e($n['description']) ?></p>
          </div>
          <div class="card-footer">
            <div class="card-meta">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <?= formatDateOnly($n['published_at'] ?? $n['created_at']) ?>
            </div>
            <a class="card-action" href="/digital-notice-board/notice.php?id=<?= (int)$n['id'] ?>">
              Read more
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Page navigation">
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
           class="<?= $page <= 1 ? 'disabled' : '' ?>" aria-label="Previous">&#8249;</a>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
             class="<?= $i === $page ? 'current' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
           class="<?= $page >= $pages ? 'disabled' : '' ?>" aria-label="Next">&#8250;</a>
      </nav>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
