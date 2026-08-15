<?php
/**
 * admin/setup.php
 *
 * First-run setup page for the IrtiJa admin panel.
 * Creates the initial admin user account and database schema.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Define admin context ---
define('IRTIJA_ADMIN', true);

// --- Include required files ---
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// --- If an admin user already exists, redirect to login ---
if (!admin_requires_setup()) {
    header('Location: login.php');
    exit;
}

// --- CSRF token ---
$csrfToken = admin_csrf_token();

// --- Initialize variables ---
$errors = [];
$success = false;
$username = '';
$password = '';
$confirmPassword = '';

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Validate CSRF ---
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // --- Validate username ---
        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username must be less than 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, underscores, and hyphens.';
        }

        // --- Validate password ---
        if (empty($password)) {
            $errors[] = 'Password is required.';
        } else {
            $pwValidation = admin_validate_password($password);
            if (!$pwValidation['valid']) {
                $errors[] = $pwValidation['message'];
            }
        }

        // --- Check password confirmation ---
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // --- If no errors, create the admin user ---
        if (empty($errors)) {
            try {
                // Ensure database schema exists
                if (!db_ensure_ready()) {
                    $errors[] = 'Database schema could not be created. Please check logs.';
                } else {
                    // Hash the password
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert the admin user
                    db_insert('admin_user', [
                        'username' => $username,
                        'password_hash' => $passwordHash,
                    ]);

                    $success = true;

                    // Store success message in session for login page
                    $_SESSION['setup_success'] = 'Admin account created successfully. You can now log in.';

                    // Redirect to login page after a short delay (handled by JS or meta refresh)
                    // We'll show a success message and a link to login
                }
            } catch (PDOException $e) {
                // Log the actual error for debugging
                db_log_error('Failed to create admin user during setup', [
                    'username' => $username,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);

                // Display a more specific error message based on the exception
                if ($e->getCode() === '23000') {
                    $errors[] = 'Username already exists. Please choose a different username.';
                } else {
                    // Show the actual error message in development
                    if (IRTIJA_DEBUG) {
                        $errors[] = 'Database error: ' . $e->getMessage();
                    } else {
                        $errors[] = 'Database error: Could not create admin user.';
                    }
                }
            }
        }
    }
}

// --- Check if the database file is writable ---
$dbPath = IRTIJA_DB_PATH;
$dbDir = dirname($dbPath);
$dbWritable = is_writable($dbDir);
if (!$dbWritable) {
    $errors[] = 'Database directory (' . $dbDir . ') is not writable. Please check permissions.';
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Setup · IrtiJa Admin</title>
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
        /* Setup page specific styles (fallback) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #F0EDE5;
            color: #4A4A4A;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background-image:
                radial-gradient(ellipse at 20% 30%, rgba(212, 168, 83, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(26, 122, 116, 0.03) 0%, transparent 50%);
        }

        .setup-card {
            background: #FCFAF5;
            border-radius: 24px;
            padding: 2.5rem 2rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 16px 64px rgba(0, 70, 67, 0.04);
            border: 1px solid rgba(213, 207, 196, 0.20);
            transition: box-shadow 0.3s ease;
        }

        .setup-card:hover {
            box-shadow: 0 20px 72px rgba(0, 70, 67, 0.06);
        }

        .setup-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            text-decoration: none;
        }

        .setup-brand img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(212, 168, 83, 0.12);
        }

        .setup-brand .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.02em;
        }

        .setup-brand .brand-name .gold {
            color: #D4A853;
        }

        .setup-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1A1A1A;
            text-align: center;
            margin-bottom: 0.25rem;
        }

        .setup-subtitle {
            text-align: center;
            color: #7A7A7A;
            font-size: 0.95rem;
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        .setup-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4A4A4A;
            letter-spacing: 0.3px;
        }

        .form-group label .required {
            color: #C44A4A;
            font-weight: 700;
        }

        .form-group .help-text {
            font-size: 0.75rem;
            color: #7A7A7A;
            margin-top: 0.25rem;
        }

        .form-group input {
            padding: 0.7rem 1rem;
            border: 2px solid rgba(213, 207, 196, 0.30);
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            background: #FFFFFF;
            color: #1A1A1A;
            transition: all 0.2s ease;
            outline: none;
            width: 100%;
        }

        .form-group input:focus {
            border-color: #D4A853;
            box-shadow: 0 0 0 4px rgba(212, 168, 83, 0.06);
        }

        .form-group input::placeholder {
            color: #B0B0B0;
        }

        .form-group input.error {
            border-color: #C44A4A;
            box-shadow: 0 0 0 4px rgba(196, 74, 74, 0.06);
        }

        .form-group .password-requirements {
            font-size: 0.7rem;
            color: #7A7A7A;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.25rem;
        }

        .form-group .password-requirements .req {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            background: rgba(213, 207, 196, 0.10);
            font-size: 0.65rem;
            color: #7A7A7A;
        }

        .form-group .password-requirements .req.met {
            background: rgba(43, 140, 110, 0.08);
            color: #2B8C6E;
        }

        .form-group .password-requirements .req i {
            font-size: 0.55rem;
        }

        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .btn-setup {
            background: linear-gradient(135deg, #004643, #1A7A74);
            color: #fff;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 9999px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            min-height: 52px;
        }

        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0, 70, 67, 0.20);
        }

        .btn-setup:active {
            transform: scale(0.98);
        }

        .btn-setup:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-setup i {
            font-size: 1rem;
        }

        .message {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        .message.error {
            background: rgba(196, 74, 74, 0.06);
            border: 1px solid rgba(196, 74, 74, 0.15);
            color: #B44A4A;
        }

        .message.success {
            background: rgba(43, 140, 110, 0.06);
            border: 1px solid rgba(43, 140, 110, 0.15);
            color: #2B8C6E;
        }

        .message ul {
            padding-left: 1.5rem;
            margin: 0.25rem 0 0;
        }

        .message ul li {
            margin-bottom: 0.15rem;
        }

        .setup-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: #7A7A7A;
        }

        .setup-footer a {
            color: #004643;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .setup-footer a:hover {
            color: #D4A853;
        }

        .setup-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 0.5rem 0;
        }

        .setup-divider::before,
        .setup-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(213, 207, 196, 0.20);
        }

        .setup-divider span {
            font-size: 0.75rem;
            color: #7A7A7A;
            white-space: nowrap;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .setup-card {
                padding: 2rem 1.25rem;
            }
            .setup-title {
                font-size: 1.5rem;
            }
            .setup-brand .brand-name {
                font-size: 1.3rem;
            }
            .setup-brand img {
                width: 36px;
                height: 36px;
            }
            .form-group .password-requirements {
                flex-direction: column;
                gap: 0.2rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

    <div class="setup-card" role="main" aria-labelledby="setup-heading">

        <!-- Brand -->
        <a href="../index.php" class="setup-brand" aria-label="IrtiJa home">
            <img src="../logo.png" alt="IrtiJa Logo" />
            <span class="brand-name">Irti<span class="gold">Ja</span></span>
        </a>

        <!-- Title -->
        <h1 class="setup-title" id="setup-heading">Admin Setup</h1>
        <p class="setup-subtitle">Create your administrator account to get started</p>

        <?php if ($success): ?>
            <div class="message success" role="status">
                <i class="fas fa-check-circle"></i>
                <strong>Account created successfully!</strong>
                <p style="margin-top:0.5rem;font-size:0.9rem;">
                    You can now <a href="login.php" style="color:#004643;font-weight:600;text-decoration:underline;">log in to the admin panel</a>.
                </p>
            </div>

            <!-- Auto-redirect after 3 seconds -->
            <meta http-equiv="refresh" content="3; url=login.php">
            <p style="text-align:center;font-size:0.8rem;color:#7A7A7A;margin-top:0.5rem;">
                Redirecting to login page in 3 seconds...
            </p>

        <?php else: ?>

            <!-- Display errors -->
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

            <!-- Setup Form -->
            <form class="setup-form" method="POST" action="" autocomplete="off">
                <?php echo admin_csrf_field(); ?>

                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required autofocus
                           value="<?php echo htmlspecialchars($username); ?>"
                           class="<?php echo !empty($errors) && !empty($username) ? 'error' : ''; ?>" />
                    <div class="help-text">3–50 characters. Letters, numbers, underscores, and hyphens only.</div>
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Choose a strong password" required
                           class="<?php echo !empty($errors) && !empty($password) ? 'error' : ''; ?>" />
                    <div class="password-requirements" id="passwordRequirements">
                        <span class="req" id="req-length"><i class="fas fa-circle"></i> 8+ characters</span>
                        <span class="req" id="req-upper"><i class="fas fa-circle"></i> Uppercase</span>
                        <span class="req" id="req-lower"><i class="fas fa-circle"></i> Lowercase</span>
                        <span class="req" id="req-number"><i class="fas fa-circle"></i> Number</span>
                        <span class="req" id="req-special"><i class="fas fa-circle"></i> Special character</span>
                    </div>
                    <div class="help-text">Minimum 8 characters with uppercase, lowercase, number, and special character.</div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required />
                </div>

                <div class="setup-divider">
                    <span>First &amp; only admin account</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-setup" id="setupBtn">
                        <i class="fas fa-user-plus"></i> Create Admin Account
                    </button>
                </div>
            </form>

            <div class="setup-footer">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to site</a>
            </div>

        <?php endif; ?>

    </div>

    <!-- Password strength checker (client-side enhancement only) -->
    <script>
        (function() {
            'use strict';

            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            const requirements = {
                length: document.getElementById('req-length'),
                upper: document.getElementById('req-upper'),
                lower: document.getElementById('req-lower'),
                number: document.getElementById('req-number'),
                special: document.getElementById('req-special'),
            };

            function checkPasswordStrength(password) {
                const checks = {
                    length: password.length >= 8,
                    upper: /[A-Z]/.test(password),
                    lower: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[^A-Za-z0-9]/.test(password),
                };
                return checks;
            }

            function updateRequirements(password) {
                const checks = checkPasswordStrength(password);
                for (const [key, el] of Object.entries(requirements)) {
                    if (checks[key]) {
                        el.classList.add('met');
                        el.innerHTML = '<i class="fas fa-check-circle"></i> ' + el.textContent.replace(/^[^\s]+\s/, '');
                    } else {
                        el.classList.remove('met');
                        // Restore icon if not met
                        if (!el.innerHTML.includes('circle')) {
                            el.innerHTML = '<i class="fas fa-circle"></i> ' + el.textContent.replace(/^[^\s]+\s/, '');
                        }
                    }
                }
            }

            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    updateRequirements(this.value);
                });
                // Initial check if password is pre-filled
                if (passwordInput.value) {
                    updateRequirements(passwordInput.value);
                }
            }

            // Confirm password validation
            if (confirmInput && passwordInput) {
                confirmInput.addEventListener('input', function() {
                    if (this.value && this.value !== passwordInput.value) {
                        this.classList.add('error');
                    } else {
                        this.classList.remove('error');
                    }
                });
                passwordInput.addEventListener('input', function() {
                    if (confirmInput.value && confirmInput.value !== this.value) {
                        confirmInput.classList.add('error');
                    } else {
                        confirmInput.classList.remove('error');
                    }
                });
            }

        })();
    </script>

</body>
</html>
