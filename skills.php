<?php
/**
 * skills.php
 *
 * Skills page for IrtiJa portfolio.
 * Displays technical and professional capabilities with progress indicators.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'Skills · IrtiJa';
$page_description = 'Technical and professional capabilities of Md. Irtija Azad Talha — cybersecurity, web development, networking, and leadership skills.';
$page_canonical   = 'https://irtizaa6x.github.io/skills.php';
$current_page     = 'skills';

// --- Page-specific scripts (loaded via footer hook) ---
$page_scripts = <<<'EOT'
    <script src="skdata.js" defer></script>
    <script>
        (function() {
            'use strict';

            function init() {
                // --- Render Skill Categories ---
                const container = document.getElementById('skillsContainer');
                if (!container) return;

                if (!window.skills || typeof window.skills !== 'object') {
                    container.innerHTML = `
                        <div class="blog-empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Skills data not loaded. Please check your configuration.</p>
                        </div>
                    `;
                    return;
                }

                const skillsData = window.skills;
                // Order of categories (matches skdata.js structure)
                const categoryOrder = ['cyber', 'web', 'networking', 'professional'];

                let html = '';
                let totalSkills = 0;

                categoryOrder.forEach((key) => {
                    const cat = skillsData[key];
                    if (!cat) return;

                    totalSkills += (cat.items || []).length;

                    const itemsHtml = (cat.items || []).map((item) => {
                        const levelClass = item.level ? item.level.toLowerCase() : 'beginner';
                        return `
                            <div class="skill-item">
                                <div class="skill-info">
                                    <span class="skill-name">${item.name}</span>
                                    <span class="skill-level ${levelClass}">${item.level || 'Beginner'}</span>
                                </div>
                                <div class="skill-bar">
                                    <div class="skill-progress" style="width: ${Math.min(Math.max(item.progress || 0, 0), 100)}%;"></div>
                                </div>
                            </div>
                        `;
                    }).join('');

                    const tagsHtml = (cat.tags || []).map((tag) => {
                        return `<span class="skill-tag"><i class="fas fa-shield-alt"></i> ${tag}</span>`;
                    }).join('');

                    html += `
                        <div class="skill-category-group fade-up">
                            <div class="skill-category-header">
                                <div class="category-icon">
                                    <i class="fas ${cat.icon || 'fa-cog'}"></i>
                                </div>
                                <div>
                                    <h3 class="category-title">${cat.title || key}</h3>
                                    <p class="category-description">${cat.description || ''}</p>
                                </div>
                            </div>
                            <div class="skill-items">${itemsHtml}</div>
                            ${tagsHtml ? `<div class="skill-tags-container">${tagsHtml}</div>` : ''}
                        </div>
                    `;
                });

                container.innerHTML = html;

                // --- Update Hero Stats ---
                const statsContainer = document.getElementById('skillsStats');
                if (statsContainer) {
                    const categoryCount = Object.keys(skillsData).length;
                    statsContainer.innerHTML = `
                        <div class="hero-stat">
                            <span class="hero-stat-number">${categoryCount}</span>
                            <span class="hero-stat-label">Skill Domains</span>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">${totalSkills}+</span>
                            <span class="hero-stat-label">Individual Skills</span>
                        </div>
                        <div class="hero-stat-divider"></div>
                        <div class="hero-stat">
                            <span class="hero-stat-number">8</span>
                            <span class="hero-stat-label">Tools &amp; Technologies</span>
                        </div>
                    `;
                }

                // --- Re-trigger scroll reveal ---
                if (window.IrtiJa && window.IrtiJa.ScrollReveal) {
                    setTimeout(() => {
                        window.IrtiJa.ScrollReveal.refresh();
                    }, 200);
                }

                console.log(`✅ Skills page rendered: ${totalSkills} skills across ${Object.keys(skillsData).length} categories.`);
            }

            // Run when data is loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                if (window.skills) {
                    init();
                } else {
                    const checkData = setInterval(() => {
                        if (window.skills) {
                            clearInterval(checkData);
                            init();
                        }
                    }, 50);
                    setTimeout(() => {
                        clearInterval(checkData);
                        if (!window.skills) {
                            console.warn('Skills data not loaded. Check skdata.js script order.');
                        }
                        init();
                    }, 3000);
                }
            }
        })();
    </script>
EOT;

// --- Include the shared header ---
include 'header.php';
?>

    <!-- ============================================================
         PAGE HERO
         ============================================================ -->
    <section class="page-hero" aria-labelledby="page-hero-title">
        <div class="container">
            <div class="page-hero-content">
                <span class="section-tag">Capabilities</span>
                <h1 class="page-hero-title" id="page-hero-title">
                    Technical &amp; Professional Skills
                </h1>
                <p class="page-hero-subtitle">
                    A comprehensive overview of the technologies, tools, and
                    competencies I bring to every project.
                </p>
                <div class="page-hero-stats" id="skillsStats">
                    <!-- Dynamically populated -->
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         SKILLS GRID (DYNAMIC)
         ============================================================ -->
    <section class="skills-main" aria-labelledby="skills-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Expertise</span>
                <h2 class="section-title section-title-underline" id="skills-title">
                    My Capabilities
                </h2>
                <p class="section-subtitle">
                    Categorized skills with proficiency indicators to show my
                    level of experience and comfort.
                </p>
            </div>

            <!-- Dynamic container for skill categories -->
            <div id="skillsContainer">
                <!-- Rendered by JavaScript -->
            </div>
        </div>
    </section>

    <!-- ============================================================
         TOOL STACK (Static)
         ============================================================ -->
    <section class="tool-stack" aria-labelledby="tools-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Toolbox</span>
                <h2 class="section-title section-title-underline" id="tools-title">
                    Tools &amp; Technologies
                </h2>
                <p class="section-subtitle">
                    The tools and platforms I use daily to build, secure, and
                    deploy.
                </p>
            </div>

            <div class="tools-grid">
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-html5"></i></div>
                    <span class="tool-name">HTML5</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-css3-alt"></i></div>
                    <span class="tool-name">CSS3</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-js"></i></div>
                    <span class="tool-name">JavaScript</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-python"></i></div>
                    <span class="tool-name">Python</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-git-alt"></i></div>
                    <span class="tool-name">Git</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-github"></i></div>
                    <span class="tool-name">GitHub</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fab fa-linux"></i></div>
                    <span class="tool-name">Linux</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fas fa-shield-alt"></i></div>
                    <span class="tool-name">Wireshark</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fas fa-terminal"></i></div>
                    <span class="tool-name">Command Line</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fas fa-database"></i></div>
                    <span class="tool-name">MySQL</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fas fa-cloud"></i></div>
                    <span class="tool-name">AWS (Learning)</span>
                </div>
                <div class="tool-item fade-up">
                    <div class="tool-icon"><i class="fas fa-code-branch"></i></div>
                    <span class="tool-name">VS Code</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         SOFT SKILLS (Static)
         ============================================================ -->
    <section class="soft-skills" aria-labelledby="soft-title">
        <div class="container">
            <div class="soft-skills-card fade-up">
                <div class="soft-skills-content">
                    <span class="section-tag">Beyond Technical</span>
                    <h2 id="soft-title">Professional Qualities</h2>
                    <p>
                        What sets me apart isn't just what I know — it's how I work.
                        These qualities define my professional approach.
                    </p>
                    <div class="soft-skills-grid">
                        <div class="soft-skill">
                            <i class="fas fa-flag"></i>
                            <div>
                                <h4>Leadership</h4>
                                <p>Leading teams with integrity and purpose</p>
                            </div>
                        </div>
                        <div class="soft-skill">
                            <i class="fas fa-handshake"></i>
                            <div>
                                <h4>Integrity</h4>
                                <p>Ethical decision-making in every situation</p>
                            </div>
                        </div>
                        <div class="soft-skill">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Discipline</h4>
                                <p>Consistent, reliable, and committed</p>
                            </div>
                        </div>
                        <div class="soft-skill">
                            <i class="fas fa-users"></i>
                            <div>
                                <h4>Collaboration</h4>
                                <p>Working effectively in diverse teams</p>
                            </div>
                        </div>
                        <div class="soft-skill">
                            <i class="fas fa-bullseye"></i>
                            <div>
                                <h4>Focus</h4>
                                <p>Driven by clear goals and purpose</p>
                            </div>
                        </div>
                        <div class="soft-skill">
                            <i class="fas fa-gem"></i>
                            <div>
                                <h4>Excellence</h4>
                                <p>Always striving for the highest quality</p>
                            </div>
                        </div>
                    </div>
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
                    <h2 id="cta-title">Ready to Build Something Great?</h2>
                    <p>
                        With a strong foundation in cybersecurity, web development,
                        and leadership — I'm ready to contribute to your team.
                    </p>
                    <div class="cta-actions">
                        <a href="contact.php" class="btn btn-cta-primary">
                            <i class="fas fa-paper-plane"></i> Get in Touch
                        </a>
                        <a href="experience.php" class="btn btn-cta-secondary">
                            <i class="fas fa-briefcase"></i> View Experience
                        </a>
                    </div>
                </div>
                <div class="cta-decoration" aria-hidden="true">
                    <i class="fas fa-code-branch"></i>
                </div>
            </div>
        </div>
    </section>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>
