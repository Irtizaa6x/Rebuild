<?php
/**
 * admin/categories.php
 *
 * Category management for the IrtiJa admin panel.
 * Allows administrators to view, create, edit, and delete categories.
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

// --- Initialize variables ---
$errors = [];
$success = [];
$categories = [];
$editCategory = null;
$isEditing = false;

// --- Handle actions ---

// 1. Handle Create
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        }

        if (empty($slug)) {
            $slug = slugify($name);
        }

        // Check for duplicate slug
        if (!empty($slug)) {
            $existing = db_fetch_column(
                'SELECT COUNT(*) FROM categories WHERE slug = :slug',
                ['slug' => $slug]
            );
            if ((int)$existing > 0) {
                $errors[] = 'A category with this slug already exists.';
            }
        }

        if (empty($errors)) {
            try {
                db_insert('categories', ['name' => $name, 'slug' => $slug]);
                $success[] = 'Category "' . htmlspecialchars($name) . '" created successfully.';
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors[] = 'A category with this name already exists.';
                } else {
                    db_log_error('Failed to create category', ['name' => $name, 'error' => $e->getMessage()]);
                    $errors[] = 'Database error: Could not create category.';
                }
            }
        }
    }
}

// 2. Handle Edit (load category for editing)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editCategory = db_fetch_one('SELECT id, name, slug FROM categories WHERE id = :id', ['id' => $editId]);
    if ($editCategory) {
        $isEditing = true;
    } else {
        $errors[] = 'Category not found.';
    }
}

// 3. Handle Update
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if ($id < 1) {
            $errors[] = 'Invalid category ID.';
        }

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        }

        if (empty($slug)) {
            $slug = slugify($name);
        }

        // Check for duplicate slug (excluding current)
        if (!empty($slug) && $id > 0) {
            $existing = db_fetch_column(
                'SELECT COUNT(*) FROM categories WHERE slug = :slug AND id != :id',
                ['slug' => $slug, 'id' => $id]
            );
            if ((int)$existing > 0) {
                $errors[] = 'A category with this slug already exists.';
            }
        }

        if (empty($errors)) {
            try {
                $updated = db_update(
                    'categories',
                    ['name' => $name, 'slug' => $slug],
                    'id = :id',
                    ['id' => $id]
                );
                if ($updated) {
                    $success[] = 'Category updated successfully.';
                    $isEditing = false;
                    $editCategory = null;
                } else {
                    $errors[] = 'Category could not be updated.';
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors[] = 'A category with this name already exists.';
                } else {
                    db_log_error('Failed to update category', ['id' => $id, 'error' => $e->getMessage()]);
                    $errors[] = 'Database error: Could not update category.';
                }
            }
        }
    }
}

// 4. Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];

    // Validate CSRF for delete (using GET with token)
    if (!isset($_GET['csrf_token']) || !admin_validate_csrf($_GET['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Check if category is in use
        $postCount = db_fetch_column(
            'SELECT COUNT(*) FROM posts WHERE category_id = :id',
            ['id' => $deleteId]
        );

        if ((int)$postCount > 0) {
            $errors[] = 'This category is used by ' . $postCount . ' post(s). Deleting it will set those posts to "Uncategorized". Continue?';
            // We'll show a confirmation dialog via JavaScript instead of auto-deleting
            // Store the delete ID in session for confirmation
            $_SESSION['pending_delete_category'] = $deleteId;
            $_SESSION['pending_delete_count'] = $postCount;
            // Redirect to show confirmation
            header('Location: categories.php?confirm_delete=1');
            exit;
        } else {
            // No posts use this category, safe to delete
            try {
                db_delete('categories', 'id = :id', ['id' => $deleteId]);
                $success[] = 'Category deleted successfully.';
            } catch (PDOException $e) {
                db_log_error('Failed to delete category', ['id' => $deleteId, 'error' => $e->getMessage()]);
                $errors[] = 'Database error: Could not delete category.';
            }
        }
    }
}

// 5. Handle Confirm Delete (with posts)
if (isset($_GET['confirm_delete']) && $_GET['confirm_delete'] === '1') {
    $deleteId = $_SESSION['pending_delete_category'] ?? 0;
    $postCount = $_SESSION['pending_delete_count'] ?? 0;

    if ($deleteId > 0 && isset($_GET['csrf_token']) && admin_validate_csrf($_GET['csrf_token'])) {
        // If user confirmed via POST, we should handle it
        // But we'll handle it via a form POST for safety
        // Show the confirmation UI instead
    }
}

// 6. Handle Confirm Delete (POST)
if (isset($_POST['action']) && $_POST['action'] === 'confirm_delete') {
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            try {
                // Delete the category (ON DELETE SET NULL will handle posts)
                db_delete('categories', 'id = :id', ['id' => $id]);
                $success[] = 'Category deleted successfully. Posts have been set to "Uncategorized".';
                unset($_SESSION['pending_delete_category']);
                unset($_SESSION['pending_delete_count']);
            } catch (PDOException $e) {
                db_log_error('Failed to delete category with posts', ['id' => $id, 'error' => $e->getMessage()]);
                $errors[] = 'Database error: Could not delete category.';
            }
        }
    }
}

// 7. Cancel delete
if (isset($_GET['cancel_delete'])) {
    unset($_SESSION['pending_delete_category']);
    unset($_SESSION['pending_delete_count']);
    header('Location: categories.php');
    exit;
}

// --- Fetch all categories ---
try {
    $categories = db_fetch_all(
        'SELECT c.*, COUNT(p.id) as post_count 
         FROM categories c
         LEFT JOIN posts p ON p.category_id = c.id
         GROUP BY c.id
         ORDER BY c.name'
    );
} catch (PDOException $e) {
    db_log_error('Failed to fetch categories', ['error' => $e->getMessage()]);
    $errors[] = 'Database error: Could not load categories.';
}

// --- Check for pending delete confirmation ---
$showConfirmDelete = false;
$pendingDeleteId = $_SESSION['pending_delete_category'] ?? 0;
$pendingDeleteCount = $_SESSION['pending_delete_count'] ?? 0;
if ($pendingDeleteId > 0 && isset($_GET['confirm_delete']) && $_GET['confirm_delete'] === '1') {
    $showConfirmDelete = true;
}

/**
 * Helper function to slugify a string.
 */
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
    <title>Categories · IrtiJa Admin</title>
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

        .card {
            background: #FCFAF5;
            border-radius: 16px;
            border: 1px solid rgba(213,207,196,0.20);
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(0,70,67,0.02);
        }
        .card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 1.25rem;
        }

        .form-group { margin-bottom: 1rem; }
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.5rem;
        }

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
        .btn-primary {
            background: linear-gradient(135deg, #004643, #1A7A74);
            color: #fff;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,70,67,0.20); }
        .btn-secondary {
            background: linear-gradient(135deg, #D4A853, #B8923A);
            color: #fff;
        }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(212,168,83,0.20); }
        .btn-outline {
            background: transparent;
            color: #004643;
            border: 2px solid #004643;
        }
        .btn-outline:hover { background: #004643; color: #fff; }
        .btn-danger {
            background: #C44A4A;
            color: #fff;
        }
        .btn-danger:hover { background: #A33A3A; }
        .btn-sm { padding: 0.3rem 1rem; font-size: 0.8rem; }

        .table-wrapper {
            overflow-x: auto;
        }
        .categories-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .categories-table th {
            text-align: left;
            padding: 0.8rem 1rem;
            font-weight: 600;
            color: #4A4A4A;
            border-bottom: 2px solid rgba(213,207,196,0.20);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .categories-table td {
            padding: 0.7rem 1rem;
            border-bottom: 1px solid rgba(213,207,196,0.08);
            vertical-align: middle;
        }
        .categories-table tr:last-child td { border-bottom: none; }
        .categories-table .actions {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        .categories-table .actions a {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .actions .btn-edit { background: rgba(26,122,116,0.08); color: #1A7A74; }
        .actions .btn-edit:hover { background: #1A7A74; color: #fff; }
        .actions .btn-delete { background: rgba(196,74,74,0.08); color: #B44A4A; }
        .actions .btn-delete:hover { background: #C44A4A; color: #fff; }

        .empty-state {
            text-align: center;
            padding: 2rem 0;
            color: #B0B0B0;
        }
        .empty-state i { font-size: 2.5rem; color: rgba(213,207,196,0.30); margin-bottom: 0.5rem; display: block; }

        /* Confirmation modal */
        .confirm-modal {
            display: <?php echo $showConfirmDelete ? 'flex' : 'none'; ?>;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .confirm-modal .modal-content {
            background: #FCFAF5;
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(213,207,196,0.20);
            box-shadow: 0 16px 64px rgba(0,70,67,0.10);
        }
        .confirm-modal .modal-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        .confirm-modal .modal-content p {
            color: #4A4A4A;
            margin-bottom: 1.5rem;
        }
        .confirm-modal .modal-content .modal-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
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
            .admin-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .admin-header h1 { font-size: 1.6rem; }
            .card { padding: 1.25rem; }
            .form-row { grid-template-columns: 1fr; gap: 1rem; }
            .categories-table { font-size: 0.8rem; min-width: 400px; }
        }
        @media (max-width: 480px) {
            .categories-table { min-width: 300px; }
            .actions a { font-size: 0.65rem; padding: 0.15rem 0.4rem; }
            .confirm-modal .modal-content { padding: 1.5rem; }
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
            <a href="categories.php" class="nav-link active"><i class="fas fa-tags"></i> Categories</a>
            <a href="media.php" class="nav-link"><i class="fas fa-images"></i> Media</a>
            <div class="nav-heading">Account</div>
            <a href="logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main" role="main">

        <div class="admin-header">
            <h1>Categories</h1>
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

        <?php if (!empty($success)): ?>
            <div class="message success" role="status">
                <ul>
                    <?php foreach ($success as $msg): ?>
                        <li><?php echo htmlspecialchars($msg); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Confirmation Modal for deleting categories with posts -->
        <div class="confirm-modal" id="confirmModal">
            <div class="modal-content">
                <h3>Delete Category?</h3>
                <p>
                    This category is used by <strong><?php echo $pendingDeleteCount; ?></strong> post(s).
                    Deleting it will set those posts to "Uncategorized".
                    <br /><br />
                    Are you sure you want to proceed?
                </p>
                <div class="modal-actions">
                    <form method="POST" action="categories.php">
                        <?php echo admin_csrf_field(); ?>
                        <input type="hidden" name="action" value="confirm_delete" />
                        <input type="hidden" name="id" value="<?php echo $pendingDeleteId; ?>" />
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Yes, Delete</button>
                    </form>
                    <a href="categories.php?cancel_delete=1" class="btn btn-outline">Cancel</a>
                </div>
            </div>
        </div>

        <!-- Category Form (Create / Edit) -->
        <div class="card">
            <h2><?php echo $isEditing ? 'Edit Category' : 'Create New Category'; ?></h2>
            <form method="POST" action="categories.php">
                <?php echo admin_csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $isEditing ? 'update' : 'create'; ?>" />
                <?php if ($isEditing && $editCategory): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editCategory['id']; ?>" />
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Category Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="<?php echo $isEditing && $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>"
                               required placeholder="e.g. Cybersecurity" />
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" class="form-control"
                               value="<?php echo $isEditing && $editCategory ? htmlspecialchars($editCategory['slug']) : ''; ?>"
                               placeholder="Leave empty to auto-generate" />
                        <div class="help-text">URL-friendly version. Auto-generated if left empty.</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas <?php echo $isEditing ? 'fa-save' : 'fa-plus'; ?>"></i>
                        <?php echo $isEditing ? 'Update Category' : 'Create Category'; ?>
                    </button>
                    <?php if ($isEditing): ?>
                        <a href="categories.php" class="btn btn-outline">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Categories List -->
        <div class="card">
            <h2>All Categories</h2>

            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <i class="fas fa-tags"></i>
                    <p>No categories yet. Create your first category above.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="categories-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Posts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($category['slug']); ?></code></td>
                                    <td><?php echo (int)$category['post_count']; ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="categories.php?edit=<?php echo (int)$category['id']; ?>" class="btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <?php if ((int)$category['post_count'] > 0): ?>
                                                <a href="categories.php?delete=<?php echo (int)$category['id']; ?>&csrf_token=<?php echo urlencode($csrfToken); ?>"
                                                   class="btn-delete"
                                                   title="Delete (has posts)"
                                                   onclick="return confirm('This category is used by <?php echo (int)$category['post_count']; ?> post(s). Deleting it will set those posts to Uncategorized. Continue?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            <?php else: ?>
                                                <a href="categories.php?delete=<?php echo (int)$category['id']; ?>&csrf_token=<?php echo urlencode($csrfToken); ?>"
                                                   class="btn-delete"
                                                   title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this category?');">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

</div>

<!-- Admin JavaScript -->
<script src="assets/admin.js"></script>

<!-- Inline CSRF token for JavaScript -->
<script>
    const csrfToken = '<?php echo $csrfToken; ?>';

    // Auto-slug generation
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('blur', function() {
                if (!slugInput.value.trim()) {
                    const slug = nameInput.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    slugInput.value = slug;
                }
            });
        }
    });
</script>

</body>
</html>
