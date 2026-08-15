<?php
/**
 * experience.php
 *
 * Experience page for IrtiJa portfolio.
 * Displays professional experience, leadership roles, certifications, and activities.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'Experience · IrtiJa';
$page_description = 'Professional experience, leadership roles, certifications, and activities of Md. Irtija Azad Talha.';
$page_canonical   = 'https://irtizaa6x.github.io/experience.php';
$current_page     = 'experience';

// --- Include the shared header ---
include 'header.php';
?>

    <!-- ============================================================
         PAGE HERO
         ============================================================ -->
    <section class="page-hero" aria-labelledby="page-hero-title">
        <div class="container">
            <div class="page-hero-content">
                <span class="section-tag">Experience</span>
                <h1 class="page-hero-title" id="page-hero-title">
                    Professional Journey
                </h1>
                <p class="page-hero-subtitle">
                    Building skills beyond the classroom — leadership, cybersecurity,
                    and real-world experience.
                </p>
                <div class="page-hero-stats" id="experienceStats">
                    <!-- Dynamically populated -->
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CAREER OBJECTIVE
         ============================================================ -->
    <section class="career-objective" aria-labelledby="objective-title">
        <div class="container">
            <div class="objective-card fade-up">
                <div class="objective-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="objective-content">
                    <span class="section-tag">Mission</span>
                    <h2 id="objective-title">Career Objective</h2>
                    <p>
                        To join a forward-thinking security team where I can apply my
                        growing technical knowledge, learn from experienced professionals,
                        and contribute to protecting critical digital infrastructure.
                        I'm seeking opportunities that challenge me to grow while making
                        security more accessible and robust.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         EXPERIENCE TIMELINE (Dynamic)
         ============================================================ -->
    <section class="experience-timeline" aria-labelledby="timeline-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Timeline</span>
                <br/>
                <h2 class="section-title section-title-underline" id="timeline-title">
                    Activities &amp; Leadership
                </h2>
                <p class="section-subtitle">
                    A chronological journey through my professional and
                    extracurricular experiences.
                </p>
            </div>

            <div class="timeline-container" id="timelineContainer">
                <!-- Dynamically populated -->
            </div>
        </div>
    </section>

    <!-- ============================================================
         CERTIFICATIONS & ACHIEVEMENTS (Dynamic)
         ============================================================ -->
    <section class="certifications-section" aria-labelledby="certs-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Credentials</span>
                <br/>
                <h2 class="section-title section-title-underline" id="certs-title">
                    Certifications &amp; Achievements
                </h2>
                <p class="section-subtitle">
                    Professional certifications and recognitions demonstrating
                    commitment to continuous learning.
                </p>
            </div>

            <div class="cert-grid" id="certGrid">
                <!-- Dynamically populated -->
            </div>
        </div>
    </section>

    <!-- ============================================================
         SKILLS HIGHLIGHT (Static)
         ============================================================ -->
    <section class="skills-highlight" aria-labelledby="skills-highlight-title">
        <div class="container">
            <div class="skills-highlight-card fade-up">
                <div class="skills-highlight-content">
                    <span class="section-tag">Capabilities</span>
                    <h2 id="skills-highlight-title">Skills That Drive Results</h2>
                    <p>
                        From cybersecurity fundamentals to web development and
                        professional leadership — here's what I bring to the table.
                    </p>
                    <div class="skills-highlight-tags">
                        <span class="skill-tag"><i class="fas fa-shield-alt"></i> Threat Analysis</span>
                        <span class="skill-tag"><i class="fas fa-shield-alt"></i> Vulnerability Assessment</span>
                        <span class="skill-tag"><i class="fas fa-code"></i> JavaScript</span>
                        <span class="skill-tag"><i class="fas fa-code"></i> Python</span>
                        <span class="skill-tag"><i class="fas fa-network-wired"></i> TCP/IP</span>
                        <span class="skill-tag"><i class="fas fa-network-wired"></i> DNS</span>
                        <span class="skill-tag"><i class="fas fa-briefcase"></i> Leadership</span>
                        <span class="skill-tag"><i class="fas fa-briefcase"></i> Teamwork</span>
                        <span class="skill-tag"><i class="fas fa-briefcase"></i> Discipline</span>
                    </div>
                    <a href="skills.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> View All Capabilities
                    </a>
                </div>
                <div class="skills-highlight-decoration" aria-hidden="true">
                    <i class="fas fa-code-branch"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CALL TO ACTION
         ============================================================ -->
    <section class="cta-section" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 id="cta-title">Ready to Collaborate?</h2>
                    <p>
                        I'm always open to interesting conversations, collaborations,
                        and opportunities in cybersecurity and technology.
                    </p>
                    <div class="cta-actions">
                        <a href="contact.php" class="btn btn-cta-primary">
                            <i class="fas fa-paper-plane"></i> Get in Touch
                        </a>
                        <a href="blog.php" class="btn btn-cta-secondary">
                            <i class="fas fa-code-branch"></i> Explore Projects
                        </a>
                    </div>
                </div>
                <div class="cta-decoration" aria-hidden="true">
                    <i class="fas fa-briefcase"></i>
                </div>
            </div>
        </div>
    </section>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>

<!-- ============================================================
     PAGE-SPECIFIC SCRIPTS (loaded after footer)
     ============================================================ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/suncalc/1.9.0/suncalc.min.js" defer></script>
<script src="data.js" defer></script>
<script src="expdata.js" defer></script>
<script>
    (function() {
        'use strict';

        // --- Wait for data to be ready ---
        function init() {
            // 1. Render Experience Timeline
            const timelineContainer = document.getElementById('timelineContainer');
            if (timelineContainer && window.experiences && window.experiences.length) {
                const experiences = window.experiences;

                // Sort by startDate (newest first)
                const sorted = [...experiences].sort((a, b) => {
                    const dateA = new Date(a.startDate);
                    const dateB = new Date(b.startDate);
                    return dateB - dateA;
                });

                let html = '';
                sorted.forEach((exp) => {
                    const startYear = exp.startDate ? new Date(exp.startDate).getFullYear() : '';
                    const endYear = exp.endDate ? new Date(exp.endDate).getFullYear() : 'Present';
                    const dateStr = startYear ? `${startYear} – ${endYear}` : '';

                    const certButtonsHtml = (exp.certButtons || []).map(btn =>
                        `<a href="${btn.url}" class="btn-outline" target="${btn.url.startsWith('http') ? '_blank' : '_self'}"><i class="${btn.icon}"></i> ${btn.label}</a>`
                    ).join('');

                    html += `
                        <div class="timeline-entry fade-up">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                ${dateStr ? `<div class="timeline-date">${dateStr}</div>` : ''}
                                <h4>
                                    <i class="fas ${exp.icon || 'fa-briefcase'}"></i>
                                    ${exp.title}
                                </h4>
                                <div class="meta-inline">
                                    <span><i class="fas fa-user-check"></i> ${exp.role || ''}</span>
                                    ${exp.parentClub ? `<span><i class="fas fa-tag"></i> ${exp.parentClub}</span>` : ''}
                                </div>
                                <p class="key-points-single">${exp.description}</p>
                                ${certButtonsHtml ? `<div class="cert-buttons">${certButtonsHtml}</div>` : ''}
                            </div>
                        </div>
                    `;
                });

                timelineContainer.innerHTML = html;
            }

            // 2. Render Certifications
            const certGrid = document.getElementById('certGrid');
            if (certGrid && window.certifications && window.certifications.length) {
                const certifications = window.certifications;

                let html = '';
                certifications.forEach((cert) => {
                    const statusClass = cert.ongoing ? 'ongoing' : 'completed';
                    const statusLabel = cert.ongoing ? 'Ongoing' : 'Completed';

                    html += `
                        <div class="cert-card fade-up">
                            <div class="cert-card-header">
                                <div class="cert-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <span class="ongoing-badge ${statusClass}">${statusLabel}</span>
                            </div>
                            <h3>${cert.title}</h3>
                            <p class="cert-issuer">${cert.issuer}</p>
                            <p class="cert-description">${cert.description}</p>
                            ${cert.link ? `
                                <div class="cert-actions">
                                    <a href="${cert.link}" class="btn-outline">
                                        <i class="fas fa-external-link-alt"></i> View Details
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });

                certGrid.innerHTML = html;
            }

            // 3. Update hero stats
            const statsContainer = document.getElementById('experienceStats');
            if (statsContainer) {
                const expCount = (window.experiences || []).length;
                const certCount = (window.certifications || []).length;
                statsContainer.innerHTML = `
                    <div class="hero-stat">
                        <span class="hero-stat-number">${expCount}</span>
                        <span class="hero-stat-label">Organizations</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">${certCount}</span>
                        <span class="hero-stat-label">Certifications</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">2+</span>
                        <span class="hero-stat-label">Years of Activity</span>
                    </div>
                `;
            }

            // 4. Re-trigger scroll reveal
            if (window.IrtiJa && window.IrtiJa.ScrollReveal) {
                setTimeout(() => {
                    window.IrtiJa.ScrollReveal.refresh();
                }, 200);
            }
        }

        // Run when DOM is ready and data is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            // If scripts are deferred, they might still load after DOMContentLoaded.
            // Use a small delay or check for data.
            if (window.experiences && window.certifications) {
                init();
            } else {
                // Wait a bit for the scripts to load
                const checkData = setInterval(() => {
                    if (window.experiences && window.certifications) {
                        clearInterval(checkData);
                        init();
                    }
                }, 50);
                // Fallback after 3 seconds
                setTimeout(() => {
                    clearInterval(checkData);
                    if (!window.experiences || !window.certifications) {
                        console.warn('Experience or Certification data not loaded. Check your script order.');
                        // Still try to render with what we have
                        init();
                    }
                }, 3000);
            }
        }

    })();
</script>
</body>
</html>
