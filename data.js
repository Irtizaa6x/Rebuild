// ============================================================
//   DATA.JS — Core Content Data
//   Version 2.0 · Professional
//   Central data store for Education, Certifications, Contact,
//   and Social Links.
//
//   NOTE: Projects have been moved to the blog system.
//   Experiences and Skills are managed in:
//          - expdata.js
//          - skdata.js
// ============================================================

(function() {
    'use strict';

    // ============================================================
    //   1.  EDUCATION DATA (Degrees & Academic Background)
    // ============================================================

    const education = [
        {
            id: 'bsc',
            degree: 'B.Sc. in Computer Science & Engineering',
            institution: 'Green University of Bangladesh',
            major: 'Computer Science & Engineering (CSE)',
            gpa: '3.14',
            startYear: '2025',
            endYear: null, // ongoing
            board: null,
            note: '4th semester (Trimester) • Ongoing',
            semesters: [
                {
                    name: 'Semester 250',
                    courses: [
                        { code: 'CSE 100', title: 'Computational Thinking and Problem Solving', credits: 1.5, grade: 'A+',
                            gradePoint: 4.0 },
                        { code: 'CSE 101-CSE(181)', title: 'Discrete Mathematics', credits: 3, grade: 'A-', gradePoint: 3.5 },
                        { code: 'ESP 009', title: 'Academic English', credits: 0, grade: 'A', gradePoint: 3.75 },
                    ],
                },
                {
                    name: 'Semester 252',
                    courses: [
                        { code: 'CSE 103-CSE(181)', title: 'Structured Programming', credits: 3, grade: 'C+',
                            gradePoint: 2.5 },
                        { code: 'CHE 101-CSE(181)', title: 'Chemistry', credits: 3, grade: 'B', gradePoint: 3.0 },
                        { code: 'CSE 104-CSE(181)', title: 'Structured Programming Lab', credits: 1.5, grade: 'A-',
                            gradePoint: 3.5 },
                        { code: 'ESP 101', title: 'Academic English I', credits: 3, grade: 'B-', gradePoint: 2.75 },
                        { code: 'MAT 101(V1)', title: 'Calculus for Computing', credits: 3, grade: 'C+', gradePoint: 2.5 },
                        { code: 'CHE 102-CSE(181)', title: 'Chemistry Lab', credits: 1, grade: 'A+', gradePoint: 4.0 },
                    ],
                },
                {
                    name: 'Semester 261',
                    courses: [
                        { code: 'CSE 205 (V1)', title: 'Data Structures', credits: 3, grade: 'A-', gradePoint: 3.5 },
                        { code: 'EEE 101', title: 'Introduction to Electrical Engineering', credits: 3, grade: 'B',
                            gradePoint: 3.0 },
                        { code: 'MAT 103(V1)', title: 'Linear Algebra and Vector Analysis', credits: 3, grade: 'B',
                            gradePoint: 3.0 },
                        { code: 'CSE 206 (V1)', title: 'Data Structures Lab', credits: 1.5, grade: 'B+', gradePoint: 3.25 },
                        { code: 'PHY 101-CSE(181)', title: 'Physics I', credits: 3, grade: 'B+', gradePoint: 3.25 },
                        { code: 'EEE 102 (V1)', title: 'Introduction to Electrical Engineering Lab', credits: 1, grade: 'A+',
                            gradePoint: 4.0 },
                    ],
                },
            ],
        },
        {
            id: 'hsc',
            degree: 'Higher Secondary Certificate (HSC)',
            institution: 'Giasuddin Islamic Model College',
            major: 'Science',
            gpa: '5.00',
            startYear: '2022',
            endYear: '2024',
            board: 'Dhaka',
            note: null,
            subjects: [
                { code: '101', name: 'BANGLA', grade: 'A' },
                { code: '107', name: 'ENGLISH', grade: 'A' },
                { code: '275', name: 'INFORMATION & COMMUNICATION TECHNOLOGY', grade: 'A+' },
                { code: '174', name: 'PHYSICS', grade: 'A+' },
                { code: '176', name: 'CHEMISTRY', grade: 'A+' },
                { code: '178', name: 'BIOLOGY', grade: 'A+' },
                { code: '265', name: 'HIGHER MATHEMATICS', grade: 'A+' },
            ],
        },
        {
            id: 'ssc',
            degree: 'Secondary School Certificate (SSC)',
            institution: 'Rafiqul Islam School & College',
            major: 'Science',
            gpa: '4.72',
            startYear: '2020',
            endYear: '2022',
            board: 'Dhaka',
            note: null,
            subjects: [
                { code: '101', name: 'BANGLA', grade: 'A' },
                { code: '107', name: 'ENGLISH', grade: 'A+' },
                { code: '109', name: 'MATHEMATICS', grade: 'A+' },
                { code: '150', name: 'BANGLADESH AND GLOBAL STUDIES', grade: 'B' },
                { code: '111', name: 'ISLAM AND MORAL EDUCATION', grade: 'A-' },
                { code: '136', name: 'PHYSICS', grade: 'A+' },
                { code: '137', name: 'CHEMISTRY', grade: 'A+' },
                { code: '126', name: 'HIGHER MATHEMATICS', grade: 'A+' },
                { code: '154', name: 'INFORMATION AND COMMUNICATION TECHNOLOGY', grade: 'A' },
                { code: '138', name: 'BIOLOGY', grade: 'A+' },
                { code: '147', name: 'PHYSICAL EDUCATION, HEALTH AND SPORTS', grade: 'A+' },
                { code: '156', name: 'CAREER EDUCATION', grade: 'A+' },
            ],
        },
    ];

    // ============================================================
    //   2.  CERTIFICATIONS DATA
    // ============================================================

    const certifications = [
        {
            id: 'cybersecurity-fundamentals',
            title: 'Cybersecurity Fundamentals',
            issuer: 'Green University Cyber Security Society',
            description: 'Exploring core cybersecurity concepts including threat analysis, vulnerability assessment, and defensive strategies. Active participation in workshops and hands-on exercises.',
            date: '2025',
            link: 'skills.php#cyber',
            ongoing: true,
        },
        {
            id: 'bncc-leadership',
            title: 'BNCC Leadership Training',
            issuer: 'Bangladesh National Cadet Corps',
            description: 'Leadership development through military-style training. Focus on discipline, teamwork, integrity, and performing under pressure.',
            date: '2024',
            link: 'experience.php#timeline-title',
            ongoing: true,
        },
        {
            id: 'hsc-gpa-500',
            title: 'HSC GPA 5.00',
            issuer: 'Dhaka Board, Bangladesh',
            description: 'Achieved a perfect GPA of 5.00 in the Higher Secondary Certificate examination. Recognized for outstanding academic performance.',
            date: '2024',
            link: 'education.php#hsc-details',
            ongoing: false,
        },
        {
            id: 'ssc-gpa-472',
            title: 'SSC GPA 4.72',
            issuer: 'Dhaka Board, Bangladesh',
            description: 'Secured a GPA of 4.72 in the Secondary School Certificate examination. Demonstrated excellence in Science and Mathematics.',
            date: '2022',
            link: 'education.php#ssc-details',
            ongoing: false,
        },
    ];

    // ============================================================
    //   3.  CONTACT INFORMATION
    // ============================================================

    const contact = {
        email: 'irtija.x.k6@hotmail.com',
        phonePrimary: '+8801518940566',
        phoneSecondary: '+8801886940566',
        github: 'https://github.com/Irtizaa6x',
        linkedin: 'https://linkedin.com/in/irtija-talha',
        discordUsername: 'naz.irt.k6',
        whatsapp: 'https://wa.me/qr/H3R2HPTW66G3P1',
    };

    // ============================================================
    //   4.  SOCIAL LINKS
    // ============================================================

    const socialLinks = [
        { platform: 'Facebook', url: 'https://www.facebook.com/irtija.webhop.me', icon: 'fab fa-facebook-f' },
        { platform: 'Instagram', url: 'https://www.instagram.com/7d6_nev', icon: 'fab fa-instagram' },
        { platform: 'X (Twitter)', url: 'https://www.x.com/irtijaXtalha', icon: 'fab fa-x-twitter' },
        { platform: 'Threads', url: 'https://www.threads.com/7d6_nev', icon: 'fab fa-threads' },
        { platform: 'GitHub', url: 'https://github.com/Irtizaa6x', icon: 'fab fa-github' },
        { platform: 'LinkedIn', url: 'https://linkedin.com/in/irtija-talha', icon: 'fab fa-linkedin-in' },
        { platform: 'WhatsApp', url: 'https://wa.me/qr/H3R2HPTW66G3P1', icon: 'fab fa-whatsapp' },
        { platform: 'Discord', url: '#', icon: 'fab fa-discord' }, // Handled by copy script
    ];

    // ============================================================
    //   5.  EXPOSE GLOBALLY
    // ============================================================

    if (typeof window !== 'undefined') {
        window.education = education;
        window.certifications = certifications;
        window.contact = contact;
        window.socialLinks = socialLinks;
    }

    // Optional: console log for debugging
    console.log(
        '%c📦 data.js loaded (Core)',
        'color:#1A7A74;font-weight:600;'
    );
    console.log(`   ↳ Education: ${education.length} entries`);
    console.log(`   ↳ Certifications: ${certifications.length} entries`);

})();
