<?php
/**
 * admin/config.php
 *
 * Central configuration file for the IrtiJa admin system.
 * All configuration values are defined here as constants.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Prevent direct access ---
if (!defined('IRTIJA_ADMIN')) {
    die('Direct access to this file is not permitted.');
}

// ============================================================
//  1.  PATH CONFIGURATION
// ============================================================

/**
 * Project root directory (absolute path)
 * Since this file is in admin/, the root is one level up.
 */
define('IRTIJA_ROOT', dirname(__DIR__));

/**
 * Admin directory path
 */
define('IRTIJA_ADMIN_DIR', __DIR__);

/**
 * Database directory path
 */
define('IRTIJA_DB_DIR', IRTIJA_ROOT . '/database');

/**
 * Database file path
 */
define('IRTIJA_DB_PATH', IRTIJA_DB_DIR . '/blog.db');

/**
 * Uploads directory path
 */
define('IRTIJA_UPLOAD_DIR', IRTIJA_ROOT . '/uploads');

/**
 * Covers directory path (within uploads)
 */
define('IRTIJA_COVERS_DIR', IRTIJA_UPLOAD_DIR . '/covers');

/**
 * Gallery directory path (within uploads)
 */
define('IRTIJA_GALLERY_DIR', IRTIJA_UPLOAD_DIR . '/gallery');

/**
 * Logs directory path
 */
define('IRTIJA_LOG_DIR', IRTIJA_ROOT . '/logs');

/**
 * Admin assets directory (relative to web root)
 * Used for CSS and JS links in admin pages.
 */
define('IRTIJA_ADMIN_ASSETS_URL', 'assets/');

// ============================================================
//  2.  UPLOAD CONFIGURATION
// ============================================================

/**
 * Maximum upload file size for cover images (5 MB)
 * Value is in bytes.
 */
define('IRTIJA_MAX_COVER_SIZE', 5 * 1024 * 1024);

/**
 * Maximum upload file size for gallery images (5 MB each)
 * Value is in bytes.
 */
define('IRTIJA_MAX_GALLERY_SIZE', 5 * 1024 * 1024);

/**
 * Maximum number of gallery images per post
 */
define('IRTIJA_MAX_GALLERY_IMAGES', 10);

/**
 * Allowed image MIME types
 * Used for file upload validation.
 */
define('IRTIJA_ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
]);

/**
 * Allowed image file extensions
 * Used for file upload validation.
 */
define('IRTIJA_ALLOWED_EXTENSIONS', [
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
]);

/**
 * Image quality for WebP conversion (1-100)
 * Higher values = better quality = larger files.
 */
define('IRTIJA_IMAGE_QUALITY', 80);

/**
 * Cover image crop dimensions
 * Used for the cropping tool and for generating thumbnails.
 */
define('IRTIJA_COVER_WIDTH', 1200);
define('IRTIJA_COVER_HEIGHT', 630);

/**
 * Gallery image max dimension
 * Images will be resized to this maximum dimension while maintaining aspect ratio.
 */
define('IRTIJA_GALLERY_MAX_DIMENSION', 800);

// ============================================================
//  3.  SESSION CONFIGURATION
// ============================================================

/**
 * Session name prefix to avoid conflicts with other applications
 * The actual session name will be this prefix plus a unique identifier.
 */
define('IRTIJA_SESSION_PREFIX', 'irtija_');

/**
 * Session lifetime in seconds (2 hours = 7200 seconds)
 * After this time, the user will be logged out.
 */
define('IRTIJA_SESSION_LIFETIME', 7200);

/**
 * Maximum login attempts before lockout
 * Prevents brute-force attacks.
 */
define('IRTIJA_LOGIN_ATTEMPTS_MAX', 5);

/**
 * Login lockout duration in seconds (15 minutes)
 */
define('IRTIJA_LOGIN_LOCKOUT_DURATION', 900);

// ============================================================
//  4.  PAGINATION CONFIGURATION
// ============================================================

/**
 * Number of posts per page in the admin posts list
 */
define('IRTIJA_POSTS_PER_PAGE', 10);

/**
 * Number of media items per page in the media list
 */
define('IRTIJA_MEDIA_PER_PAGE', 24);

// ============================================================
//  5.  APPLICATION SETTINGS
// ============================================================

/**
 * Application name
 */
define('IRTIJA_APP_NAME', 'IrtiJa');

/**
 * Application version
 */
define('IRTIJA_VERSION', '1.0.0');

/**
 * Debug mode
 * Set to true for development, false for production.
 * When true, error reporting is enabled.
 * When false, errors are logged but not displayed.
 */
define('IRTIJA_DEBUG', false);

/**
 * Default timezone
 * Set to match the website's primary audience (Asia/Dhaka).
 */
define('IRTIJA_TIMEZONE', 'Asia/Dhaka');

/**
 * Default category slug
 * Used when a post has no category assigned.
 */
define('IRTIJA_DEFAULT_CATEGORY_SLUG', 'uncategorized');

/**
 * Default category name
 * Used when a post has no category assigned.
 */
define('IRTIJA_DEFAULT_CATEGORY_NAME', 'Uncategorized');

// ============================================================
//  6.  SECURITY & VALIDATION
// ============================================================

/**
 * Regular expression pattern for safe slug validation
 * Allows lowercase letters, numbers, and hyphens only.
 */
