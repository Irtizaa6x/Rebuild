<?php
/**
 * admin/dashboard.php
 *
 * Main administrator dashboard for the IrtiJa admin panel.
 * Displays blog statistics, recent posts, and quick action links.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Define admin context ---
define('IRTIJA_ADMIN', true);

// --- Include required files ---
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// --- Require authentication ---
admin_require_login();

// --- Fetch statistics ---
$totalPosts = 0;
$publishedPosts = 0;
$draftPosts = 0;
$totalCategories = 0;
$recentPosts = [];

try {
    $totalPosts = (int) db_fetch_column('SELECT COUNT(*) FROM posts');
    $publishedPosts = (int) db_fetch_column("SELECT COUNT(*) FROM posts WHERE status = 'published'");
    $draftPosts = (int) db_fetch_column("SELECT COUNT(*) FROM posts WHERE status = 'draft'");
    $totalCategories = (int) db_fetch_column('SELECT COUNT(*) FROM categories');

    // Fetch recent posts (5 most recent by created_at)
    $recentPosts = db_fetch_all(
        "SELECT id, title, slug, status, created_at 
         FROM posts 
         ORDER BY created_at DESC 
         LIMIT 5"
    );
} catch (PDOException $e) {
    // Log error but continue with zeros
    db_log_error('Dashboard stats query failed', ['error' => $e->getMessage()]);
}

// --- CSRF token for forms (if needed later) ---
$csrfToken = admin_csrf_token();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard · IrtiJa Admin</title>
    <link rel="icon" type="image/png" href="../irtija.png" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Admin Dashboard Styles (self-contained) -->
    <style>
        /* ============================================================
           ADMIN DASHBOARD — Clean, minimal, brand-consistent
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F0EDE5;
            color: #4A4A4A;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Layout */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .admin-sidebar {
            width: 260px;
            background: #004643;
            color: rgba(255,255,255,0.80);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .admin-sidebar .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            text-decoration: none;
            color: #fff;
        }

        .admin-sidebar .brand img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(212, 168, 83, 0.20);
        }

        .admin-sidebar .brand .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
        }

        .admin-sidebar .brand .brand-name .gold {
            color: #D4A853;
        }

        .admin-sidebar .nav-section {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .admin-sidebar .nav-heading {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.30);
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .admin-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.8rem;
            border-radius: 10px;
            color: rgba(255,255,255,0.60);
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .admin-sidebar .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .admin-sidebar .nav-link:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }

        .admin-sidebar .nav-link.active {
            background: rgba(212, 168, 83, 0.12);
            color: #D4A853;
            font-weight: 600;
        }

        .admin-sidebar .nav-link.logout {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.04);
            padding-top: 1rem;
            color: rgba(255,255,255,0.40);
        }

        .admin-sidebar .nav-link.logout:hover {
            background: rgba(196, 74, 74, 0.10);
            color: #FF6B6B;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            padding: 2rem 2.5rem;
            max-width: calc(100% - 260px);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(213, 207, 196, 0.20);
        }

        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.02em;
        }

        .admin-header .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: #7A7A7A;
        }

        .admin-header .user-info i {
            font-size: 1.2rem;
            color: #D4A853;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: #FCFAF5;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(213, 207, 196, 0.20);
            box-shadow: 0 2px 12px rgba(0, 70, 67, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0, 70, 67, 0.04);
        }

        .stat-card .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 800;
            color: #004643;
            line-height: 1.1;
            margin-bottom: 0.25rem;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #7A7A7A;
        }

        .stat-card .stat-icon {
            float: right;
            font-size: 1.8rem;
            color: rgba(212, 168, 83, 0.15);
        }

        /* Recent Posts */
        .recent-posts-section {
            background: #FCFAF5;
            border-radius: 16px;
            border: 1px solid rgba(213, 207, 196, 0.20);
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 12px rgba(0, 70, 67, 0.02);
        }

        .recent-posts-section .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .recent-posts-section .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #1A1A1A;
        }

        .recent-posts-section .section-header a {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1A7A74;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .recent-posts-section .section-header a:hover {
            color: #D4A853;
        }

        .post-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .post-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid rgba(213, 207, 196, 0.10);
        }

        .post-list li:last-child {
            border-bottom: none;
        }

        .post-list .post-title {
            font-weight: 500;
            color: #1A1A1A;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .post-list .post-title:hover {
            color: #D4A853;
        }

        .post-list .post-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 0.8rem;
            color: #7A7A7A;
        }

        .post-list .post-status {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .post-status.published {
            background: rgba(43, 140, 110, 0.08);
            color: #2B8C6E;
        }

        .post-status.draft {
            background: rgba(212, 168, 83, 0.08);
            color: #B8923A;
        }

        .post-list .post-date {
            font-size: 0.75rem;
            color: #B0B0B0;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 0;
            color: #B0B0B0;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: rgba(213, 207, 196, 0.30);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 220px;
                padding: 1.5rem 1rem;
            }
            .admin-main {
                padding: 1.5rem;
                max-width: calc(100% - 220px);
            }
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                flex-direction: column;
            }
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 1rem 1.5rem;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                gap: 0.5rem;
                min-height: 60px;
            }
            .admin-sidebar .brand {
                margin-bottom: 0;
                flex: 1;
            }
            .admin-sidebar .nav-section {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.25rem;
                width: 100%;
                margin-top: 0.5rem;
            }
            .admin-sidebar .nav-heading {
                display: none;
            }
            .admin-sidebar .nav-link {
                padding: 0.4rem 0.7rem;
                font-size: 0.8rem;
            }
            .admin-sidebar .nav-link.logout {
                margin-top: 0;
                border-top: none;
                padding-top: 0;
                margin-left: auto;
            }
            .admin-main {
                padding: 1.25rem;
                max-width: 100%;
            }
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .admin-header h1 {
                font-size: 1.6rem;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            .stat-card .stat-number {
                font-size: 1.8rem;
            }
            .recent-posts-section {
                padding: 1.25rem;
            }
            .post-list li {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
            .post-list .post-meta {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 0.5rem;
            }
            .stat-card {
                padding: 1rem;
            }
            .stat-card .stat-number {
                font-size: 1.5rem;
            }
            .admin-main {
                padding: 1rem;
            }
            .admin-sidebar .brand .brand-name {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar" role="navigation" aria-label="Admin navigation">
        <a href="dashboard.php" class="brand" aria-label="IrtiJa Admin">
            <img src="../logo.png" alt="IrtiJa Logo" />
            <span class="brand-name">Irti<span class="gold">Ja</span></span>
        </a>

        <div class="nav-section">
            <div class="nav-heading">Main</div>
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="posts.php" class="nav-link"><i class="fas fa-newspaper"></i> Posts</a>
            <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="media.php" class="nav-link"><i class="fas fa-images"></i> Media</a>
            <div class="nav-heading">Account</div>
            <a href="logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" role="main">

        <div class="admin-header">
            <h1>Dashboard</h1>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars(admin_get_username() ?? 'Admin'); ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
                <div class="stat-number"><?php echo $totalPosts; ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle" style="color:#2B8C6E;"></i></div>
                <div class="stat-number"><?php echo $publishedPosts; ?></div>
                <div class="stat-label">Published</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-edit" style="color:#D4A853;"></i></div>
                <div class="stat-number"><?php echo $draftPosts; ?></div>
                <div class="stat-label">Drafts</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tags" style="color:#1A7A74;"></i></div>
                <div class="stat-number"><?php echo $totalCategories; ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>

        <!-- Recent Posts -->
        <div class="recent-posts-section">
            <div class="section-header">
                <h2>Recent Posts</h2>
                <a href="posts.php"><i class="fas fa-arrow-right"></i> View All</a>
            </div>

            <?php if (empty($recentPosts)): ?>
                <div class="empty-state">
                    <i class="fas fa-pen-fancy"></i>
                    <p>No posts yet. <a href="edit.php?action=create" style="color:#1A7A74;font-weight:600;">Create your first post</a></p>
                </div>
            <?php else: ?>
                <ul class="post-list">
                    <?php foreach ($recentPosts as $post): ?>
                        <li>
                            <a href="edit.php?id=<?php echo (int)$post['id']; ?>" class="post-title">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                            <div class="post-meta">
                                <span class="post-status <?php echo $post['status'] === 'published' ? 'published' : 'draft'; ?>">
                                    <?php echo htmlspecialchars($post['status'] ?? 'draft'); ?>
                                </span>
                                <span class="post-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Quick Action Links -->
        <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="edit.php?action=create" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;background:linear-gradient(135deg,#004643,#1A7A74);color:#fff;padding:0.6rem 1.5rem;border-radius:9999px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.2s ease;border:none;cursor:pointer;">
                <i class="fas fa-plus"></i> New Post
            </a>
            <a href="categories.php" class="btn-secondary" style="display:inline-flex;align-items:center;gap:0.5rem;background:linear-gradient(135deg,#D4A853,#B8923A);color:#fff;padding:0.6rem 1.5rem;border-radius:9999px;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all 0.2s ease;">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <a href="media.php" class="btn-outline" style="display:inline-flex;align-items:center;gap:0.5rem;background:transparent;color:#004643;padding:0.6rem 1.5rem;border-radius:9999px;text-decoration:none;font-weight:600;font-size:0.9rem;border:2px solid #004643;transition:all 0.2s ease;">
                <i class="fas fa-images"></i> Media Library
            </a>
        </div>

    </main>

</div>

<!-- Optional: CSRF token for AJAX if needed later -->
<script>
    const csrfToken = '<?php echo $csrfToken; ?>';
</script>

</body>
</html>
