// ============================================================
//   SKDATA.JS — Skills Data
//   Version 2.0 · Professional
//   Exposes window.skills for dynamic rendering.
//   Based on the actual skills of Md Irtija Azad Talha.
// ============================================================

(function() {
    'use strict';

    /**
     * SKILLS — Object containing all skill categories.
     * Each category includes:
     *   - title       : Display name of the category.
     *   - icon        : Font Awesome icon class.
     *   - description : Brief description of the category.
     *   - items       : Array of { name, level, progress }.
     *        name     : Skill name.
     *        level    : 'Beginner', 'Intermediate', or 'Advanced'.
     *        progress : Percentage (0-100) for the skill bar.
     *   - tags        : Array of related keywords/tools (displayed as tags).
     */
    const skills = {

        // ----- 1. Education & Academic Coursework -----
        academic: {
            title: 'Education & Academic Coursework',
            icon: 'fa-graduation-cap',
            description:
                'Core computer science and engineering courses completed during my B.Sc. program at Green University of Bangladesh.',
            items: [
                { name: 'Computational Thinking & Problem Solving', level: 'Advanced', progress: 85 },
                { name: 'Discrete Mathematics', level: 'Intermediate', progress: 70 },
                { name: 'Structured Programming', level: 'Intermediate', progress: 75 },
                { name: 'Data Structures', level: 'Intermediate', progress: 70 },
                { name: 'Linear Algebra & Vector Analysis', level: 'Intermediate', progress: 65 },
                { name: 'Calculus for Computing', level: 'Intermediate', progress: 65 },
                { name: 'Introduction to Electrical Engineering', level: 'Beginner', progress: 55 },
                { name: 'Physics I', level: 'Intermediate', progress: 60 },
                { name: 'Chemistry (Theory + Lab)', level: 'Beginner', progress: 50 },
                { name: 'Academic English I & II', level: 'Advanced', progress: 85 },
            ],
            tags: [
                'CSE',
                'Engineering',
                'Mathematics',
                'Problem Solving',
                'Physics',
                'Chemistry',
                'Data Structures',
                'Discrete Mathematics',
                'Linear Algebra',
                'Calculus',
                'Computational Thinking',
                'Structured Programming',
            ],
        },

        // ----- 2. Cybersecurity Learning (TryHackMe + Cybrary) -----
        cyber: {
            title: 'Cybersecurity & Ethical Hacking',
            icon: 'fa-user-secret',
            description:
                'Hands-on cybersecurity training from TryHackMe and Cybrary — covering offensive, defensive, and web security fundamentals.',
            items: [
                { name: 'Penetration Testing Fundamentals', level: 'Intermediate', progress: 70 },
                { name: 'Offensive Security Fundamentals', level: 'Intermediate', progress: 65 },
                { name: 'Defensive Security Fundamentals', level: 'Intermediate', progress: 60 },
                { name: 'Web Security & HTTP/HTTPS', level: 'Intermediate', progress: 75 },
                { name: 'Networking Fundamentals (DNS, TCP/IP)', level: 'Intermediate', progress: 70 },
                { name: 'How Websites Work', level: 'Advanced', progress: 85 },
                { name: 'Careers in Cybersecurity', level: 'Advanced', progress: 90 },
                { name: 'Ethical Hacking Fundamentals', level: 'Intermediate', progress: 65 },
            ],
            tags: [
                'TryHackMe: How Websites Work',
                'TryHackMe: Putting It All Together',
                'TryHackMe: DNS in Detail',
                'TryHackMe: HTTP in Detail',
                'TryHackMe: What is Networking?',
                'TryHackMe: Pentesting Fundamentals',
                'TryHackMe: Defensive Security Intro',
                'TryHackMe: Careers in Cyber',
                'TryHackMe: Offensive Security Intro',
                'Cybrary: Orientation',
                'Cybrary: Careers in Cybersecurity',
                'Web Security Basics',
                'Network Security',
                'Threat Analysis',
                'Vulnerability Assessment',
                'Basic Penetration Testing',
            ],
        },

        // ----- 3. Web Development (Codecademy) -----
        web: {
            title: 'Web Development & Programming',
            icon: 'fa-code',
            description:
                'Completed learning paths from Codecademy — HTML, CSS, and JavaScript fundamentals with hands-on projects.',
            items: [
                { name: 'HTML5 & Semantic HTML', level: 'Advanced', progress: 90 },
                { name: 'CSS3 (Box Model, Flexbox, Grid)', level: 'Advanced', progress: 85 },
                { name: 'JavaScript (Beginner Level)', level: 'Intermediate', progress: 65 },
                { name: 'HTML Forms & Validation', level: 'Intermediate', progress: 75 },
                { name: 'Responsive Design', level: 'Intermediate', progress: 70 },
                { name: 'Front-End Development Basics', level: 'Intermediate', progress: 75 },
            ],
            tags: [
                'Codecademy: Learn HTML',
                'Codecademy: Semantic HTML',
                'Codecademy: HTML Forms',
                'Codecademy: Form Validation',
                'Codecademy: HTML Tables',
                'Codecademy: HTML Document Standards',
                'Codecademy: Introduction to HTML',
                'Codecademy: Elements and Structure',
                'Codecademy: Overview of the Internet',
                'Codecademy: Languages for Web Development',
                'Codecademy: Overview of Web Development',
                'Codecademy: Introduction to Front-End Development',
                'Codecademy: Learn CSS',
                'Codecademy: CSS Typography',
                'Codecademy: CSS Colors',
                'Codecademy: CSS Display and Positioning',
                'Codecademy: CSS Box Model',
                'Codecademy: Changing the Box Model',
                'Codecademy: CSS Visual Rules',
                'Codecademy: CSS Syntax and Selectors',
                'Codecademy: CSS Setup and Syntax',
                'Codecademy: Introduction to JavaScript',
                'Codecademy: Variables in JavaScript',
                'Codecademy: Conditional Statements',
                'Codecademy: JavaScript Conditionals',
                'Codecademy: Welcome to Learn JavaScript',
                'Projects: Dog Years',
                'Projects: Kelvin Weather',
                'HTML5',
                'CSS3',
                'JavaScript',
                'Problem Solving & Computational Thinking',
                'Responsive Design',
                'Web Fundamentals',
            ],
        },

        // ----- 4. Professional & Soft Skills -----
        professional: {
            title: 'Professional & Soft Skills',
            icon: 'fa-briefcase',
            description:
                'Core competencies that enable effective learning, collaboration, and problem-solving in any environment.',
            items: [
                { name: 'Analytical Thinking', level: 'Advanced', progress: 85 },
                { name: 'Problem Solving', level: 'Advanced', progress: 80 },
                { name: 'Self-Learning & Adaptability', level: 'Advanced', progress: 90 },
                { name: 'Technical Research', level: 'Advanced', progress: 75 },
                { name: 'Continuous Learning Mindset', level: 'Advanced', progress: 95 },
                { name: 'Collaboration & Teamwork', level: 'Advanced', progress: 80 },
            ],
            tags: [
                'Analytical Thinking',
                'Problem Solving',
                'Self-Learning',
                'Adaptability',
                'Technical Research',
                'Continuous Learning',
                'Collaboration',
                'Communication',
                'Discipline',
                'Leadership',
            ],
        },

    };

    // ============================================================
    //   EXPOSE GLOBALLY
    // ============================================================

    if (typeof window !== 'undefined') {
        window.skills = skills;
    }

    // Optional: console log for debugging
    console.log('✅ skdata.js loaded — %d skill categories.', Object.keys(skills).length);

})();
