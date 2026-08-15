<?php
/**
 * admin/login.php
 *
 * Secure administrator login page for the IrtiJa admin panel.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Define admin context for included files ---
define('IRTIJA_ADMIN', true);

// --- Include authentication and database helpers ---
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// --- Check if the system requires first-run setup ---
if (admin_requires_setup()) {
    // Redirect to the setup page if no admin user exists
    header('Location: setup.php');
    exit;
}

// --- Handle logout parameter (for users coming from logout) ---
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    admin_logout();
    // Continue to show login page with a message
}

// --- If already logged in, redirect to the dashboard ---
if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

// --- CSRF token generation ---
$csrfToken = admin_csrf_token();

// --- Initialize login result messages ---
$errorMessage = '';
$successMessage = '';

// --- Handle login form submission ---
if (admin_is_post()) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !admin_validate_csrf($_POST['csrf_token'])) {
        $errorMessage = 'Invalid security token. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = admin_login($username, $password);

        if ($result['success']) {
            // Login successful, redirect to dashboard
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            // Prevent open redirects: only allow relative paths or safe domains
            if (strpos($redirect, 'http') === 0) {
                $redirect = 'index.php';
            }
            header('Location: ' . $redirect);
            exit;
        } else {
            $errorMessage = $result['message'];
        }
    }
}

// --- Check if redirected from logout with a message ---
if (isset($_GET['logged_out']) && $_GET['logged_out'] === '1') {
    $successMessage = 'You have been successfully logged out.';
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login · IrtiJa</title>
    <link rel="icon" type="image/png" href="../irtija.png" />
    
    <!-- Google Fonts (matching main site) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Admin Login Styles (self-contained for simplicity) -->
    <style>
        /* ============================================================
           ADMIN LOGIN — Clean, minimal, brand-consistent
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background-image: 
                radial-gradient(ellipse at 20% 30%, rgba(212, 168, 83, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(26, 122, 116, 0.03) 0%, transparent 50%);
        }

        .login-card {
            background: #FCFAF5;
            border-radius: 24px;
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 16px 64px rgba(0, 70, 67, 0.04);
            border: 1px solid rgba(213, 207, 196, 0.20);
            transition: box-shadow 0.3s ease;
        }

        .login-card:hover {
            box-shadow: 0 20px 72px rgba(0, 70, 67, 0.06);
        }

        .login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            text-decoration: none;
        }

        .login-brand img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(212, 168, 83, 0.12);
        }

        .login-brand .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 800;
            color: #1A1A1A;
            letter-spacing: -0.02em;
        }

        .login-brand .brand-name .gold {
            color: #D4A853;
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1A1A1A;
            text-align: center;
            margin-bottom: 0.25rem;
        }

        .login-subtitle {
            text-align: center;
            color: #7A7A7A;
            font-size: 0.95rem;
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        .login-form {
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

        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0, 70, 67, 0.20);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .btn-login i {
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

        .login-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            color: #7A7A7A;
        }

        .login-footer a {
            color: #004643;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .login-footer a:hover {
            color: #D4A853;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.25rem;
            }
            .login-title {
                font-size: 1.5rem;
            }
            .login-brand .brand-name {
                font-size: 1.3rem;
            }
            .login-brand img {
                width: 36px;
                height: 36px;
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

    <div class="login-card" role="main" aria-labelledby="login-heading">
        <!-- Brand -->
        <a href="../index.php" class="login-brand" aria-label="IrtiJa home">
            <img src="../logo.png" alt="IrtiJa Logo" />
            <span class="brand-name">Irti<span class="gold">Ja</span></span>
        </a>

        <!-- Title -->
        <h1 class="login-title" id="login-heading">Admin Login</h1>
        <p class="login-subtitle">Enter your credentials to access the dashboard</p>

        <!-- Display messages -->
        <?php if (!empty($errorMessage)): ?>
            <div class="message error" role="alert"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="message success" role="status"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?><?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" autocomplete="off">
            <!-- CSRF Token -->
            <?php echo admin_csrf_field(); ?>

            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required />
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </form>

        <div class="login-footer">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to site</a>
        </div>
    </div>

    <!-- Optional: Auto-focus username on load -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.focus();
            }
        });
    </script>

</body>
</html>
