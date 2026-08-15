<?php
/**
 * admin/edit.php
 *
 * Edit an existing blog post.
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

// --- Get post ID ---
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($postId < 1) {
    // No valid ID, redirect to posts list
    header('Location: posts.php');
    exit;
}

// --- Fetch the post ---
$post = null;
try {
    $post = db_fetch_one(
        "SELECT p.*, 
                (SELECT GROUP_CONCAT(t.name, ',') 
                 FROM post_tags pt 
                 JOIN tags t ON pt.tag_id = t.id 
                 WHERE pt.post_id = p.id) as tags_string
         FROM posts p
         WHERE p.id = :id",
        ['id' => $postId]
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch post for edit', ['id' => $postId, 'error' => $e->getMessage()]);
}

if (!$post) {
    // Post not found, redirect to posts list with error
    header('Location: posts.php?error=notfound');
    exit;
}

// --- Fetch categories for dropdown ---
$categories = [];
try {
    $categories = db_fetch_all('SELECT id, name, slug FROM categories ORDER BY name');
} catch (PDOException $e) {
    db_log_error('Failed to fetch categories for edit', ['error' => $e->getMessage()]);
}

// --- Fetch existing gallery images ---
$galleryImages = [];
try {
    $galleryImages = db_fetch_all(
        'SELECT id, image_path, sort_order FROM gallery_images WHERE post_id = :post_id ORDER BY sort_order',
        ['post_id' => $postId]
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch gallery for edit', ['post_id' => $postId, 'error' => $e->getMessage()]);
}

// --- CSRF token ---
$csrfToken = admin_csrf_token();

// --- Initialize variables ---
$errors = [];
$success = false;
$successMessage = '';

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Validate CSRF ---
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // --- Collect and sanitize input ---
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $displayDate = trim($_POST['display_date'] ?? '');
        $previewText = trim($_POST['preview_text'] ?? '');
        $content = $_POST['content'] ?? '';
        $certificateUrl = trim($_POST['certificate_url'] ?? '');
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $tagsInput = trim($_POST['tags'] ?? '');
        $status = $_POST['status'] ?? 'published';

        // --- Cover image handling ---
        $coverCroppedData = $_POST['cover_cropped_data'] ?? '';
        $coverUploaded = isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK;

        // --- Gallery handling: which existing images to keep ---
        $keepGalleryIds = isset($_POST['keep_gallery']) ? (array)$_POST['keep_gallery'] : [];
        $keepGalleryIds = array_map('intval', $keepGalleryIds);

        // --- New gallery uploads ---
        $galleryFiles = $_FILES['gallery_images'] ?? null;
        $galleryUploaded = $galleryFiles && $galleryFiles['error'][0] !== UPLOAD_ERR_NO_FILE;

        // --- Validation ---
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        if (empty($displayDate)) {
            $errors[] = 'Display date is required.';
        }
        if (empty($content)) {
            $errors[] = 'Content is required.';
        }

        // Generate slug if empty
        if (empty($slug)) {
            $slug = slugify($title);
        }

        // Check slug uniqueness (exclude current post)
        if (!empty($slug)) {
            $existing = db_fetch_column(
                'SELECT COUNT(*) FROM posts WHERE slug = :slug AND id != :id',
                ['slug' => $slug, 'id' => $postId]
            );
            if ((int)$existing > 0) {
                $counter = 1;
                $newSlug = $slug . '-' . $counter;
                while ((int)db_fetch_column('SELECT COUNT(*) FROM posts WHERE slug = :slug AND id != :id', ['slug' => $newSlug, 'id' => $postId]) > 0) {
                    $counter++;
                    $newSlug = $slug . '-' . $counter;
                }
                $slug = $newSlug;
                // We'll auto-fix
            }
        }

        // Validate certificate URL if provided
        if (!empty($certificateUrl) && !filter_var($certificateUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Certificate URL is not a valid URL.';
        }

        // Validate category
        if ($categoryId > 0) {
            $catExists = db_fetch_column('SELECT COUNT(*) FROM categories WHERE id = :id', ['id' => $categoryId]);
            if ((int)$catExists === 0) {
                $errors[] = 'Selected category does not exist.';
            }
        } else {
            // Default to uncategorized if exists
            $defaultCat = db_fetch_column("SELECT id FROM categories WHERE slug = 'uncategorized'");
            if ($defaultCat) {
                $categoryId = (int)$defaultCat;
            } else {
                // Insert uncategorized
                try {
                    db_insert('categories', ['name' => 'Uncategorized', 'slug' => 'uncategorized']);
                    $categoryId = (int)db()->getPdo()->lastInsertId();
                } catch (PDOException $e) {
                    $errors[] = 'Failed to set default category.';
                }
            }
        }

        // --- Process cover image ---
        $coverFinalPath = $post['cover_image'] ?? '';
        if ($coverUploaded) {
            $file = $_FILES['cover_image'];
            $uploadError = validateImage($file);
            if ($uploadError !== true) {
                $errors[] = 'Cover image: ' . $uploadError;
            } else {
                // If cropped data provided, use that
                if (!empty($coverCroppedData)) {
                    $croppedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $coverCroppedData));
                    if ($croppedImageData === false) {
                        $errors[] = 'Failed to decode cropped image data.';
                    } else {
                        $filename = 'cover_' . uniqid() . '.webp';
                        $targetDir = __DIR__ . '/../uploads/covers/';
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }
                        $targetPath = $targetDir . $filename;
                        $img = imagecreatefromstring($croppedImageData);
                        if ($img === false) {
                            $errors[] = 'Failed to create image from cropped data.';
                        } else {
                            if (imagewebp($img, $targetPath, 80)) {
                                // Delete old cover if exists
                                if (!empty($post['cover_image']) && file_exists(__DIR__ . '/../' . $post['cover_image'])) {
                                    unlink(__DIR__ . '/../' . $post['cover_image']);
                                }
                                $coverFinalPath = 'uploads/covers/' . $filename;
                            } else {
                                $errors[] = 'Failed to save cropped cover image.';
                            }
                            imagedestroy($img);
                        }
                    }
                } else {
                    // No crop, use uploaded file directly
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $filename = 'cover_' . uniqid() . '.' . ($ext === 'png' ? 'png' : 'webp');
                    $targetDir = __DIR__ . '/../uploads/covers/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $targetPath = $targetDir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // Convert to webp if possible
                        if ($ext !== 'webp') {
                            $image = null;
                            if ($ext === 'jpeg' || $ext === 'jpg') {
                                $image = imagecreatefromjpeg($targetPath);
                            } elseif ($ext === 'png') {
                                $image = imagecreatefrompng($targetPath);
                            } elseif ($ext === 'gif') {
                                $image = imagecreatefromgif($targetPath);
                            }
                            if ($image) {
                                $webpPath = $targetDir . 'cover_' . uniqid() . '.webp';
                                if (imagewebp($image, $webpPath, 80)) {
                                    unlink($targetPath);
                                    $coverFinalPath = 'uploads/covers/' . basename($webpPath);
                                } else {
                                    $coverFinalPath = 'uploads/covers/' . $filename;
                                }
                                imagedestroy($image);
                            } else {
                                $coverFinalPath = 'uploads/covers/' . $filename;
                            }
                        } else {
                            $coverFinalPath = 'uploads/covers/' . $filename;
                        }
                        // Delete old cover
                        if (!empty($post['cover_image']) && $coverFinalPath !== $post['cover_image'] && file_exists(__DIR__ . '/../' . $post['cover_image'])) {
                            unlink(__DIR__ . '/../' . $post['cover_image']);
                        }
                    } else {
                        $errors[] = 'Failed to move uploaded cover image.';
                    }
                }
            }
        } // end cover upload

        // --- Process gallery ---
        // We'll collect new gallery paths
        $newGalleryPaths = [];
        if ($galleryUploaded) {
            $files = $_FILES['gallery_images'];
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $uploadError = validateImage($file, 5 * 1024 * 1024);
                if ($uploadError !== true) {
                    $errors[] = 'Gallery image ' . ($i+1) . ': ' . $uploadError;
                    continue;
                }
                // Process and save
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = 'gallery_' . uniqid() . '.' . ($ext === 'png' ? 'png' : 'webp');
                $targetDir = __DIR__ . '/../uploads/gallery/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $targetPath = $targetDir . $filename;
                $image = null;
                if ($ext === 'jpeg' || $ext === 'jpg') {
                    $image = imagecreatefromjpeg($file['tmp_name']);
                } elseif ($ext === 'png') {
                    $image = imagecreatefrompng($file['tmp_name']);
                } elseif ($ext === 'gif') {
                    $image = imagecreatefromgif($file['tmp_name']);
                } else {
                    // Unsupported, just move
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $newGalleryPaths[] = 'uploads/gallery/' . $filename;
                    }
                    continue;
                }
                if ($image) {
                    $webpPath = $targetDir . 'gallery_' . uniqid() . '.webp';
                    if (imagewebp($image, $webpPath, 75)) {
                        $newGalleryPaths[] = 'uploads/gallery/' . basename($webpPath);
                    } else {
                        // fallback to original
                        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                            $newGalleryPaths[] = 'uploads/gallery/' . $filename;
                        }
                    }
                    imagedestroy($image);
                } else {
                    // If can't create image, just move
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $newGalleryPaths[] = 'uploads/gallery/' . $filename;
                    }
                }
            }
        }

        // --- Determine which gallery images to keep ---
        $currentGalleryIds = array_column($galleryImages, 'id');
        $toDeleteIds = array_diff($currentGalleryIds, $keepGalleryIds);

        // --- Validate total gallery count (existing kept + new) ---
        $keptCount = count($keepGalleryIds);
        $newCount = count($newGalleryPaths);
        $totalGallery = $keptCount + $newCount;
        if ($totalGallery > 10) {
            $errors[] = 'Gallery cannot exceed 10 images total. You have ' . $totalGallery . '.';
        }

        // --- If no errors, update post ---
        if (empty($errors)) {
            try {
                // Begin transaction
                db()->beginTransaction();

                // Update post
                $updated = db_update(
                    'posts',
                    [
                        'category_id' => $categoryId,
                        'title' => $title,
                        'slug' => $slug,
                        'display_date' => $displayDate,
                        'sort_date' => date('Y-m-d', strtotime($displayDate)) ?: null,
                        'preview_text' => $previewText,
                        'content' => $content,
                        'cover_image' => $coverFinalPath,
                        'certificate_url' => $certificateUrl,
                        'status' => $status,
                    ],
                    'id = :id',
                    ['id' => $postId]
                );

                // --- Update tags ---
                // Delete existing post_tags
                db_delete('post_tags', 'post_id = :post_id', ['post_id' => $postId]);

                // Insert new tags
                if (!empty($tagsInput)) {
                    $tagsArray = array_map('trim', explode(',', $tagsInput));
                    foreach ($tagsArray as $tagName) {
                        if (empty($tagName)) continue;
                        $tagSlug = slugify($tagName);
                        $tagId = db_fetch_column('SELECT id FROM tags WHERE slug = :slug', ['slug' => $tagSlug]);
                        if (!$tagId) {
                            db_insert('tags', ['name' => $tagName, 'slug' => $tagSlug]);
                            $tagId = (int)db()->getPdo()->lastInsertId();
                        } else {
                            $tagId = (int)$tagId;
                        }
                        db_insert('post_tags', ['post_id' => $postId, 'tag_id' => $tagId]);
                    }
                }

                // --- Update gallery ---
                // Delete images marked for removal and their files
                foreach ($toDeleteIds as $id) {
                    $imgPath = db_fetch_column('SELECT image_path FROM gallery_images WHERE id = :id', ['id' => $id]);
                    if ($imgPath) {
                        $fullPath = __DIR__ . '/../' . $imgPath;
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                    }
                    db_delete('gallery_images', 'id = :id', ['id' => $id]);
                }

                // Get current max sort order for existing kept images
                $maxSort = 0;
                if (!empty($keepGalleryIds)) {
                    $maxSort = (int)db_fetch_column(
                        'SELECT MAX(sort_order) FROM gallery_images WHERE post_id = :post_id AND id IN (' . implode(',', $keepGalleryIds) . ')',
                        ['post_id' => $postId]
                    );
                }

                // Insert new gallery images with incremental sort order
                $sortOrder = $maxSort + 1;
                foreach ($newGalleryPaths as $path) {
                    db_insert('gallery_images', [
                        'post_id' => $postId,
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                // Commit
                db()->commit();

                $success = true;
                $successMessage = 'Post updated successfully.';

                // Refresh post data to reflect changes
                $post = db_fetch_one(
                    "SELECT p.*, 
                            (SELECT GROUP_CONCAT(t.name, ',') 
                             FROM post_tags pt 
                             JOIN tags t ON pt.tag_id = t.id 
                             WHERE pt.post_id = p.id) as tags_string
                     FROM posts p
                     WHERE p.id = :id",
                    ['id' => $postId]
                );
                $galleryImages = db_fetch_all(
                    'SELECT id, image_path, sort_order FROM gallery_images WHERE post_id = :post_id ORDER BY sort_order',
                    ['post_id' => $postId]
                );

                // Update form data
                $postData = [
                    'title' => $title,
                    'slug' => $slug,
                    'display_date' => $displayDate,
                    'preview_text' => $previewText,
                    'content' => $content,
                    'certificate_url' => $certificateUrl,
                    'category_id' => $categoryId,
                    'tags' => $tagsInput,
                    'status' => $status,
                ];

            } catch (PDOException $e) {
                db()->rollBack();
                db_log_error('Failed to update post', ['post_id' => $postId, 'error' => $e->getMessage()]);
                $errors[] = 'Database error: Could not update the post.';
            }
        } else {
            // If errors, repopulate form data from POST
            $postData = [
                'title' => $title,
                'slug' => $slug,
                'display_date' => $displayDate,
                'preview_text' => $previewText,
                'content' => $content,
                'certificate_url' => $certificateUrl,
                'category_id' => $categoryId,
                'tags' => $tagsInput,
                'status' => $status,
            ];
        }
    }
} else {
    // Initial load: populate postData from DB
    $postData = [
        'title' => $post['title'] ?? '',
        'slug' => $post['slug'] ?? '',
        'display_date' => $post['display_date'] ?? '',
        'preview_text' => $post['preview_text'] ?? '',
        'content' => $post['content'] ?? '',
        'certificate_url' => $post['certificate_url'] ?? '',
        'category_id' => $post['category_id'] ?? 0,
        'tags' => $post['tags_string'] ?? '',
        'status' => $post['status'] ?? 'published',
    ];
}

// --- Helper functions (same as create-post) ---

function validateImage($file, $maxSize = 5 * 1024 * 1024) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload error: ' . $file['error'];
    }
    if ($file['size'] > $maxSize) {
        return 'File size exceeds limit (' . ($maxSize / 1024 / 1024) . ' MB).';
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowed)) {
        return 'Invalid image type. Allowed: JPEG, PNG, GIF, WebP.';
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowedExt)) {
        return 'Invalid file extension. Allowed: jpg, jpeg, png, gif, webp.';
    }
    return true;
}

function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Post · IrtiJa Admin</title>
    <link rel="icon" type="image/png" href="../irtija.png" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <!-- Cropper.js for cover image crop -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
    <!-- TinyMCE for rich text -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <!-- Admin CSS (fallback) -->
    <link rel="stylesheet" href="assets/admin.css" />
    <style>
        /* Full form styles (similar to create-post) */
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
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
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
        .form-card {
            background: #FCFAF5;
            border-radius: 16px;
            border: 1px solid rgba(213,207,196,0.20);
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0,70,67,0.02);
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: #4A4A4A;
            margin-bottom: 0.25rem;
        }
        .form-group label .required { color: #C44A4A; }
        .form-group .help-text {
            font-size: 0.8rem;
            color: #7A7A7A;
            margin-top: 0.25rem;
        }
        .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 2px solid rgba(213,207,196,0.30);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            background: #FFFFFF;
            color: #1A1A1A;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            outline: none;
        }
        .form-control:focus {
            border-color: #D4A853;
            box-shadow: 0 0 0 4px rgba(212,168,83,0.06);
        }
        select.form-control { appearance: auto; }
        textarea.form-control { resize: vertical; min-height: 100px; }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-row .form-group { margin-bottom: 0; }
        .cover-upload-area { display: flex; flex-direction: column; gap: 1rem; }
        .cover-preview {
            max-width: 300px;
            max-height: 200px;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid rgba(213,207,196,0.20);
        }
        .cover-preview img { width: 100%; height: auto; display: block; }
        .gallery-upload-area { display: flex; flex-direction: column; gap: 0.5rem; }
        .gallery-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 0.5rem;
        }
        .gallery-preview-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(213,207,196,0.20);
            aspect-ratio: 1/1;
            background: #f5f2ea;
        }
        .gallery-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-preview-item .remove-gallery {
            position: absolute;
            top: 0.25rem;
            right: 0.25rem;
            background: rgba(0,0,0,0.6);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-preview-item .remove-gallery:hover { background: #C44A4A; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary { background: linear-gradient(135deg, #004643, #1A7A74); color: #fff; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,70,67,0.20); }
        .btn-secondary { background: linear-gradient(135deg, #D4A853, #B8923A); color: #fff; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(212,168,83,0.20); }
        .btn-outline { background: transparent; color: #004643; border: 2px solid #004643; }
        .btn-outline:hover { background: #004643; color: #fff; }
        .btn-danger { background: #C44A4A; color: #fff; }
        .btn-danger:hover { background: #A33A3A; }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .message {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1rem;
        }
        .message.error {
            background: rgba(196,74,74,0.06);
            border: 1px solid rgba(196,74,74,0.15);
            color: #B44A4A;
        }
        .message.success {
            background: rgba(43,140,110,0.06);
            border: 1px solid rgba(43,140,110,0.15);
            color: #2B8C6E;
        }
        .message ul { padding-left: 1.5rem; margin: 0.5rem 0 0; }
        .cropper-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .cropper-modal.open { display: flex; }
        .cropper-modal .cropper-container {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow: auto;
        }
        .cropper-modal .cropper-container img { max-width: 100%; display: block; }
        .cropper-modal .cropper-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            justify-content: flex-end;
        }
        @media (max-width: 1024px) {
            .admin-sidebar { width: 220px; padding: 1.5rem 1rem; }
            .admin-main { padding: 1.5rem; max-width: calc(100% - 220px); }
            .form-row { grid-template-columns: 1fr; }
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
            .admin-header h1 { font-size: 1.6rem; }
            .form-card { padding: 1.25rem; }
            .form-row { grid-template-columns: 1fr; gap: 1rem; }
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
            <a href="edit.php?id=<?php echo $postId; ?>" class="nav-link active"><i class="fas fa-edit"></i> Edit Post</a>
            <a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="media.php" class="nav-link"><i class="fas fa-images"></i> Media</a>
            <div class="nav-heading">Account</div>
            <a href="logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" role="main">
        <div class="admin-header">
            <h1>Edit Post</h1>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars(admin_get_username() ?? 'Admin'); ?>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="message error" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success" role="status">
                <?php echo htmlspecialchars($successMessage); ?>
                <a href="../blog-detail.php?slug=<?php echo urlencode($post['slug']); ?>" target="_blank">View post</a>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <form class="form-card" method="POST" action="" enctype="multipart/form-data">
            <?php echo admin_csrf_field(); ?>

            <!-- Category and Status -->
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($postData['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="published" <?php echo ($postData['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo ($postData['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
            </div>

            <!-- Title -->
            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($postData['title']); ?>" required placeholder="Enter post title" />
            </div>

            <!-- Slug -->
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($postData['slug']); ?>" placeholder="Leave empty to auto-generate from title" />
                <div class="help-text">URL-friendly version of the title. Auto-generated if left empty.</div>
            </div>

            <!-- Display Date -->
            <div class="form-group">
                <label for="display_date">Display Date <span class="required">*</span></label>
                <input type="text" id="display_date" name="display_date" class="form-control" value="<?php echo htmlspecialchars($postData['display_date']); ?>" placeholder="e.g. 15 August 2026" required />
                <div class="help-text">Flexible format: e.g., "15 August 2026", "Summer 2026", "2026".</div>
            </div>

            <!-- Preview Text -->
            <div class="form-group">
                <label for="preview_text">Preview Text</label>
                <textarea id="preview_text" name="preview_text" class="form-control" rows="2" placeholder="Short preview for blog listings"><?php echo htmlspecialchars($postData['preview_text']); ?></textarea>
            </div>

            <!-- Content (Rich Text) -->
            <div class="form-group">
                <label for="content">Content <span class="required">*</span></label>
                <textarea id="content" name="content" class="form-control" rows="15"><?php echo htmlspecialchars($postData['content']); ?></textarea>
                <div class="help-text">Write the full blog post content. Supports Markdown and rich formatting.</div>
            </div>

            <!-- Certificate URL -->
            <div class="form-group">
                <label for="certificate_url">Certificate URL</label>
                <input type="url" id="certificate_url" name="certificate_url" class="form-control" value="<?php echo htmlspecialchars($postData['certificate_url']); ?>" placeholder="https://example.com/certificate.pdf" />
                <div class="help-text">Optional link to a certificate or external resource.</div>
            </div>

            <!-- Tags -->
            <div class="form-group">
                <label for="tags">Tags</label>
                <input type="text" id="tags" name="tags" class="form-control" value="<?php echo htmlspecialchars($postData['tags']); ?>" placeholder="Comma separated: cybersecurity, web-dev, workshop" />
                <div class="help-text">Separate tags with commas.</div>
            </div>

            <!-- Cover Image -->
            <div class="form-group">
                <label>Cover Image</label>
                <div class="cover-upload-area">
                    <?php if (!empty($post['cover_image'])): ?>
                        <div class="cover-preview" id="currentCoverPreview">
                            <img src="../<?php echo htmlspecialchars($post['cover_image']); ?>" alt="Current cover" />
                            <div style="margin-top:0.25rem;font-size:0.8rem;color:#7A7A7A;">Current cover</div>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="cover_image" name="cover_image" accept="image/*" class="form-control-file" />
                    <div class="cover-preview" id="coverPreview" style="display:none;">
                        <img id="coverPreviewImg" src="#" alt="Cover preview" />
                    </div>
                    <button type="button" class="btn btn-secondary" id="cropCoverBtn" style="display:none;">Crop Cover Image</button>
                    <input type="hidden" name="cover_cropped_data" id="coverCroppedData" value="" />
                    <div class="help-text">Upload a new cover image to replace the current one (JPEG, PNG, GIF, WebP). You can crop it after upload.</div>
                </div>
            </div>

            <!-- Gallery Images -->
            <div class="form-group">
                <label>Gallery Images (1–10 total)</label>
                <div class="gallery-upload-area">
                    <!-- Existing gallery -->
                    <?php if (!empty($galleryImages)): ?>
                        <div class="gallery-preview-grid" id="existingGalleryGrid">
                            <?php foreach ($galleryImages as $img): ?>
                                <div class="gallery-preview-item" data-id="<?php echo $img['id']; ?>">
                                    <img src="../<?php echo htmlspecialchars($img['image_path']); ?>" alt="Gallery image" />
                                    <button type="button" class="remove-gallery" data-id="<?php echo $img['id']; ?>" title="Remove this image">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Hidden inputs to track kept IDs -->
                        <?php foreach ($galleryImages as $img): ?>
                            <input type="hidden" name="keep_gallery[]" value="<?php echo $img['id']; ?>" class="keep-gallery-input" />
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- New uploads preview -->
                    <div class="gallery-preview-grid" id="galleryPreviewGrid"></div>
                    <input type="file" id="gallery_images" name="gallery_images[]" accept="image/*" multiple class="form-control-file" />
                    <div class="help-text">Upload up to 10 images for the gallery. Existing images can be removed by clicking the X.</div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Post</button>
                <a href="posts.php" class="btn btn-outline">Cancel</a>
                <a href="../blog-detail.php?slug=<?php echo urlencode($post['slug']); ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-eye"></i> View Post</a>
            </div>
        </form>
    </main>
</div>

<!-- Cropper Modal -->
<div class="cropper-modal" id="cropperModal">
    <div class="cropper-container">
        <img id="cropperImage" src="#" alt="Crop cover image" />
        <div class="cropper-actions">
            <button type="button" class="btn btn-outline" id="cancelCrop">Cancel</button>
            <button type="button" class="btn btn-primary" id="applyCrop">Apply Crop</button>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
    // --- TinyMCE initialization ---
    tinymce.init({
        selector: '#content',
        height: 400,
        menubar: false,
        plugins: 'lists link image code',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
        branding: false,
        promotion: false,
        forced_root_block: 'p',
        valid_elements: '*[*]',
        convert_urls: false,
        relative_urls: false,
        remove_script_host: true,
    });

    // --- Cover image crop functionality ---
    const coverInput = document.getElementById('cover_image');
    const coverPreview = document.getElementById('coverPreview');
    const coverPreviewImg = document.getElementById('coverPreviewImg');
    const cropCoverBtn = document.getElementById('cropCoverBtn');
    const cropperModal = document.getElementById('cropperModal');
    const cropperImage = document.getElementById('cropperImage');
    const cancelCrop = document.getElementById('cancelCrop');
    const applyCrop = document.getElementById('applyCrop');
    const coverCroppedData = document.getElementById('coverCroppedData');
    let cropper = null;

    coverInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(event) {
            coverPreviewImg.src = event.target.result;
            coverPreview.style.display = 'block';
            cropCoverBtn.style.display = 'inline-block';
            coverCroppedData.value = '';
        };
        reader.readAsDataURL(file);
    });

    cropCoverBtn.addEventListener('click', function() {
        if (!coverPreviewImg.src || coverPreviewImg.src === '#') return;
        cropperImage.src = coverPreviewImg.src;
        cropperModal.classList.add('open');
        if (cropper) { cropper.destroy(); }
        cropper = new Cropper(cropperImage, {
            aspectRatio: 16 / 9,
            viewMode: 1,
            autoCropArea: 0.8,
        });
    });

    cancelCrop.addEventListener('click', closeCropper);
    applyCrop.addEventListener('click', function() {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 1200, height: 630 });
        if (!canvas) {
            alert('Could not crop image. Please try again.');
            return;
        }
        const dataUrl = canvas.toDataURL('image/png');
        coverCroppedData.value = dataUrl;
        coverPreviewImg.src = dataUrl;
        closeCropper();
    });

    function closeCropper() {
        if (cropper) { cropper.destroy(); cropper = null; }
        cropperModal.classList.remove('open');
    }
    cropperModal.addEventListener('click', function(e) {
        if (e.target === cropperModal) closeCropper();
    });

    // --- Gallery removal ---
    document.querySelectorAll('#existingGalleryGrid .remove-gallery').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const parent = this.closest('.gallery-preview-item');
            // Remove the hidden input for this gallery image
            const hiddenInput = document.querySelector('input.keep-gallery-input[value="' + id + '"]');
            if (hiddenInput) {
                hiddenInput.remove();
            }
            // Remove the preview
            parent.remove();
        });
    });

    // --- New gallery preview ---
    const galleryInput = document.getElementById('gallery_images');
    const galleryPreviewGrid = document.getElementById('galleryPreviewGrid');

    galleryInput.addEventListener('change', function(e) {
        const files = e.target.files;
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'gallery-preview-item';
                const img = document.createElement('img');
                img.src = event.target.result;
                img.alt = file.name;
                div.appendChild(img);
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-gallery';
                removeBtn.innerHTML = '&times;';
                removeBtn.setAttribute('data-index', i);
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Remove this file from the input
                    const dt = new DataTransfer();
                    const filesList = galleryInput.files;
                    for (let j = 0; j < filesList.length; j++) {
                        if (j !== i) {
                            dt.items.add(filesList[j]);
                        }
                    }
                    galleryInput.files = dt.files;
                    // Remove preview
                    div.remove();
                });
                div.appendChild(removeBtn);
                galleryPreviewGrid.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    });

    // --- Slug auto-generation ---
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    titleInput.addEventListener('blur', function() {
        if (!slugInput.value.trim()) {
            const slug = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        }
    });

    // --- Count gallery images (existing kept + new uploads) for validation on submit ---
    // Not strictly needed but could help.
</script>
</body>
</html>
