<?php
/**
 * education.php
 *
 * Education page for IrtiJa portfolio.
 * Displays academic qualifications, degrees, and achievements.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'Education · IrtiJa';
$page_description = 'Educational qualifications of Md. Irtija Azad Talha — B.Sc. in CSE, HSC, and SSC academic achievements.';
$page_canonical   = 'https://irtizaa6x.github.io/education.php';
$current_page     = 'education';

// --- Page-specific styles ---
$page_styles = '
    /* Achievements grid - 2 columns with spacing */
    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-5);
        margin-top: var(--space-4);
    }

    /* Make it single column on smaller screens */
    @media (max-width: 768px) {
        .achievements-grid {
            grid-template-columns: 1fr;
            gap: var(--space-4);
        }
    }
';

// --- Include the shared header ---
include 'header.php';
?>

    <!-- ============================================================
         PAGE HERO
         ============================================================ -->
    <section class="page-hero" aria-labelledby="page-hero-title">
        <div class="container">
            <div class="page-hero-content">
                <span class="section-tag">Education</span>
                <h1 class="page-hero-title" id="page-hero-title">
                    Academic Journey
                </h1>
                <p class="page-hero-subtitle">
                    A foundation built on excellence — from secondary school
                    to university.
                </p>
                <div class="page-hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number">3.14</span>
                        <span class="hero-stat-label">Current CGPA</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">5.00</span>
                        <span class="hero-stat-label">HSC GPA</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">4.72</span>
                        <span class="hero-stat-label">SSC GPA</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         DEGREE CARDS (Detailed)
         ============================================================ -->
    <section class="degree-cards" aria-labelledby="degrees-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Qualifications</span>
                <br />
                <h2 class="section-title section-title-underline" id="degrees-title">
                    Degrees &amp; Certificates
                </h2>
                <p class="section-subtitle">
                    Detailed overview of my academic achievements.
                </p>
            </div>

            <div class="degrees-grid">
                <!-- B.Sc. Card -->
                <div class="degree-card card">
                    <div class="degree-card-header">
                        <div class="degree-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <span class="degree-status ongoing">Ongoing</span>
                    </div>
                    <h3 class="degree-title">B.Sc. in Computer Science &amp; Engineering</h3>
                    <p class="degree-institution">Green University of Bangladesh</p>
                    <div class="degree-meta">
                        <span class="degree-gpa"><i class="fas fa-star"></i> CGPA: 3.14</span>
                        <span class="degree-year"><i class="far fa-calendar-alt"></i> 2025 – Present</span>
                    </div>
                    <p class="degree-description">
                        Currently in the 4<sup>th</sup> semester (Trimester). Coursework
                        includes Data Structures, Discrete Mathematics, Linear Algebra,
                        and Network Security.
                    </p>
                    <button
                        class="btn-toggle-details"
                        data-target="bsc-details"
                        aria-controls="bsc-details"
                        aria-expanded="false"
                    >
                        <i class="fas fa-chevron-down"></i> View Semester Details
                    </button>
                    <div id="bsc-details" class="details-content" role="region" aria-label="B.Sc. semester details">
                        <div class="details-inner">
                            <p class="details-note">
                                <i class="fas fa-info-circle"></i>
                                Detailed academic transcript — semester-wise course
                                breakdown with credits, grades, and grade points.
                            </p>

                            <!-- Semester 250 -->
                            <div class="semester-group">
                                <h4><i class="fas fa-calendar-alt"></i> Semester 250</h4>
                                <div class="table-responsive">
                                    <table class="academic-table">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Title</th>
                                                <th>Credits</th>
                                                <th>Grade</th>
                                                <th>Grade Point</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>CSE 100</td>
                                                <td>Computational Thinking and Problem Solving</td>
                                                <td>1.5</td>
                                                <td>A+</td>
                                                <td>4.00</td>
                                            </tr>
                                            <tr>
                                                <td>CSE 101-CSE(181)</td>
                                                <td>Discrete Mathematics</td>
                                                <td>3</td>
                                                <td>A-</td>
                                                <td>3.50</td>
                                            </tr>
                                            <tr>
                                                <td>ESP 009</td>
                                                <td>Academic English</td>
                                                <td>0</td>
                                                <td>A</td>
                                                <td>3.75</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Semester 252 -->
                            <div class="semester-group">
                                <h4><i class="fas fa-calendar-alt"></i> Semester 252</h4>
                                <div class="table-responsive">
                                    <table class="academic-table">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Title</th>
                                                <th>Credits</th>
                                                <th>Grade</th>
                                                <th>Grade Point</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>CSE 103-CSE(181)</td>
                                                <td>Structured Programming</td>
                                                <td>3</td>
                                                <td>C+</td>
                                                <td>2.50</td>
                                            </tr>
                                            <tr>
                                                <td>CHE 101-CSE(181)</td>
                                                <td>Chemistry</td>
                                                <td>3</td>
                                                <td>B</td>
                                                <td>3.00</td>
                                            </tr>
                                            <tr>
                                                <td>CSE 104-CSE(181)</td>
                                                <td>Structured Programming Lab</td>
                                                <td>1.5</td>
                                                <td>A-</td>
                                                <td>3.50</td>
                                            </tr>
                                            <tr>
                                                <td>ESP 101</td>
                                                <td>Academic English I</td>
                                                <td>3</td>
                                                <td>B-</td>
                                                <td>2.75</td>
                                            </tr>
                                            <tr>
                                                <td>MAT 101(V1)</td>
                                                <td>Calculus for Computing</td>
                                                <td>3</td>
                                                <td>C+</td>
                                                <td>2.50</td>
                                            </tr>
                                            <tr>
                                                <td>CHE 102-CSE(181)</td>
                                                <td>Chemistry Lab</td>
                                                <td>1</td>
                                                <td>A+</td>
                                                <td>4.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Semester 261 -->
                            <div class="semester-group">
                                <h4><i class="fas fa-calendar-alt"></i> Semester 261</h4>
                                <div class="table-responsive">
                                    <table class="academic-table">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Title</th>
                                                <th>Credits</th>
                                                <th>Grade</th>
                                                <th>Grade Point</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>CSE 205 (V1)</td>
                                                <td>Data Structures</td>
                                                <td>3</td>
                                                <td>A-</td>
                                                <td>3.50</td>
                                            </tr>
                                            <tr>
                                                <td>EEE 101</td>
                                                <td>Introduction to Electrical Engineering</td>
                                                <td>3</td>
                                                <td>B</td>
                                                <td>3.00</td>
                                            </tr>
                                            <tr>
                                                <td>MAT 103(V1)</td>
                                                <td>Linear Algebra and Vector Analysis</td>
                                                <td>3</td>
                                                <td>B</td>
                                                <td>3.00</td>
                                            </tr>
                                            <tr>
                                                <td>CSE 206 (V1)</td>
                                                <td>Data Structures Lab</td>
                                                <td>1.5</td>
                                                <td>B+</td>
                                                <td>3.25</td>
                                            </tr>
                                            <tr>
                                                <td>PHY 101-CSE(181)</td>
                                                <td>Physics I</td>
                                                <td>3</td>
                                                <td>B+</td>
                                                <td>3.25</td>
                                            </tr>
                                            <tr>
                                                <td>EEE 102 (V1)</td>
                                                <td>Introduction to Electrical Engineering Lab</td>
                                                <td>1</td>
                                                <td>A+</td>
                                                <td>4.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <p class="details-footer">
                                <i class="fas fa-file-alt"></i>
                                Complete transcripts available upon request.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- HSC Card -->
                <div class="degree-card card">
                    <div class="degree-card-header">
                        <div class="degree-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <span class="degree-status completed">Completed</span>
                    </div>
                    <h3 class="degree-title">Higher Secondary Certificate (HSC)</h3>
                    <p class="degree-institution">Giasuddin Islamic Model College</p>
                    <div class="degree-meta">
                        <span class="degree-gpa"><i class="fas fa-star"></i> GPA: 5.00 / 5.00</span>
                        <span class="degree-year"><i class="far fa-calendar-alt"></i> 2024</span>
                    </div>
                    <p class="degree-description">
                        Science background with distinction. Subjects included
                        Physics, Chemistry, Biology, and Higher Mathematics.
                        Board: Dhaka.
                    </p>
                    <button
                        class="btn-toggle-details"
                        data-target="hsc-details"
                        aria-controls="hsc-details"
                        aria-expanded="false"
                    >
                        <i class="fas fa-chevron-down"></i> View Subject Details
                    </button>
                    <div id="hsc-details" class="details-content" role="region" aria-label="HSC subject details">
                        <div class="details-inner">
                            <p class="details-note">
                                <i class="fas fa-info-circle"></i>
                                Subject-wise grade breakdown for Higher Secondary
                                Certificate (HSC).
                            </p>
                            <div class="table-responsive">
                                <table class="academic-table">
                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject Name</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>101</td><td>BANGLA</td><td>A</td></tr>
                                        <tr><td>107</td><td>ENGLISH</td><td>A</td></tr>
                                        <tr><td>275</td><td>INFORMATION &amp; COMMUNICATION TECHNOLOGY</td><td>A+</td></tr>
                                        <tr><td>174</td><td>PHYSICS</td><td>A+</td></tr>
                                        <tr><td>176</td><td>CHEMISTRY</td><td>A+</td></tr>
                                        <tr><td>178</td><td>BIOLOGY</td><td>A+</td></tr>
                                        <tr><td>265</td><td>HIGHER MATHEMATICS</td><td>A+</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="details-footer">
                                <i class="fas fa-check-circle"></i>
                                HSC result published by Dhaka Board, 2024.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SSC Card -->
                <div class="degree-card card">
                    <div class="degree-card-header">
                        <div class="degree-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <span class="degree-status completed">Completed</span>
                    </div>
                    <h3 class="degree-title">Secondary School Certificate (SSC)</h3>
                    <p class="degree-institution">Rafiqul Islam School &amp; College</p>
                    <div class="degree-meta">
                        <span class="degree-gpa"><i class="fas fa-star"></i> GPA: 4.72 / 5.00</span>
                        <span class="degree-year"><i class="far fa-calendar-alt"></i> 2022</span>
                    </div>
                    <p class="degree-description">
                        Science background with strong performance in Mathematics
                        and Sciences. Board: Dhaka.
                    </p>
                    <button
                        class="btn-toggle-details"
                        data-target="ssc-details"
                        aria-controls="ssc-details"
                        aria-expanded="false"
                    >
                        <i class="fas fa-chevron-down"></i> View Subject Details
                    </button>
                    <div id="ssc-details" class="details-content" role="region" aria-label="SSC subject details">
                        <div class="details-inner">
                            <p class="details-note">
                                <i class="fas fa-info-circle"></i>
                                Subject-wise grade breakdown for Secondary School
                                Certificate (SSC).
                            </p>
                            <div class="table-responsive">
                                <table class="academic-table">
                                    <thead>
                                        <tr>
                                            <th>Subject Code</th>
                                            <th>Subject Name</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>101</td><td>BANGLA</td><td>A</td></tr>
                                        <tr><td>107</td><td>ENGLISH</td><td>A+</td></tr>
                                        <tr><td>109</td><td>MATHEMATICS</td><td>A+</td></tr>
                                        <tr><td>150</td><td>BANGLADESH AND GLOBAL STUDIES</td><td>B</td></tr>
                                        <tr><td>111</td><td>ISLAM AND MORAL EDUCATION</td><td>A-</td></tr>
                                        <tr><td>136</td><td>PHYSICS</td><td>A+</td></tr>
                                        <tr><td>137</td><td>CHEMISTRY</td><td>A+</td></tr>
                                        <tr><td>126</td><td>HIGHER MATHEMATICS</td><td>A+</td></tr>
                                        <tr><td>154</td><td>INFORMATION AND COMMUNICATION TECHNOLOGY</td><td>A</td></tr>
                                        <tr><td>138</td><td>BIOLOGY</td><td>A+</td></tr>
                                        <tr><td>147</td><td>PHYSICAL EDUCATION, HEALTH AND SPORTS</td><td>A+</td></tr>
                                        <tr><td>156</td><td>CAREER EDUCATION</td><td>A+</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="details-footer">
                                <i class="fas fa-check-circle"></i>
                                SSC result published by Dhaka Board, 2022.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <br />

    <!-- ============================================================
         ACADEMIC ACHIEVEMENTS
         ============================================================ -->
    <section class="academic-achievements" aria-labelledby="achievements-title">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Honors</span>
                <br />
                <h2 class="section-title section-title-underline" id="achievements-title">
                    Academic Achievements
                </h2>
                <p class="section-subtitle">
                    Recognitions and milestones along the way.
                </p>
            </div>

            <div class="achievements-grid">
                <div class="achievement-card card fade-up">
                    <div class="achievement-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>HSC GPA 5.00</h3>
                    <p>
                        Achieved a perfect GPA of 5.00 in the Higher Secondary
                        Certificate examination from Dhaka Board.
                    </p>
                    <span class="achievement-year">2024</span>
                </div>

                <div class="achievement-card card fade-up">
                    <div class="achievement-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>SSC GPA 4.72</h3>
                    <p>
                        Secured a GPA of 4.72 in the Secondary School Certificate
                        examination from Dhaka Board.
                    </p>
                    <span class="achievement-year">2022</span>
                </div>

                <div class="achievement-card card fade-up">
                    <div class="achievement-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>BNCC Cadet</h3>
                    <p>
                        Active member of the BNCC Green University Platoon,
                        developing leadership, discipline, and teamwork skills.
                    </p>
                    <span class="achievement-year">2024 – Present</span>
                </div>

                <div class="achievement-card card fade-up">
                    <div class="achievement-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>GUCC Cyber Security Society</h3>
                    <p>
                        Member of the Green University Cyber Security Society,
                        participating in workshops, CTFs, and security discussions.
                    </p>
                    <span class="achievement-year">2025 – Present</span>
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
                    <h2 id="cta-title">Ready to Learn More?</h2>
                    <p>
                        Explore my professional experience and the skills I've
                        developed along the way.
                    </p>
                    <div class="cta-actions">
                        <a href="experience.php" class="btn btn-cta-primary">
                            <i class="fas fa-briefcase"></i> View Experience
                        </a>
                        <a href="skills.php" class="btn btn-cta-secondary">
                            <i class="fas fa-code-branch"></i> Explore Skills
                        </a>
                    </div>
                </div>
                <div class="cta-decoration" aria-hidden="true">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            </div>
        </div>
    </section>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>
