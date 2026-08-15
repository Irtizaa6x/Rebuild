<?php
/**
 * admin/auth.php
 *
 * Authentication and authorization functions for the IrtiJa admin panel.
 * Handles secure session management, login, logout, and access control.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Prevent direct access ---
if (!defined('IRTIJA_ADMIN')) {
    die('Direct access to this file is not permitted.');
}

// --- Include database abstraction ---
require_once __DIR__ . '/db.php';

// --- Session configuration ---
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters before starting
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    session_start();
}

/**
 * Check if an admin user exists in the database.
 *
 * @return bool True if at least one admin user exists
 */
function admin_user_exists(): bool
{
    try {
        $result = db_fetch_column('SELECT COUNT(*) FROM admin_user');
        return (int) $result > 0;
    } catch (PDOException $e) {
        db_log_error('Failed to check if admin user exists', [
            'error' => $e->getMessage()
        ]);
        return false;
    }
}

/**
 * Check if the system requires first-run setup.
 *
 * @return bool True if no admin user exists (setup needed)
 */
function admin_requires_setup(): bool
{
    return !admin_user_exists();
}

/**
 * Login an administrator.
 *
 * @param string $username The username
 * @param string $password The plaintext password
 *
 * @return array{success: bool, message: string} Result of the login attempt
 */
function admin_login(string $username, string $password): array
{
    // --- Validate input ---
    $username = trim($username);
    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Please enter both username and password.'
        ];
    }

    // --- Prevent brute force by tracking attempts ---
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attemptsKey = 'login_attempts_' . md5($ip);

    // Check if too many attempts have been made
    if (isset($_SESSION[$attemptsKey])) {
        $attempts = (int) $_SESSION[$attemptsKey];
        if ($attempts >= 5) {
            // Check if the lockout period has expired (15 minutes)
            if (isset($_SESSION['login_lockout_time'])) {
                $lockoutTime = (int) $_SESSION['login_lockout_time'];
                if (time() - $lockoutTime < 900) { // 15 minutes
                    return [
                        'success' => false,
                        'message' => 'Too many failed login attempts. Please try again later.'
                    ];
                } else {
                    // Lockout period expired, reset attempts
                    unset($_SESSION[$attemptsKey]);
                    unset($_SESSION['login_lockout_time']);
                }
            }
        }
    }

    try {
        // --- Fetch the admin user ---
        $user = db_fetch_one(
            'SELECT id, username, password_hash FROM admin_user WHERE username = :username',
            ['username' => $username]
        );

        if (!$user) {
            // Record failed attempt
            $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
            if ($_SESSION[$attemptsKey] >= 5) {
                $_SESSION['login_lockout_time'] = time();
            }
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        // --- Verify the password ---
        if (!password_verify($password, $user['password_hash'])) {
            // Record failed attempt
            $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
            if ($_SESSION[$attemptsKey] >= 5) {
                $_SESSION['login_lockout_time'] = time();
            }
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }

        // --- Check if the password needs rehashing ---
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            db_update(
                'admin_user',
                ['password_hash' => $newHash],
                'id = :id',
                ['id' => $user['id']]
            );
        }

        // --- Login successful ---
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store the user data in the session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = (int) $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_login_time'] = time();

        // Clear any failed attempt records
        unset($_SESSION[$attemptsKey]);
        unset($_SESSION['login_lockout_time']);

        // Log the successful login
        db_log_error('Admin login successful', [
            'username' => $username,
            'ip' => $ip
        ], 'info');

        return [
            'success' => true,
            'message' => 'Login successful.'
        ];

    } catch (PDOException $e) {
        db_log_error('Login failed: database error', [
            'username' => $username,
            'error' => $e->getMessage()
        ]);
        return [
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ];
    }
}

/**
 * Check if the current user is logged in.
 *
 * @return bool True if the user is logged in
 */
