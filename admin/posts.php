<?php
/**
 * admin/posts.php
 *
 * Administrator's blog post management page.
 * Displays all posts in a responsive table with actions.
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

// --- Handle delete action (POST only) ---
$deleteMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $deleteMessage = 'Invalid security token. Please try again.';
    } else {
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
        if ($postId > 0) {
            // Begin transaction to ensure consistency
            db()->beginTransaction();
            try {
                // Fetch cover image and gallery images to delete files
                $cover = db_fetch_column('SELECT cover_image FROM posts WHERE id = :id', ['id' => $postId]);
                $gallery = db_fetch_all('SELECT image_path FROM gallery_images WHERE post_id = :id', ['id' => $postId]);

                // Delete the post (cascading will delete post_tags and gallery_images)
                $deleted = db_delete('posts', 'id = :id', ['id' => $postId]);

                if ($deleted) {
                    // Delete cover image file if exists
                    if ($cover) {
                        $coverPath = __DIR__ . '/../' . $cover;
                        if (file_exists($coverPath)) {
                            unlink($coverPath);
                        }
                    }
                    // Delete gallery image files
                    foreach ($gallery as $row) {
                        $galleryPath = __DIR__ . '/../' . $row['image_path'];
                        if (file_exists($galleryPath)) {
                            unlink($galleryPath);
                        }
                    }
                    db()->commit();
                    $deleteMessage = 'Post deleted successfully.';
                } else {
                    db()->rollBack();
                    $deleteMessage = 'Post could not be deleted.';
                }
            } catch (PDOException $e) {
                db()->rollBack();
                db_log_error('Failed to delete post', ['post_id' => $postId, 'error' => $e->getMessage()]);
                $deleteMessage = 'Database error occurred. Post not deleted.';
            }
        } else {
            $deleteMessage = 'Invalid post ID.';
        }
    }
}

// --- Pagination ---
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// --- Total posts count ---
$totalPosts = (int) db_fetch_column('SELECT COUNT(*) FROM posts');
$totalPages = ceil($totalPosts / $perPage);

// --- Fetch posts for current page ---
$posts = [];
try {
    $posts = db_fetch_all(
        "SELECT p.*, c.name as category_name 
         FROM posts p
         LEFT JOIN categories c ON p.category_id = c.id
         ORDER BY p.created_at DESC
         LIMIT :limit OFFSET :offset",
        ['limit' => $perPage, 'offset' => $offset]
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch posts', ['error' => $e->getMessage()]);
}

// --- CSRF token for delete forms ---
$csrfToken = admin_csrf_token();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Posts · IrtiJa Admin</title>
    <link rel="icon" type="image/png" href="../irtija.png" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Admin CSS -->
    <link rel="stylesheet" href="assets/admin.css" />

    <style>
        /* Minimal fallback styles (should be in admin.css) */
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
        .admin-wrapper { display: flex; min-height: 100vh; }
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
            border: 2px solid rgba(212,168,83,0.20);
        }
        .admin-sidebar .brand .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
        }
        .admin-sidebar .brand .brand-name .gold { color: #D4A853; }
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
        .admin-sidebar .nav-link i { width: 20px; text-align: center; font-size: 1rem; }
        .admin-sidebar .nav-link:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .admin-sidebar .nav-link.active { background: rgba(212,168,83,0.12); color: #D4A853; font-weight: 600; }
        .admin-sidebar .nav-link.logout {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.04);
            padding-top: 1rem;
            color: rgba(255,255,255,0.40);
        }
        .admin-sidebar .nav-link.logout:hover { background: rgba(196,74,74,0.10); color: #FF6B6B; }
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
            border-bottom: 1px solid rgba(213,207,196,0.20);
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
        .admin-header .user-info i { font-size: 1.2rem; color: #D4A853; }
        .message {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .message.success { background: rgba(43,140,110,0.06); border: 1px solid rgba(43,140,110,0.15); color: #2B8C6E; }
        .message.error { background: rgba(196,74,74,0.06); border: 1px solid rgba(196,74,74,0.15); color: #B44A4A; }

        .table-wrapper { overflow-x: auto; background: #FCFAF5; border-radius: 16px; border: 1px solid rgba(213,207,196,0.20); padding: 0.5rem 0; }
        .posts-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            min-width: 700px;
        }
        .posts-table th {
            text-align: left;
            padding: 0.8rem 1rem;
            font-weight: 600;
            color: #4A4A4A;
            border-bottom: 2px solid rgba(213,207,196,0.20);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .posts-table td {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid rgba(213,207,196,0.08);
            vertical-align: middle;
        }
        .posts-table tr:last-child td { border-bottom: none; }
        .posts-table .cover-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            background: #f0ede5;
            border: 1px solid rgba(213,207,196,0.15);
        }
        .posts-table .cover-placeholder {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0ede5;
            border-radius: 6px;
            color: #B0B0B0;
            font-size: 1.2rem;
            border: 1px solid rgba(213,207,196,0.15);
        }
        .post-status {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .post-status.published { background: rgba(43,140,110,0.08); color: #2B8C6E; }
        .post-status.draft { background: rgba(212,168,83,0.08); color: #B8923A; }
        .actions {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        .actions a, .actions button {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .actions .btn-edit { background: rgba(26,122,116,0.08); color: #1A7A74; }
        .actions .btn-edit:hover { background: #1A7A74; color: #fff; }
        .actions .btn-view { background: rgba(212,168,83,0.08); color: #B8923A; }
        .actions .btn-view:hover { background: #D4A853; color: #fff; }
        .actions .btn-delete { background: rgba(196,74,74,0.08); color: #B44A4A; }
        .actions .btn-delete:hover { background: #C44A4A; color: #fff; }

        .pagination {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            color: #4A4A4A;
            background: #FCFAF5;
            border: 1px solid rgba(213,207,196,0.20);
            min-width: 40px;
            transition: all 0.2s ease;
        }
        .pagination a:hover { background: #004643; color: #fff; border-color: #004643; }
        .pagination .active { background: #004643; color: #fff; border-color: #004643; }
        .pagination .disabled { opacity: 0.5; pointer-events: none; }

        .empty-state {
            text-align: center;
            padding: 3rem 0;
            color: #B0B0B0;
        }
        .empty-state i { font-size: 2.5rem; color: rgba(213,207,196,0.30); margin-bottom: 0.5rem; display: block; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.5rem;
            background: linear-gradient(135deg, #004643, #1A7A74);
            color: #fff;
            border: none;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,70,67,0.20); }

        @media (max-width: 1024px) {
            .admin-sidebar { width: 220px; padding: 1.5rem 1rem; }
            .admin-main { padding: 1.5rem; max-width: calc(100% - 220px); }
        }
        @media (max-width: 768px) {
            .admin-wrapper { flex-direction: column; }
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
            .admin-sidebar .brand { margin-bottom: 0; flex: 1; }
            .admin-sidebar .nav-section {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.25rem;
                width: 100%;
                margin-top: 0.5rem;
            }
            .admin-sidebar .nav-heading { display: none; }
            .admin-sidebar .nav-link { padding: 0.4rem 0.7rem; font-size: 0.8rem; }
            .admin-sidebar .nav-link.logout { margin-top: 0; border-top: none; padding-top: 0; margin-left: auto; }
            .admin-main { padding: 1rem; max-width: 100%; }
            .admin-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .admin-header h1 { font-size: 1.6rem; }
            .posts-table { font-size: 0.8rem; min-width: 500px; }
        }
        @media (max-width: 480px) {
            .posts-table { min-width: 400px; }
            .actions a, .actions button { font-size: 0.65rem; padding: 0.15rem 0.4rem; }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar" role="navigation" aria-label="Admin navigation">
        <a href="index.php" class="brand" aria-label="IrtiJa Admin">
            <img src="../logo.png" alt="IrtiJa Logo" />
            <span class="brand-name">Irti<span class="gold">Ja</span></span>
        </a>
        <div class="nav-section">
            <div class="nav-heading">Main</div>
            <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="posts.php" class="nav-link active"><i class="fas fa-newspaper"></i> Posts</a>
            <a href="create-post.php" class="nav-link"><i class="fas fa-plus-circle"></i> New Post</a>
            <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="media.php" class="nav-link"><i class="fas fa-images"></i> Media</a>
            <div class="nav-heading">Account</div>
            <a href="logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" role="main">

        <div class="admin-header">
            <h1>Posts</h1>
            <div>
                <span class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars(admin_get_username() ?? 'Admin'); ?>
                </span>
                &nbsp;
                <a href="create-post.php" class="btn-primary"><i class="fas fa-plus"></i> New Post</a>
            </div>
        </div>

        <?php if ($deleteMessage): ?>
            <div class="message <?php echo strpos($deleteMessage, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($deleteMessage); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <i class="fas fa-pen-fancy"></i>
                <p>No posts yet. <a href="create-post.php" style="color:#1A7A74;font-weight:600;">Create your first post</a></p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($post['cover_image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($post['cover_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="cover-thumb" loading="lazy" />
                                    <?php else: ?>
                                        <div class="cover-placeholder"><i class="fas fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                    <div style="font-size:0.75rem;color:#7A7A7A;"><?php echo htmlspecialchars($post['slug']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($post['display_date'] ?? ''); ?></td>
                                <td>
                                    <span class="post-status <?php echo $post['status'] === 'published' ? 'published' : 'draft'; ?>">
                                        <?php echo htmlspecialchars($post['status'] ?? 'draft'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($post['updated_at'] ?? $post['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="edit.php?id=<?php echo (int)$post['id']; ?>" class="btn-edit" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="../blog-detail.php?slug=<?php echo urlencode($post['slug']); ?>" target="_blank" class="btn-view" title="View"><i class="fas fa-eye"></i> View</a>
                                        <form method="POST" action="posts.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>" />
                                            <input type="hidden" name="post_id" value="<?php echo (int)$post['id']; ?>" />
                                            <input type="hidden" name="action" value="delete" />
                                            <button type="submit" class="btn-delete" title="Delete"><i class="fas fa-trash-alt"></i> Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php else: ?>
                        <span class="disabled"><i class="fas fa-chevron-right"></i></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </main>

</div>

<!-- Admin JavaScript (if needed) -->
<script src="assets/admin.js"></script>

</body>
</html>
