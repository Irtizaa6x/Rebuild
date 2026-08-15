<?php
/**
 * 404.php
 *
 * Custom 404 error page for IrtiJa portfolio.
 * Provides helpful navigation when a page is not found.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'Page Not Found · IrtiJa';
$page_description = 'The page you\'re looking for doesn\'t exist. Return to the IrtiJa portfolio homepage.';
$page_canonical   = 'https://irtizaa6x.github.io/404.php';
$current_page     = '404';

// --- 404-specific styles (inline to keep it self-contained) ---
$page_styles = '
    /* ----- 404 Page specific styles ----- */
    .error-404-page {
        padding: var(--space-10) 0 var(--space-9);
        min-height: 60vh;
        display: flex;
        align-items: center;
    }

    .error-404-content {
        text-align: center;
        max-width: 620px;
        margin: 0 auto;
    }

    .error-404-icon {
        font-size: 5rem;
        color: var(--gold);
        margin-bottom: var(--space-4);
        display: block;
        animation: float-404 3s ease-in-out infinite;
    }

    @keyframes float-404 {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }

    .error-404-title {
        font-family: "Playfair Display", serif;
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: var(--space-3);
        line-height: 1.1;
    }

    .error-404-title .gold {
        color: var(--gold);
    }

    .error-404-subtitle {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: var(--space-3);
    }

    .error-404-message {
        font-size: 1.05rem;
        color: var(--text-muted);
        line-height: 1.7;
        margin-bottom: var(--space-6);
        max-width: 440px;
        margin-left: auto;
        margin-right: auto;
    }

    .error-404-actions {
        display: flex;
        flex-wrap: wrap;
        gap: var(--space-3);
        justify-content: center;
        margin-bottom: var(--space-6);
    }

    .error-404-search {
        max-width: 420px;
        margin: 0 auto;
        position: relative;
    }

    .error-404-search i {
        position: absolute;
        left: var(--space-3);
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.9rem;
        pointer-events: none;
    }

    .error-404-search input {
        width: 100%;
        padding: 0.75rem var(--space-3) 0.75rem 2.8rem;
        border: 2px solid var(--border-subtle);
        border-radius: var(--radius-full);
        font-family: "Inter", sans-serif;
        font-size: 0.875rem;
        background: var(--bg-card);
        color: var(--text-primary);
        transition: all var(--transition-fast);
        outline: none;
    }

    .error-404-search input:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 4px rgba(212, 168, 83, 0.06);
    }

    .error-404-search input::placeholder {
        color: var(--text-muted);
    }

    .error-404-search .search-submit {
        position: absolute;
        right: 4px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--gradient-gold);
        border: none;
        color: #fff;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        cursor: pointer;
        transition: all var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .error-404-search .search-submit:hover {
        transform: translateY(-50%) scale(1.05);
        box-shadow: 0 4px 16px rgba(212, 168, 83, 0.20);
    }

    .error-404-footer {
        margin-top: var(--space-6);
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .error-404-footer a {
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
        transition: color var(--transition-fast);
    }

    .error-404-footer a:hover {
        color: var(--gold);
    }

    @media (max-width: 768px) {
        .error-404-page {
            padding: var(--space-7) 0 var(--space-6);
            min-height: auto;
        }

        .error-404-icon {
            font-size: 3.5rem;
        }

        .error-404-title {
            font-size: 2rem;
        }

        .error-404-subtitle {
            font-size: 1rem;
        }

        .error-404-message {
            font-size: 0.95rem;
        }

        .error-404-actions {
            flex-direction: column;
            align-items: center;
        }

        .error-404-actions .btn {
            width: 100%;
            max-width: 280px;
        }
    }

    @media (max-width: 400px) {
        .error-404-icon {
            font-size: 2.8rem;
        }

        .error-404-title {
            font-size: 1.6rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .error-404-icon {
            animation: none !important;
        }
    }
';

// --- Include the shared header ---
include 'header.php';
?>

    <!-- ============================================================
         404 ERROR PAGE
         ============================================================ -->
    <main class="error-404-page" role="main" aria-labelledby="error-title">
        <div class="container">
            <div class="error-404-content">

                <!-- Icon -->
                <i class="fas fa-compass error-404-icon" aria-hidden="true"></i>

                <!-- Error Code Badge -->
                <span class="section-tag">404 Error</span>

                <!-- Title -->
                <h1 class="error-404-title" id="error-title">
                    Page Not <span class="gold">Found</span>
                </h1>

                <!-- Subtitle -->
                <p class="error-404-subtitle">
                    Oops! This page seems to have wandered off.
                </p>

                <!-- Message -->
                <p class="error-404-message">
                    The page you're looking for doesn't exist or may have been moved.
                    Let's get you back on track.
                </p>

                <!-- Actions -->
                <div class="error-404-actions">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                    <a href="blog.php" class="btn btn-ghost">
                        <i class="fas fa-book-open"></i> Visit Blog
                    </a>
                    <a href="contact.php" class="btn btn-secondary">
                        <i class="fas fa-paper-plane"></i> Contact Me
                    </a>
                </div>

                <!-- Search -->
                <div class="error-404-search">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        id="errorSearch"
                        placeholder="Search the site..."
                        aria-label="Search the site"
                    />
                    <button class="search-submit" id="searchSubmit" aria-label="Submit search">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Footer note -->
                <p class="error-404-footer">
                    <a href="index.php">Irti<span style="color:var(--gold);">Ja</span></a>
                    · Built with care. Let's find your way home.
                </p>

            </div>
        </div>
    </main>

    <!-- ============================================================
         SEARCH FUNCTIONALITY
         ============================================================ -->
    <script>
        (function() {
            'use strict';

            const searchInput = document.getElementById('errorSearch');
            const searchSubmit = document.getElementById('searchSubmit');

            function performSearch() {
                const query = searchInput.value.trim();
                if (!query) {
                    searchInput.focus();
                    return;
                }
                const siteUrl = window.location.origin;
                const searchUrl =
                    `https://www.google.com/search?q=site:${siteUrl} ${encodeURIComponent(query)}`;
                window.open(searchUrl, '_blank');
            }

            if (searchSubmit) {
                searchSubmit.addEventListener('click', performSearch);
            }

            if (searchInput) {
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch();
                    }
                });
                // Focus the search input on load
                setTimeout(() => {
                    searchInput.focus();
                }, 500);
            }

        })();
    </script>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>
