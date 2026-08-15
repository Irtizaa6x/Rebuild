// ============================================================
//   EXPDATA.JS — Experience Data
//   Version 2.0 · Professional
//   Exposes window.experiences for dynamic rendering.
//   Edit this file to update your experience timeline.
// ============================================================

(function() {
    'use strict';

    /**
     * EXPERIENCES — Array of professional & extracurricular activities.
     * Each entry includes:
     *   - id          : Unique identifier (used for anchors).
     *   - title       : Display title (with organisation/role).
     *   - startDate   : ISO date string (YYYY-MM-DD).
     *   - endDate     : ISO date string or null (ongoing).
     *   - icon        : Font Awesome icon class.
     *   - role        : Your role / position.
     *   - description : Detailed description (supports HTML).
     *   - parentClub  : Parent organisation (if applicable).
     *   - certButtons : Array of { label, url, icon } for action buttons.
     */
    const experiences = [
        {
            id: 'gucc',
            title: 'Member · GUCC Cyber Security Society',
            startDate: '2025-01-01',
            endDate: null, // ongoing
            icon: 'fa-user-secret',
            role: 'Active Member',
            description:
                'Actively participating in cybersecurity workshops, CTF competitions, and network security discussions. ' +
                'Learning the fundamentals of threat analysis, vulnerability assessment, and defensive strategies. ' +
                'Collaborating with peers on security research and practical challenges.',
            parentClub: 'Green University Cyber Security Society',
            certButtons: [
                {
                    label: 'View Society',
                    url: 'https://www.facebook.com/gucccss.gub',
                    icon: 'fas fa-external-link-alt',
                },
            ],
        },
        {
            id: 'bncc',
            title: 'Cadet · BNCC Green University Platoon',
            startDate: '2024-01-01',
            endDate: null,
            icon: 'fa-shield-alt',
            role: 'Cadet',
            description:
                'Developed leadership, discipline, and teamwork through rigorous military-style training. ' +
                'Performed under pressure and learned the value of responsibility, integrity, and commitment. ' +
                'Participated in drills, parades, and leadership exercises.',
            parentClub: 'Bangladesh National Cadet Corps (BNCC)',
            certButtons: [],
        },
        {
            id: 'webdev',
            title: 'Cybersecurity & Technical Development',
            startDate: '2025-01-01',
            endDate: null,
            icon: 'fa-code',
            role: 'Developer',
            description:
                'Developing practical cybersecurity skills through hands-on learning, ' +
                'security labs, and technical projects. Building a strong foundation in ' +
                'Linux, networking, web technologies, cybersecurity fundamentals, and ' +
                'offensive and defensive security practices.',
            parentClub: 'Self-Initiated',
            certButtons: [
                {
                    label: 'View Codecademy Profile',
                    url: 'https://www.codecademy.com/users/Irtija.Talha/achievements',
                    icon: 'fab fa-codecademy',
                },
                {
                    label: 'View Cybrary Certificate',
                    url: 'https://app.cybrary.it/profile/irtija_talha',
                    icon: 'fas fa-certificate',
                },
                {
                    label: 'View TryHackMe Profile',
                    url: 'https://tryhackme.com/p/irtija.talha',
                    icon: 'fas fa-shield-alt',
                },
                {
                    label: 'View Cisco Certificate',
                    url: 'https://drive.google.com/file/d/1N_PM211klCLd7hNWSZ0vsXEJIdQJB2EB/view?usp=sharing',
                    icon: 'fas fa-certificate',
                },
            ],
        },
    ];

    // ============================================================
    //   EXPOSE GLOBALLY
    // ============================================================

    if (typeof window !== 'undefined') {
        window.experiences = experiences;
    }

    // Optional: console log for debugging
    console.log('✅ expdata.js loaded — %d experience entries.', experiences.length);

})();
