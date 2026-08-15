<?php
/**
 * index.php
 *
 * Main homepage for IrtiJa portfolio.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'IrtiJa · Cybersecurity & CSE Portfolio';
$page_description = 'Md. Irtija Azad Talha — CSE student at Green University of Bangladesh, cybersecurity enthusiast, BNCC cadet. Explore my work and journey.';
$page_canonical   = 'https://irtizaa6x.github.io/';
$current_page     = 'index';

// --- Include the shared header ---
include 'header.php';
?>

    <!-- ============================================================
         HERO — Full viewport, magnetic first impression
         ============================================================ -->
    <section class="hero" id="hero" aria-labelledby="hero-title">
        <div class="container hero-inner">
            <div class="hero-content">
                <!-- Badge / status -->
                <div class="hero-badge">
                    <span class="live-dot"></span>
                    Open for collaborations &amp; internships
                </div>

                <!-- Main headline -->
                <h1 class="hero-title" id="hero-title">
                    Md. Irtija<br />
                    <span class="gold">Azad Talha</span>
                </h1>

                <!-- Tagline -->
                <p class="hero-subtitle">
                    Computer Science &amp; Engineering · Cybersecurity
                </p>

                <!-- Description -->
                <p class="hero-description">
                    Building secure digital futures — one line of code at a time.
                    CSE student at Green University of Bangladesh, cybersecurity
                    enthusiast, and BNCC cadet.
                </p>

                <!-- CTAs -->
                <div class="hero-actions">
                    <a href="#about" class="btn btn-primary">
                        <i class="fas fa-arrow-down"></i> Explore My Work
                    </a>
                    <a href="contact.php" class="btn btn-secondary">
                        <i class="fas fa-paper-plane"></i> Let's Connect
                    </a>
                </div>
            </div>

            <!-- Hero visual (profile photo + decorative shapes) -->
            <div class="hero-visual">
                <div class="hero-image-wrapper">
                    <img src="Talha.jpg" alt="Md. Irtija Azad Talha — profile photo" class="hero-image" width="400" height="400" loading="eager" />
                    <div class="hero-ring"></div>
                    <div class="hero-ring hero-ring-2"></div>
                </div>
            </div>
        </div>

        <!-- Subtle scroll indicator -->
        <div class="scroll-indicator" aria-hidden="true">
            <span class="scroll-line"></span>
            <span class="scroll-label">Scroll</span>
        </div>
    </section>

    <!-- ============================================================
         QUICK STATS — Count-up placeholders
         ============================================================ -->
    <section class="stats" aria-label="Quick statistics">
        <div class="container stats-grid">
            <div class="stat-item">
                <span class="stat-number" data-count="3.14">3.14</span>
                <span class="stat-label">CGPA</span>
                <span class="stat-sub">B.Sc. in CSE</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-count="5">0</span>
                <span class="stat-label">Projects</span>
                <span class="stat-sub">&amp; Counting</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-count="4">0</span>
                <span class="stat-label">Certifications</span>
                <span class="stat-sub">Cybersecurity</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-count="2">0</span>
                <span class="stat-label">Years of Study</span>
                <span class="stat-sub">CSE Program</span>
            </div>
        </div>
    </section>

    <!-- ============================================================
         ABOUT ME — Professional introduction
         ============================================================ -->
    <section class="about" id="about" aria-labelledby="about-title">
        <div class="container">
            <div class="about-grid">
                <!-- Left column: main text -->
                <div class="about-content">
                    <span class="section-tag">About Me</span>
                    <h2 class="section-title" id="about-title">
                        I'm Irtija.
                    </h2>
                    <p class="about-lead">
                        I'm a CSE student at Green University of Bangladesh with
                        a singular focus: <strong>cybersecurity</strong>. I'm
                        driven by the challenge of protecting digital systems
                        and understanding how networks are secured at every
                        layer.
                    </p>
                    <p>
                        When I'm not studying cryptography or network security,
                        I'm leading teams as a BNCC cadet — where I've honed
                        discipline, leadership, and the ability to perform under
                        pressure.
                    </p>
                    <p class="about-mission">
                        <i class="fas fa-bullseye gold-icon"></i>
                        <strong>My mission:</strong> to build the technical
                        depth and real-world experience needed to become a
                        cybersecurity professional who makes a tangible
                        difference.
                    </p>
                    <div class="about-actions">
                        <a href="education.php" class="btn btn-primary">
                            <i class="fas fa-graduation-cap"></i> View Education
                        </a>
                        <a href="experience.php" class="btn btn-ghost">
                            <i class="fas fa-briefcase"></i> Experience
                        </a>
                    </div>
                </div>

                <!-- Right column: quick personal details -->
                <div class="about-details">
                    <div class="detail-card">
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <div>
                                <span class="detail-label">Name</span>
                                <span class="detail-value">Md. Irtija Azad Talha</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-pin"></i>
                            <div>
                                <span class="detail-label">Location</span>
                                <span class="detail-value">Dhaka, Bangladesh</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-university"></i>
                            <div>
                                <span class="detail-label">University</span>
                                <span class="detail-value">Green University of Bangladesh</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <span class="detail-label">Focus</span>
                                <span class="detail-value">Cybersecurity &amp; Network Security</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-flag"></i>
                            <div>
                                <span class="detail-label">Nationality</span>
                                <span class="detail-value">Bangladeshi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         FEATURED SKILLS — Preview of capabilities
         ============================================================ -->
    <section class="featured-skills" aria-labelledby="skills-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Capabilities</span>
                <h2 class="section-title" id="skills-title">
                    Core Skills
                </h2>
                <p class="section-subtitle">
                    A preview of the technologies and competencies I work with
                    every day.
                </p>
            </div>

            <div class="skills-preview-grid">
                <!-- Cybersecurity -->
                <div class="skill-preview-card fade-up">
                    <div class="skill-preview-icon">
                        <i class="fas fa-user-secret"></i>
                    </div>
                    <h3>Cybersecurity</h3>
                    <div class="skill-tags">
                        <span class="skill-tag">Threat Analysis</span>
                        <span class="skill-tag">Vulnerability Assessment</span>
                        <span class="skill-tag">Defensive Strategies</span>
                        <span class="skill-tag">Penetration Testing</span>
                        <span class="skill-tag">Incident Response</span>
                    </div>
                </div>

                <!-- Web Dev & Programming -->
                <div class="skill-preview-card fade-up">
                    <div class="skill-preview-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>Web Dev &amp; Programming</h3>
                    <div class="skill-tags">
                        <span class="skill-tag">C</span>
                        <span class="skill-tag">JavaScript</span>
                        <span class="skill-tag">Python</span>
                        <span class="skill-tag">HTML5</span>
                        <span class="skill-tag">CSS3</span>
                    </div>
                </div>

                <!-- Networking -->
                <div class="skill-preview-card fade-up">
                    <div class="skill-preview-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <h3>Networking &amp; Web Tech</h3>
                    <div class="skill-tags">
                        <span class="skill-tag">TCP/IP</span>
                        <span class="skill-tag">DNS</span>
                        <span class="skill-tag">Firewalls</span>
                        <span class="skill-tag">Cloud Computing</span>
                        <span class="skill-tag">Linux</span>
                    </div>
                </div>

                <!-- Professional Skills -->
                <div class="skill-preview-card fade-up">
                    <div class="skill-preview-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Professional Skills</h3>
                    <div class="skill-tags">
                        <span class="skill-tag">Leadership</span>
                        <span class="skill-tag">Teamwork</span>
                        <span class="skill-tag">Discipline</span>
                        <span class="skill-tag">Time Management</span>
                        <span class="skill-tag">Communication</span>
                    </div>
                </div>
            </div>

            <div class="skills-cta">
                <a href="skills.php" class="btn btn-ghost">
                    <i class="fas fa-arrow-right"></i> View All Capabilities
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================
         FEATURED EXPERIENCE — Preview of timeline
         ============================================================ -->
    <section class="featured-experience" aria-labelledby="experience-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Experience</span>
                <h2 class="section-title" id="experience-title">
                    Professional Journey
                </h2>
                <p class="section-subtitle">
                    A snapshot of my activities and leadership roles.
                </p>
            </div>

            <div class="experience-preview">
                <!-- Entry 1: GUCC Cyber Security Society -->
                <div class="exp-preview-item fade-up">
                    <div class="exp-preview-marker"></div>
                    <div class="exp-preview-content">
                        <span class="exp-preview-date">2025 – Present</span>
                        <h3>Member · GUCC Cyber Security Society</h3>
                        <p>
                            Actively participating in cybersecurity workshops,
                            CTF competitions, and network security discussions.
                            Learning the fundamentals of threat analysis and
                            defensive strategies.
                        </p>
                    </div>
                </div>

                <!-- Entry 2: BNCC -->
                <div class="exp-preview-item fade-up">
                    <div class="exp-preview-marker"></div>
                    <div class="exp-preview-content">
                        <span class="exp-preview-date">2024 – Present</span>
                        <h3>Cadet · BNCC Green University Platoon</h3>
                        <p>
                            Developed leadership, discipline, and teamwork
                            through rigorous training. Performed under pressure
                            and learned the value of responsibility and
                            integrity.
                        </p>
                    </div>
                </div>

                <!-- Entry 3: B.Sc. in CSE -->
                <div class="exp-preview-item fade-up">
                    <div class="exp-preview-marker"></div>
                    <div class="exp-preview-content">
                        <span class="exp-preview-date">2025 – Present</span>
                        <h3>B.Sc. in Computer Science &amp; Engineering</h3>
                        <p>
                            Green University of Bangladesh — CGPA 3.14.
                            Coursework includes Data Structures, Discrete
                            Mathematics, Linear Algebra, and Network Security.
                        </p>
                    </div>
                </div>
            </div>

            <div class="experience-cta">
                <a href="experience.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Full Experience Timeline
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CALL TO ACTION — Final engagement
         ============================================================ -->
    <section class="cta-section" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta-card">
                <div class="cta-content">
                    <h2 id="cta-title">Let's Build Something Secure Together</h2>
                    <p>
                        Whether it's a collaboration, a cybersecurity project,
                        or just a conversation about tech — I'd love to hear
                        from you.
                    </p>
                    <div class="cta-actions">
                        <a href="contact.php" class="btn btn-cta-primary">
                            <i class="fas fa-paper-plane"></i> Get in Touch
                        </a>
                        <a href="blog.php" class="btn btn-cta-secondary">
                            <i class="fas fa-code-branch"></i> View My Work
                        </a>
                    </div>
                </div>
                <!-- Decorative element -->
                <div class="cta-decoration" aria-hidden="true">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
        </div>
    </section>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>