define('IRTIJA_SLUG_PATTERN', '/^[a-z0-9-]+$/');

/**
 * Maximum length for a slug
 */
define('IRTIJA_SLUG_MAX_LENGTH', 120);

/**
 * Maximum length for a tag name
 */
define('IRTIJA_TAG_MAX_LENGTH', 50);

/**
 * Maximum length for a category name
 */
define('IRTIJA_CATEGORY_MAX_LENGTH', 50);

// ============================================================
//  7.  RESPONSE CODES & MESSAGES
// ============================================================

/**
 * Define common HTTP status codes for the admin area
 * Used in redirects and error responses.
 */
define('IRTIJA_HTTP_OK', 200);
define('IRTIJA_HTTP_BAD_REQUEST', 400);
define('IRTIJA_HTTP_UNAUTHORIZED', 401);
define('IRTIJA_HTTP_FORBIDDEN', 403);
define('IRTIJA_HTTP_NOT_FOUND', 404);
define('IRTIJA_HTTP_INTERNAL_ERROR', 500);

// ============================================================
//  8.  DATE & TIME FORMATS
// ============================================================

/**
 * Display date format for admin interface
 * Uses PHP date() format string.
 */
define('IRTIJA_DATE_FORMAT', 'M j, Y');

/**
 * Display date/time format for admin interface
 * Uses PHP date() format string.
 */
define('IRTIJA_DATETIME_FORMAT', 'M j, Y g:i A');

/**
 * Internal date format for sorting (ISO 8601)
 * Used for sort_date column in the database.
 */
define('IRTIJA_SORT_DATE_FORMAT', 'Y-m-d');

// ============================================================
//  9.  FILE SYSTEM PERMISSIONS
// ============================================================

/**
 * Directory permission mask (0755 = rwxr-xr-x)
 * Used when creating new directories.
 */
define('IRTIJA_DIR_PERMISSION', 0755);

/**
 * File permission mask (0644 = rw-r--r--)
 * Used when creating new files.
 */
define('IRTIJA_FILE_PERMISSION', 0644);

// ============================================================
//  10.  SET UP APPLICATION ENVIRONMENT
// ============================================================

// --- Set the default timezone ---
date_default_timezone_set(IRTIJA_TIMEZONE);

// --- Configure error reporting based on debug mode ---
if (IRTIJA_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', IRTIJA_LOG_DIR . '/php_errors.log');
}

// --- Create required directories if they don't exist ---
if (!is_dir(IRTIJA_DB_DIR)) {
    @mkdir(IRTIJA_DB_DIR, IRTIJA_DIR_PERMISSION, true);
}

if (!is_dir(IRTIJA_UPLOAD_DIR)) {
    @mkdir(IRTIJA_UPLOAD_DIR, IRTIJA_DIR_PERMISSION, true);
}

if (!is_dir(IRTIJA_COVERS_DIR)) {
    @mkdir(IRTIJA_COVERS_DIR, IRTIJA_DIR_PERMISSION, true);
}

if (!is_dir(IRTIJA_GALLERY_DIR)) {
    @mkdir(IRTIJA_GALLERY_DIR, IRTIJA_DIR_PERMISSION, true);
}

if (!is_dir(IRTIJA_LOG_DIR)) {
    @mkdir(IRTIJA_LOG_DIR, IRTIJA_DIR_PERMISSION, true);
}

// ============================================================
//  11.  FUNCTION TO RETRIEVE CONFIGURATION
// ============================================================

/**
 * Get a configuration value by name.
 * Useful for cases where constants aren't the best fit.
 *
 * @param string $name The configuration name
 * @return mixed The configuration value, or null if not found
 */
function irtija_config(string $name)
{
    $config = [
        'app_name'              => IRTIJA_APP_NAME,
        'version'               => IRTIJA_VERSION,
        'debug'                 => IRTIJA_DEBUG,
        'timezone'              => IRTIJA_TIMEZONE,
        'max_cover_size'        => IRTIJA_MAX_COVER_SIZE,
        'max_gallery_size'      => IRTIJA_MAX_GALLERY_SIZE,
        'max_gallery_images'    => IRTIJA_MAX_GALLERY_IMAGES,
        'cover_width'           => IRTIJA_COVER_WIDTH,
        'cover_height'          => IRTIJA_COVER_HEIGHT,
        'gallery_max_dimension' => IRTIJA_GALLERY_MAX_DIMENSION,
        'image_quality'         => IRTIJA_IMAGE_QUALITY,
        'posts_per_page'        => IRTIJA_POSTS_PER_PAGE,
        'media_per_page'        => IRTIJA_MEDIA_PER_PAGE,
        'session_lifetime'      => IRTIJA_SESSION_LIFETIME,
        'login_attempts_max'    => IRTIJA_LOGIN_ATTEMPTS_MAX,
        'login_lockout'         => IRTIJA_LOGIN_LOCKOUT_DURATION,
        'date_format'           => IRTIJA_DATE_FORMAT,
        'datetime_format'       => IRTIJA_DATETIME_FORMAT,
        'default_category_slug' => IRTIJA_DEFAULT_CATEGORY_SLUG,
        'default_category_name' => IRTIJA_DEFAULT_CATEGORY_NAME,
    ];

    return $config[$name] ?? null;
}

// --- Configuration loaded successfully ---
// (No output, just define constants and functions)
