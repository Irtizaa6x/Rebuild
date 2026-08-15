<?php
/**
 * contact.php
 *
 * Contact page for IrtiJa portfolio.
 * Provides contact information, social links, and local time display.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'Contact · IrtiJa';
$page_description = 'Get in touch with Md. Irtija Azad Talha — cybersecurity enthusiast, CSE student, and BNCC cadet. Available for collaborations, internships, and cybersecurity opportunities.';
$page_canonical   = 'https://irtizaa6x.github.io/contact.php';
$current_page     = 'contact';

// --- Include the shared header ---
include 'header.php';
?>

    <!-- ============================================================
         PAGE HERO
         ============================================================ -->
    <section class="page-hero" aria-labelledby="page-hero-title">
        <div class="container">
            <div class="page-hero-content">
                <span class="section-tag">Connect</span>
                <h1 class="page-hero-title" id="page-hero-title">
                    Let's Connect
                </h1>
                <p class="page-hero-subtitle">
                    I'm always open to interesting conversations, collaborations,
                    and opportunities in cybersecurity and technology.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================
         AVAILABILITY STATUS
         ============================================================ -->
    <section class="availability-section" aria-label="Availability status">
        <div class="container">
            <div class="availability-card fade-up">
                <div class="availability-status">
                    <span class="status-dot"></span>
                    <span class="status-text">Available</span>
                    <span class="status-sub">— Open for cybersecurity internships, collaborations, and networking</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CONTACT METHODS (Primary)
         ============================================================ -->
    <section class="contact-methods-section" aria-labelledby="methods-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Reach Out</span>
                <h2 class="section-title section-title-underline" id="methods-title">
                    Contact Methods
                </h2>
                <p class="section-subtitle">
                    Choose the channel that works best for you — I respond to all
                    messages within 24 hours.
                </p>
            </div>

            <div class="contact-methods contact-grid primary">
                <!-- Email -->
                <div class="contact-card fade-up">
                    <div class="card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <span class="card-label">Email</span>
                    <span class="card-value">irtija.x.k6@hotmail.com</span>
                    <div class="card-action">
                        <a href="mailto:irtija.x.k6@hotmail.com" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </a>
                    </div>
                </div>

                <!-- GitHub -->
                <div class="contact-card fade-up">
                    <div class="card-icon">
                        <i class="fab fa-github"></i>
                    </div>
                    <span class="card-label">GitHub</span>
                    <span class="card-value">Irtizaa6x</span>
                    <div class="card-action">
                        <a href="https://github.com/Irtizaa6x" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm">
                            <i class="fas fa-code-branch"></i> Repositories
                        </a>
                    </div>
                </div>

                <!-- LinkedIn -->
                <div class="contact-card fade-up">
                    <div class="card-icon">
                        <i class="fab fa-linkedin-in"></i>
                    </div>
                    <span class="card-label">LinkedIn</span>
                    <span class="card-value">Md Irtija Azad Talha</span>
                    <div class="card-action">
                        <a href="https://linkedin.com/in/irtija-talha" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
                            <i class="fas fa-user-plus"></i> Connect
                        </a>
                    </div>
                </div>

                <!-- WhatsApp -->
                <div class="contact-card fade-up">
                    <div class="card-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <span class="card-label">WhatsApp</span>
                    <span class="card-value">+880 1518 940 566</span>
                    <div class="card-action">
                        <a href="https://wa.me/qr/H3R2HPTW66G3P1" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
                            <i class="fab fa-whatsapp"></i> Chat
                        </a>
                    </div>
                </div>

                <!-- Discord (with copy) -->
                <div class="contact-card fade-up">
                    <div class="card-icon">
                        <i class="fab fa-discord"></i>
                    </div>
                    <span class="card-label">Discord</span>
                    <span class="card-value">naz.irt.k6</span>
                    <div class="card-action">
                        <button class="btn btn-outline btn-sm discord-copy" aria-label="Copy Discord username">
                            <i class="fas fa-copy"></i> Copy Username
                        </button>
                    </div>
                </div>

                <!-- Phone (secondary) -->
                <div class="contact-card fade-up">
                    <div class="card-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <span class="card-label">Phone</span>
                    <span class="card-value">+880 1886 940 566</span>
                    <div class="card-action">
                        <a href="tel:+8801886940566" class="btn btn-outline btn-sm">
                            <i class="fas fa-phone"></i> Call
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         SOCIAL MEDIA
         ============================================================ -->
    <section class="social-section" aria-labelledby="social-title">
        <div class="container">
            <div class="social-section-header">
                <span class="section-tag">Follow</span>
                <h2 id="social-title">
                    <i class="fas fa-share-alt"></i> Social Media
                </h2>
                <p>Follow me on social media for updates, insights, and more.</p>
            </div>

            <div class="social-grid">
                <a href="https://www.facebook.com/Irtija.Talha96" target="_blank" rel="noopener noreferrer" class="social-btn facebook fade-up">
                    <i class="fab fa-facebook-f"></i>
                    <span>Facebook</span>
                </a>
                <a href="https://www.instagram.com/7d6_nev" target="_blank" rel="noopener noreferrer" class="social-btn instagram fade-up">
                    <i class="fab fa-instagram"></i>
                    <span>Instagram</span>
                </a>
                <a href="https://www.x.com/irtijaXtalha" target="_blank" rel="noopener noreferrer" class="social-btn twitter fade-up">
                    <i class="fab fa-x-twitter"></i>
                    <span>X (Twitter)</span>
                </a>
                <a href="https://www.threads.com/7d6_nev" target="_blank" rel="noopener noreferrer" class="social-btn threads fade-up">
                    <i class="fab fa-threads"></i>
                    <span>Threads</span>
                </a>
                <a href="https://github.com/Irtizaa6x" target="_blank" rel="noopener noreferrer" class="social-btn github fade-up">
                    <i class="fab fa-github"></i>
                    <span>GitHub</span>
                </a>
                <a href="https://linkedin.com/in/irtija-talha" target="_blank" rel="noopener noreferrer" class="social-btn linkedin fade-up">
                    <i class="fab fa-linkedin-in"></i>
                    <span>LinkedIn</span>
                </a>
                <a href="https://wa.me/qr/H3R2HPTW66G3P1" target="_blank" rel="noopener noreferrer" class="social-btn whatsapp fade-up">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <button class="social-btn discord discord-copy fade-up" aria-label="Copy Discord username">
                    <i class="fab fa-discord"></i>
                    <span>Discord</span>
                </button>
            </div>
        </div>
    </section>

    <!-- ============================================================
         PHONE & LOCAL TIME
         ============================================================ -->
    <section class="phone-time-section" aria-labelledby="phone-time-title">
        <div class="container">
            <div class="phone-card fade-up">
                <div class="phone-card-inner">
                    <div class="phone-numbers">
                        <div class="phone-item">
                            <i class="fas fa-phone-alt"></i>
                            <span class="phone-label">Primary</span>
                            <a href="tel:+8801518940566" class="phone-number">+880 1518 940 566</a>
                        </div>
                        <span class="phone-divider" aria-hidden="true"></span>
                        <div class="phone-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span class="phone-label">Secondary</span>
                            <a href="tel:+8801886940566" class="phone-number">+880 1886 940 566</a>
                        </div>
                    </div>

                    <!-- Local Time Widget -->
                    <div class="local-time-wrapper" id="localTimeWrapper">
                        <span class="live-indicator"></span>
                        <div class="astro-display" id="astroDisplay">
                            <div class="sun"></div>
                        </div>
                        <span class="time-label">
                            <i class="fas fa-clock"></i> Local Time
                        </span>
                        <span class="time-digital dhaka-time">--:--:--</span>
                        <span class="time-zone">(Asia/Dhaka UTC+6:00)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CALL TO ACTION (Final)
         ============================================================ -->
    <section class="cta-section" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 id="cta-title">Ready to Build Something Great?</h2>
                    <p>
                        Whether it's a cybersecurity project, a collaboration, or
                        just a conversation about tech — I'd love to hear from you.
                    </p>
                    <div class="cta-actions">
                        <a href="mailto:irtija.x.k6@hotmail.com" class="btn btn-cta-primary">
                            <i class="fas fa-paper-plane"></i> Send Email
                        </a>
                        <a href="https://linkedin.com/in/irtija-talha" target="_blank" rel="noopener noreferrer" class="btn btn-cta-secondary">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </a>
                    </div>
                </div>
                <div class="cta-decoration" aria-hidden="true">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
    </section>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>
