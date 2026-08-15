<?php
/**
 * admin/delete.php
 *
 * Securely delete a blog post and all associated images.
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

// --- Only allow POST requests ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: posts.php');
    exit;
}

// --- Validate CSRF token ---
if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
    $_SESSION['delete_error'] = 'Invalid security token. Please try again.';
    header('Location: posts.php');
    exit;
}

// --- Get and validate post ID ---
$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
if ($postId < 1) {
    $_SESSION['delete_error'] = 'Invalid post ID.';
    header('Location: posts.php');
    exit;
}

// --- Fetch the post to get image paths ---
$post = null;
try {
    $post = db_fetch_one(
        'SELECT id, title, slug, cover_image FROM posts WHERE id = :id',
        ['id' => $postId]
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch post for deletion', ['post_id' => $postId, 'error' => $e->getMessage()]);
    $_SESSION['delete_error'] = 'Database error occurred.';
    header('Location: posts.php');
    exit;
}

if (!$post) {
    $_SESSION['delete_error'] = 'Post not found.';
    header('Location: posts.php');
    exit;
}

// --- Fetch gallery images for this post ---
$galleryImages = [];
try {
    $galleryImages = db_fetch_all(
        'SELECT id, image_path FROM gallery_images WHERE post_id = :post_id',
        ['post_id' => $postId]
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch gallery for deletion', ['post_id' => $postId, 'error' => $e->getMessage()]);
    $_SESSION['delete_error'] = 'Database error occurred.';
    header('Location: posts.php');
    exit;
}

// --- Begin transaction ---
db()->beginTransaction();

try {
    // --- Delete the post (cascade will handle post_tags and gallery_images in DB) ---
    $deleted = db_delete('posts', 'id = :id', ['id' => $postId]);

    if ($deleted === 0) {
        db()->rollBack();
        $_SESSION['delete_error'] = 'Post could not be deleted.';
        header('Location: posts.php');
        exit;
    }

    // --- Delete cover image if it exists ---
    if (!empty($post['cover_image'])) {
        $coverPath = __DIR__ . '/../' . $post['cover_image'];
        // Security: Ensure the path is within the uploads directory
        $realCoverPath = realpath($coverPath);
        $uploadsDir = realpath(__DIR__ . '/../uploads/');
        if ($realCoverPath && $uploadsDir && strpos($realCoverPath, $uploadsDir) === 0) {
            if (file_exists($realCoverPath) && is_file($realCoverPath)) {
                unlink($realCoverPath);
            }
        }
    }

    // --- Delete gallery images ---
    foreach ($galleryImages as $gallery) {
        if (!empty($gallery['image_path'])) {
            $galleryPath = __DIR__ . '/../' . $gallery['image_path'];
            $realGalleryPath = realpath($galleryPath);
            $uploadsDir = realpath(__DIR__ . '/../uploads/');
            if ($realGalleryPath && $uploadsDir && strpos($realGalleryPath, $uploadsDir) === 0) {
                if (file_exists($realGalleryPath) && is_file($realGalleryPath)) {
                    unlink($realGalleryPath);
                }
            }
        }
    }

    // --- Commit transaction ---
    db()->commit();

    // --- Set success message and redirect ---
    $_SESSION['delete_success'] = 'Post "' . htmlspecialchars($post['title']) . '" deleted successfully.';
    header('Location: posts.php');
    exit;

} catch (PDOException $e) {
    // --- Rollback on error ---
    db()->rollBack();
    db_log_error('Failed to delete post', ['post_id' => $postId, 'error' => $e->getMessage()]);
    $_SESSION['delete_error'] = 'Database error occurred. Post not deleted.';
    header('Location: posts.php');
    exit;
}
