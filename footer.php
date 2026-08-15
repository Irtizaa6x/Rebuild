<?php
/**
 * footer.php
 *
 * Shared footer for the entire IrtiJa website.
 * Includes footer navigation, social links, legal info, and scripts.
 *
 * @package IrtiJa
 * @version 1.0
 */
?>

<!-- ============================================================
     PREMIUM FOOTER
     ============================================================ -->
<footer class="site-footer" role="contentinfo">
    <div class="container footer-inner">
        <!-- Brand -->
        <div class="footer-brand">
            <a href="index.php" class="footer-brand-link">
                <img src="logo.png" alt="IrtiJa" class="footer-logo" width="36" height="36" />
                <span class="footer-brand-name">Irti<span class="gold">Ja</span></span>
            </a>
            <p class="footer-tagline">
                Building secure digital futures.
            </p>
        </div>

        <!-- Footer Navigation -->
        <div class="footer-nav">
            <h4 class="footer-heading">Explore</h4>
            <ul class="footer-links">
                <li><a href="index.php">About</a></li>
                <li><a href="education.php">Education</a></li>
                <li><a href="experience.php">Experience</a></li>
                <li><a href="skills.php">Skills</a></li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <!-- Social Links -->
        <div class="footer-social">
            <h4 class="footer-heading">Connect</h4>
            <div class="social-icons">
                <a href="https://github.com/Irtizaa6x" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <a href="https://linkedin.com/in/irtija-talha" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="https://www.facebook.com/Irtija.Talha96" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.instagram.com/7d6_nev" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.x.com/irtijaXtalha" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)">
                    <i class="fab fa-x-twitter"></i>
                </a>
                <button class="discord-copy" aria-label="Copy Discord username" title="Copy Discord username">
                    <i class="fab fa-discord"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <span class="footer-copy">
                <i class="far fa-copyright"></i> 2026 Md. Irtija Azad Talha
            </span>
            <span class="footer-credit">
                Helped by <strong>Ai</strong>
            </span>
        </div>
    </div>
</footer>

<!-- ============================================================
     SCRIPTS (loaded at the end for performance)
     ============================================================ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/suncalc/1.9.0/suncalc.min.js" defer></script>
<script src="data.js" defer></script>
<script src="script.js" defer></script>
<!-- Optional: page‑specific scripts can be injected here -->
<?php if (isset($page_scripts)): ?>
    <?php echo $page_scripts; ?>
<?php endif; ?>
</body>
</html>