function admin_is_logged_in(): bool
{
    // Check if the session indicates a logged-in user
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }

    // Verify the user ID exists
    if (!isset($_SESSION['admin_user_id']) || !is_int($_SESSION['admin_user_id'])) {
        return false;
    }

    // Optional: check if the user still exists in the database
    // This prevents access if the admin user was deleted
    try {
        $exists = db_fetch_column(
            'SELECT COUNT(*) FROM admin_user WHERE id = :id',
            ['id' => $_SESSION['admin_user_id']]
        );
        if ((int) $exists === 0) {
            // User no longer exists, log them out
            admin_logout();
            return false;
        }
    } catch (PDOException $e) {
        // If the database check fails, assume the user is valid
        // but log the error
        db_log_error('Failed to verify admin user existence', [
            'user_id' => $_SESSION['admin_user_id'],
            'error' => $e->getMessage()
        ]);
        // Don't log the user out on a database error
    }

    // Check if the session has expired (optional: 2-hour timeout)
    if (isset($_SESSION['admin_login_time'])) {
        $maxSessionTime = 7200; // 2 hours
        if (time() - $_SESSION['admin_login_time'] > $maxSessionTime) {
            admin_logout();
            return false;
        }
    }

    return true;
}

/**
 * Require that the user is logged in.
 * If not, redirect to the login page.
 *
 * @param string $redirectUrl The URL to redirect to after login
 * @return void
 */
function admin_require_login(string $redirectUrl = ''): void
{
    if (admin_is_logged_in()) {
        return;
    }

    // Build the redirect URL
    $loginUrl = 'login.php';
    if (!empty($redirectUrl)) {
        $loginUrl .= '?redirect=' . urlencode($redirectUrl);
    }

    // If it's an AJAX request, return a 401 status
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Please log in.'
        ]);
        exit;
    }

    // Redirect to the login page
    header('Location: ' . $loginUrl);
    exit;
}

/**
 * Log out the current user.
 *
 * @param string $redirectUrl Optional URL to redirect to after logout
 * @return void
 */
function admin_logout(string $redirectUrl = ''): void
{
    // Clear session variables
    $_SESSION = [];

    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Destroy the session
    session_destroy();

    // If a redirect URL is provided, redirect after logout
    if (!empty($redirectUrl)) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Get the current admin user's ID.
 *
 * @return int|null The user ID, or null if not logged in
 */
function admin_get_user_id(): ?int
{
    if (!admin_is_logged_in()) {
        return null;
    }
    return $_SESSION['admin_user_id'] ?? null;
}

/**
 * Get the current admin user's username.
 *
 * @return string|null The username, or null if not logged in
 */
function admin_get_username(): ?string
{
    if (!admin_is_logged_in()) {
        return null;
    }
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Check if the current user has permission to perform an action.
 * Currently only a single admin user exists, so this is always true.
 *
 * @param string $action The action to check
 * @return bool True if the user has permission
 */
function admin_has_permission(string $action): bool
{
    // For a single admin user, all actions are permitted
    return admin_is_logged_in();
}

/**
 * Get the user's IP address safely.
 *
 * @return string
 */
function admin_get_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Check for proxy headers (but be careful with spoofing)
    $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if (!empty($forwardedFor)) {
        $ips = explode(',', $forwardedFor);
        $first = trim($ips[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            $ip = $first;
        }
    }
    
    return $ip;
}

/**
 * Generate a CSRF token and store it in the session.
 *
 * @return string The generated token
 */
function admin_csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a CSRF token.
 *
 * @param string $token The token to validate
 * @return bool True if the token is valid
 */
function admin_validate_csrf(string $token): bool
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get a CSRF token field for use in forms.
 *
 * @return string HTML input field with the CSRF token
 */
function admin_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(admin_csrf_token()) . '" />';
}

/**
 * Check if the request is a POST request.
 *
 * @return bool
 */
function admin_is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if the request is a GET request.
 *
 * @return bool
 */
function admin_is_get(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Redirect to a URL and exit.
 *
 * @param string $url The URL to redirect to
 * @return void
 */
function admin_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitize input for display (escape HTML).
 *
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function admin_escape(string $input): string
{
    return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Check if a password meets minimum requirements.
 *
 * @param string $password The password to check
 * @return array{valid: bool, message: string}
 */
function admin_validate_password(string $password): array
{
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'message' => 'Password must be at least 8 characters long.'
        ];
    }

    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter.'
        ];
    }

    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one lowercase letter.'
        ];
    }

    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number.'
        ];
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one special character.'
        ];
    }

    return [
        'valid' => true,
        'message' => 'Password is valid.'
    ];
}

// --- If the system requires setup, redirect to setup page ---
// (This is intentionally not called automatically here to avoid infinite redirects)
// The admin pages should call admin_require_setup() or check admin_requires_setup()
