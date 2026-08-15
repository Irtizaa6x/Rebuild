<?php
/**
 * header.php
 *
 * Shared header for the entire IrtiJa website.
 * Includes <head> metadata, styles, scripts, and the main navigation.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- 1. Set default values if not provided by the including page ---
$page_title       = $page_title ?? 'IrtiJa · Cybersecurity & CSE Portfolio';
$page_description = $page_description ?? 'Md. Irtija Azad Talha — CSE student at Green University of Bangladesh, cybersecurity enthusiast, BNCC cadet. Explore my work and journey.';
$page_canonical   = $page_canonical ?? 'https://irtizaa6x.github.io/';
$current_page     = $current_page ?? 'index';

// --- 2. Define navigation structure ---
$nav_items = [
    'index'      => ['label' => 'About', 'url' => 'index.php'],
    'education'  => ['label' => 'Education', 'url' => 'education.php'],
    'experience' => ['label' => 'Experience', 'url' => 'experience.php'],
    'skills'     => ['label' => 'Skills', 'url' => 'skills.php'],
    'blog'       => ['label' => 'Blog', 'url' => 'blog.php'],
    'contact'    => ['label' => 'Contact', 'url' => 'contact.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- SEO Meta -->
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="<?php echo htmlspecialchars($page_canonical); ?>" />

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo htmlspecialchars($page_canonical); ?>" />
    <meta property="og:image" content="https://irtizaa6x.github.io/assets/images/og-image.jpg" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="irtija.png" />

    <!-- Preconnect to critical origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css" />

    <!-- Optional: Additional page-specific styles can be injected here -->
    <?php if (isset($page_styles)): ?>
        <style><?php echo $page_styles; ?></style>
    <?php endif; ?>
</head>
<body>

    <!-- ============================================================
         TOP NAVIGATION — Premium, transparent with blur
         ============================================================ -->
    <header class="site-header" role="banner" aria-label="Main navigation">
        <div class="container header-inner">

            <!-- Logo / Brand -->
            <a href="index.php" class="brand-link" aria-label="IrtiJa home">
                <img src="logo.png" alt="IrtiJa Logo" class="brand-logo" width="40" height="40" />
                <span class="brand-name">Irti<span class="gold">Ja</span></span>
            </a>

            <!-- Desktop Navigation -->
            <nav class="nav-desktop" aria-label="Primary navigation">
                <ul class="nav-list">
                    <?php foreach ($nav_items as $key => $item): ?>
                        <li>
                            <a href="<?php echo $item['url']; ?>" class="nav-link<?php echo ($current_page === $key) ? ' active' : ''; ?>" <?php echo ($current_page === $key) ? 'aria-current="page"' : ''; ?>>
                                <?php echo $item['label']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Dark Mode Toggle -->
            <button class="theme-toggle" id="themeToggle" aria-label="Switch to dark mode">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Mobile Hamburger -->
            <button class="hamburger-toggle" id="hamburgerToggle" aria-label="Toggle menu" aria-expanded="false">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Mobile Navigation (overlay) -->
        <nav class="nav-mobile" id="mobileNav" aria-label="Mobile navigation">
            <ul class="nav-mobile-list">
                <?php foreach ($nav_items as $key => $item): ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>" class="nav-mobile-link<?php echo ($current_page === $key) ? ' active' : ''; ?>" <?php echo ($current_page === $key) ? 'aria-current="page"' : ''; ?>>
                            <?php echo $item['label']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <!-- Contact is already in the nav, but adding a CTA style for mobile is handled by CSS on the last item -->
            </ul>
        </nav>
        <div class="nav-mobile-overlay" aria-hidden="true"></div>
    </header>

    <!-- Note: The main content of the page must follow this header -->
    <!-- Note: script.js is included in footer.php (or at the bottom of pages) -->
