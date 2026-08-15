<?php
/**
 * admin/media.php
 *
 * Media management for the IrtiJa admin panel.
 * Displays uploaded cover and gallery images with file information and deletion.
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

// --- CSRF token ---
$csrfToken = admin_csrf_token();

// --- Handle delete action (POST only) ---
$deleteMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $deleteMessage = 'Invalid security token. Please try again.';
    } else {
        $type = $_POST['type'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $filePath = $_POST['file_path'] ?? '';

        if ($id < 1 || empty($filePath)) {
            $deleteMessage = 'Invalid media identifier.';
        } else {
            // Security: Validate that the file path is within the uploads directory
            $fullPath = __DIR__ . '/../' . $filePath;
            $realFullPath = realpath($fullPath);
            $uploadsDir = realpath(__DIR__ . '/../uploads/');
            if (!$realFullPath || !$uploadsDir || strpos($realFullPath, $uploadsDir) !== 0) {
                $deleteMessage = 'Invalid file path.';
            } else {
                // Determine type and delete accordingly
                try {
                    db()->beginTransaction();

                    if ($type === 'cover') {
                        // Cover image: update post to remove cover_image and delete file
                        // Check if the post still has this cover
                        $post = db_fetch_one(
                            'SELECT id, title, cover_image FROM posts WHERE id = :id AND cover_image = :path',
                            ['id' => $id, 'path' => $filePath]
                        );
                        if (!$post) {
                            throw new Exception('Cover image not found or already removed.');
                        }
                        // Update post: set cover_image to null
                        db_update('posts', ['cover_image' => null], 'id = :id', ['id' => $id]);
                        // Delete file
                        if (file_exists($realFullPath) && is_file($realFullPath)) {
                            unlink($realFullPath);
                        }
                        $deleteMessage = 'Cover image removed from post "' . htmlspecialchars($post['title']) . '".';
                    } elseif ($type === 'gallery') {
                        // Gallery image: delete the gallery record and file
                        $gallery = db_fetch_one(
                            'SELECT id, image_path, post_id FROM gallery_images WHERE id = :id AND image_path = :path',
                            ['id' => $id, 'path' => $filePath]
                        );
                        if (!$gallery) {
                            throw new Exception('Gallery image not found.');
                        }
                        // Delete gallery record
                        db_delete('gallery_images', 'id = :id', ['id' => $id]);
                        // Delete file
                        if (file_exists($realFullPath) && is_file($realFullPath)) {
                            unlink($realFullPath);
                        }
                        $deleteMessage = 'Gallery image deleted.';
                    } else {
                        throw new Exception('Invalid media type.');
                    }

                    db()->commit();
                } catch (Exception $e) {
                    db()->rollBack();
                    $deleteMessage = 'Error: ' . $e->getMessage();
                }
            }
        }
    }
}

// --- Fetch media files ---

// 1. Cover images from posts
$coverImages = [];
try {
    $coverImages = db_fetch_all(
        "SELECT id, title, cover_image as file_path, 'cover' as type 
         FROM posts 
         WHERE cover_image IS NOT NULL AND cover_image != ''"
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch cover images for media', ['error' => $e->getMessage()]);
}

// 2. Gallery images
$galleryImages = [];
try {
    $galleryImages = db_fetch_all(
        "SELECT g.id, g.image_path as file_path, 'gallery' as type, g.post_id, p.title as post_title
         FROM gallery_images g
         LEFT JOIN posts p ON g.post_id = p.id
         ORDER BY g.id DESC"
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch gallery images for media', ['error' => $e->getMessage()]);
}

// Combine and sort by file path or id
$mediaItems = array_merge($coverImages, $galleryImages);
// Sort by file path (or you can sort by id, but we'll keep as is)
usort($mediaItems, function($a, $b) {
    return strcmp($a['file_path'], $b['file_path']);
});

// --- Calculate storage usage ---
$totalSize = 0;
$totalFiles = 0;
$uploadDir = __DIR__ . '/../uploads/';
$coverDir = $uploadDir . 'covers/';
$galleryDir = $uploadDir . 'gallery/';

// Function to get directory size recursively
function getDirectorySize($dir) {
    $size = 0;
    if (is_dir($dir)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    }
    return $size;
}

$totalSize = getDirectorySize($uploadDir);
$totalFiles = 0;
if (is_dir($coverDir)) {
    $totalFiles += count(glob($coverDir . '*'));
}
if (is_dir($galleryDir)) {
    $totalFiles += count(glob($galleryDir . '*'));
}
$totalSizeFormatted = $totalSize > 0 ? number_format($totalSize / 1024 / 1024, 2) . ' MB' : '0 MB';
$totalFilesFormatted = $totalFiles;

// --- Helper to get file size ---
function getFileSize($filePath) {
    $fullPath = __DIR__ . '/../' . $filePath;
    if (file_exists($fullPath) && is_file($fullPath)) {
        $size = filesize($fullPath);
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return number_format($size / 1024, 1) . ' KB';
        } else {
            return number_format($size / 1048576, 1) . ' MB';
        }
    }
    return 'N/A';
}

// --- Helper to get thumbnail URL ---
function getThumbnailUrl($filePath) {
    // For covers and gallery, they are in uploads/...
    return '../' . $filePath;
}

// --- Helper to get post association ---
function getPostTitle($item) {
    if ($item['type'] === 'cover') {
        return $item['title'] ?? 'Unknown post';
    } elseif ($item['type'] === 'gallery') {
        return $item['post_title'] ?? 'Orphaned (post deleted)';
    }
    return '';
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Media · IrtiJa Admin</title>
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
        /* Fallback styles (should be in admin.css) */
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
        .admin-sidebar .nav-section { display: flex; flex-direction: column; gap: 0.25rem; }
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
        .message.success {
            background: rgba(43,140,110,0.06);
            border: 1px solid rgba(43,140,110,0.15);
            color: #2B8C6E;
        }
        .message.error {
            background: rgba(196,74,74,0.06);
            border: 1px solid rgba(196,74,74,0.15);
            color: #B44A4A;
        }
        .message ul { padding-left: 1.5rem; margin: 0.5rem 0 0; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #FCFAF5;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid rgba(213,207,196,0.20);
            box-shadow: 0 2px 8px rgba(0,70,67,0.02);
        }
        .stat-card .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #004643;
            line-height: 1.2;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #7A7A7A;
        }
        .stat-card .stat-icon {
            float: right;
            font-size: 1.2rem;
            color: rgba(212,168,83,0.15);
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.25rem;
        }
        .media-item {
            background: #FCFAF5;
            border-radius: 12px;
            border: 1px solid rgba(213,207,196,0.20);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,70,67,0.02);
            transition: transform 0.2s ease;
        }
        .media-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,70,67,0.04);
        }
        .media-item .media-thumb {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            background: #f0ede5;
        }
        .media-item .media-info {
            padding: 0.75rem 1rem;
        }
        .media-item .media-info .media-filename {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1A1A1A;
            word-break: break-all;
            margin-bottom: 0.25rem;
        }
        .media-item .media-info .media-meta {
            font-size: 0.75rem;
            color: #7A7A7A;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .media-item .media-info .media-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .media-item .media-info .media-meta i {
            width: 16px;
            color: #B0B0B0;
        }
        .media-item .media-actions {
            padding: 0.5rem 1rem 0.75rem;
            border-top: 1px solid rgba(213,207,196,0.10);
            display: flex;
            justify-content: flex-end;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-danger {
            background: rgba(196,74,74,0.08);
            color: #B44A4A;
        }
        .btn-danger:hover {
            background: #C44A4A;
            color: #fff;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 0;
            color: #B0B0B0;
        }
        .empty-state i {
            font-size: 3rem;
            color: rgba(213,207,196,0.30);
            margin-bottom: 0.5rem;
            display: block;
        }

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
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .media-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        }
        @media (max-width: 480px) {
            .media-grid { grid-template-columns: 1fr 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .stat-card .stat-number { font-size: 1.2rem; }
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
            <a href="posts.php" class="nav-link"><i class="fas fa-newspaper"></i> Posts</a>
            <a href="create-post.php" class="nav-link"><i class="fas fa-plus-circle"></i> New Post</a>
            <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="media.php" class="nav-link active"><i class="fas fa-images"></i> Media</a>
            <div class="nav-heading">Account</div>
            <a href="logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" role="main">

        <div class="admin-header">
            <h1>Media Library</h1>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars(admin_get_username() ?? 'Admin'); ?>
            </div>
        </div>

        <?php if ($deleteMessage): ?>
            <div class="message <?php echo strpos($deleteMessage, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($deleteMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-images"></i></div>
                <div class="stat-number"><?php echo $totalFilesFormatted; ?></div>
                <div class="stat-label">Total Media Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-database"></i></div>
                <div class="stat-number"><?php echo $totalSizeFormatted; ?></div>
                <div class="stat-label">Storage Used</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-image"></i></div>
                <div class="stat-number"><?php echo count($coverImages); ?></div>
                <div class="stat-label">Cover Images</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-th"></i></div>
                <div class="stat-number"><?php echo count($galleryImages); ?></div>
                <div class="stat-label">Gallery Images</div>
            </div>
        </div>

        <!-- Media Grid -->
        <?php if (empty($mediaItems)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>No media files uploaded yet. <br />Upload images when creating or editing a post.</p>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($mediaItems as $item): ?>
                    <?php
                        $filePath = $item['file_path'];
                        $thumbnail = getThumbnailUrl($filePath);
                        $fileSize = getFileSize($filePath);
                        $postTitle = getPostTitle($item);
                        $filename = basename($filePath);
                        $typeLabel = $item['type'] === 'cover' ? 'Cover' : 'Gallery';
                    ?>
                    <div class="media-item">
                        <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($filename); ?>" class="media-thumb" loading="lazy" />
                        <div class="media-info">
                            <div class="media-filename" title="<?php echo htmlspecialchars($filename); ?>">
                                <?php echo htmlspecialchars(strlen($filename) > 20 ? substr($filename, 0, 20) . '…' : $filename); ?>
                            </div>
                            <div class="media-meta">
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($typeLabel); ?></span>
                                <span><i class="fas fa-weight"></i> <?php echo htmlspecialchars($fileSize); ?></span>
                                <span><i class="fas fa-pen"></i> <?php echo htmlspecialchars($postTitle); ?></span>
                            </div>
                        </div>
                        <div class="media-actions">
                            <form method="POST" action="media.php" onsubmit="return confirm('Are you sure you want to delete this media file?');">
                                <?php echo admin_csrf_field(); ?>
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="type" value="<?php echo $item['type']; ?>" />
                                <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>" />
                                <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($filePath); ?>" />
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

</div>

<!-- Admin JavaScript -->
<script src="assets/admin.js"></script>

</body>
</html>
