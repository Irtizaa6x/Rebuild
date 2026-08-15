// ============================================================
//   IRTIJA — MAIN APPLICATION
//   Version 3.0 · Cleaned & Optimised
//   Vanilla JavaScript · Modular Architecture
//   Features: Smart Navbar · Dark Mode · Scroll Reveal ·
//   Count‑Up · Local Time · Discord Copy · Toggle Details
// ============================================================

(function () {
    'use strict';

    // ============================================================
    //   1.  CONSTANTS & CONFIGURATION
    // ============================================================

    const CONFIG = {
        // Dhaka, Bangladesh coordinates for SunCalc
        DHAKA_LAT: 23.8103,
        DHAKA_LON: 90.4125,

        // Animation thresholds
        REVEAL_THRESHOLD: 0.12,
        REVEAL_THRESHOLD_MOBILE: 0.08,

        // Count-up duration (ms)
        COUNT_DURATION: 2000,

        // Scroll header threshold
        SCROLL_HEADER_OFFSET: 60,

        // Navbar hide/show scroll threshold
        NAVBAR_HIDE_THRESHOLD: 80,

        // Debounce delay (ms)
        DEBOUNCE_DELAY: 100,

        // Throttle delay (ms)
        THROTTLE_DELAY: 16,

        // Dark mode storage key
        DARK_MODE_KEY: 'irtija-theme',

        // CSS class names
        CLASSES: {
            ACTIVE: 'active',
            OPEN: 'open',
            VISIBLE: 'visible',
            SCROLLED: 'scrolled',
            HIDDEN: 'hidden',
            FADE_UP: 'fade-up',
            STAGGER: 'stagger-children',
            NAV_MOBILE: 'nav-mobile',
            NAV_MOBILE_OVERLAY: 'nav-mobile-overlay',
            HAMBURGER: 'hamburger-toggle',
            THEME_TOGGLE: 'theme-toggle',
            HERO: 'hero',
            STAT_NUMBER: 'stat-number',
            DETAILS_CONTENT: 'details-content',
            TOGGLE_BTN: 'btn-toggle-details',
        },
    };

    // ============================================================
    //   2.  DOM CACHE (lazy initialisation)
    // ============================================================

    const DOM = {};

    function cacheDom() {
        DOM.header = document.querySelector('.site-header');
        DOM.hamburger = document.getElementById('hamburgerToggle');
        DOM.navMobile = document.getElementById('mobileNav');
        DOM.navOverlay = document.querySelector('.nav-mobile-overlay');
        DOM.hero = document.getElementById('hero');
        DOM.stats = document.querySelectorAll('.stat-number');
        DOM.revealElements = document.querySelectorAll('.fade-up');
        DOM.staggerElements = document.querySelectorAll('.stagger-children');
        DOM.smoothLinks = document.querySelectorAll('a[href^="#"]');
        DOM.localTimeWrapper = document.getElementById('localTimeWrapper');
        DOM.astroDisplay = document.getElementById('astroDisplay');
        DOM.timeDigital = document.querySelector('.dhaka-time');
        DOM.navLinks = document.querySelectorAll('.nav-link, .nav-mobile-link');
        DOM.body = document.body;
        DOM.document = document.documentElement;
        DOM.themeToggle = document.querySelector('.theme-toggle');
        DOM.toggleButtons = document.querySelectorAll('.btn-toggle-details');
    }

    // ============================================================
    //   3.  UTILITY FUNCTIONS
    // ============================================================

    /**
     * Debounce a function call
     */
    function debounce(fn, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    /**
     * Throttle a function call
     */
    function throttle(fn, limit) {
        let inThrottle = false;
        return function (...args) {
            if (!inThrottle) {
                fn.apply(this, args);
                inThrottle = true;
                setTimeout(() => (inThrottle = false), limit);
            }
        };
    }

    /**
     * Check if element is in viewport
     */
    function isInViewport(el, threshold) {
        if (!el) return false;
        const rect = el.getBoundingClientRect();
        const vh = window.innerHeight;
        const offset = threshold || CONFIG.REVEAL_THRESHOLD;
        return rect.top < vh * (1 - offset) && rect.bottom > vh * offset;
    }

    /**
     * Get the current page filename (without .php or .html)
     */
    function getCurrentPage() {
        const path = window.location.pathname;
        // Remove leading slash, then get filename without extension
        const filename = path.split('/').pop() || 'index';
        return filename.replace(/\.[^.]+$/, '');
    }

    /**
     * Check if running on mobile
     */
    function isMobile() {
        return window.innerWidth <= 768;
    }

    /**
     * Get the preferred color scheme from system
     */
    function getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    // ============================================================
    //   4.  DARK MODE MODULE
    // ============================================================

    const DarkMode = {
        currentTheme: 'light',

        init() {
            this.loadTheme();
            this.setupToggle();
            this.watchSystemPreference();
        },

        loadTheme() {
            const saved = localStorage.getItem(CONFIG.DARK_MODE_KEY);
            if (saved === 'dark') {
                this.setTheme('dark');
                return;
            }
            if (saved === 'light') {
                this.setTheme('light');
                return;
            }
            const system = getSystemTheme();
            this.setTheme(system);
        },

        setTheme(theme) {
            this.currentTheme = theme;
            if (theme === 'dark') {
                DOM.document.setAttribute('data-theme', 'dark');
                DOM.body.setAttribute('data-theme', 'dark');
            } else {
                DOM.document.removeAttribute('data-theme');
                DOM.body.removeAttribute('data-theme');
            }
            this.updateToggleIcon();
            localStorage.setItem(CONFIG.DARK_MODE_KEY, theme);
        },

        toggle() {
            const newTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
            this.setTheme(newTheme);
        },

        setupToggle() {
            if (!DOM.themeToggle) return;
            DOM.themeToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggle();
            });
            DOM.themeToggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggle();
                }
            });
        },

        watchSystemPreference() {
            if (!window.matchMedia) return;
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            media.addEventListener('change', (e) => {
                if (!localStorage.getItem(CONFIG.DARK_MODE_KEY)) {
                    this.setTheme(e.matches ? 'dark' : 'light');
                }
            });
        },

        updateToggleIcon() {
            if (!DOM.themeToggle) return;
            const icon = DOM.themeToggle.querySelector('i');
            if (!icon) return;
            if (this.currentTheme === 'dark') {
                icon.className = 'fas fa-sun';
                DOM.themeToggle.setAttribute('aria-label', 'Switch to light mode');
            } else {
                icon.className = 'fas fa-moon';
                DOM.themeToggle.setAttribute('aria-label', 'Switch to dark mode');
            }
        },
    };

    // ============================================================
    //   5.  NAVIGATION MODULE (enhanced with smart hide/show)
    // ============================================================

    const Navigation = {
        lastScrollY: 0,
        ticking: false,
        isNavHidden: false,

        init() {
            this.setupHamburger();
            this.setupNavOverlay();
            this.setupActiveNav();
            this.setupStickyHeader();
            this.setupSmartNavbar();
        },

        setupHamburger() {
            if (!DOM.hamburger || !DOM.navMobile) return;
            DOM.hamburger.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleMobileNav();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && DOM.navMobile.classList.contains(CONFIG.CLASSES.OPEN)) {
                    this.closeMobileNav();
                }
            });
        },

        setupNavOverlay() {
            if (!DOM.navOverlay) {
                const overlay = document.createElement('div');
                overlay.className = 'nav-mobile-overlay';
                overlay.setAttribute('aria-hidden', 'true');
                DOM.body.appendChild(overlay);
                DOM.navOverlay = overlay;
            }
            DOM.navOverlay.addEventListener('click', () => {
                this.closeMobileNav();
            });
            // Close when a mobile link is clicked
            const mobileLinks = DOM.navMobile ? DOM.navMobile.querySelectorAll('.nav-mobile-link') : [];
            mobileLinks.forEach((link) => {
                link.addEventListener('click', () => {
                    this.closeMobileNav();
                });
            });
        },

        toggleMobileNav() {
            const isOpen = DOM.navMobile.classList.contains(CONFIG.CLASSES.OPEN);
            if (isOpen) {
                this.closeMobileNav();
            } else {
                this.openMobileNav();
            }
        },

        openMobileNav() {
            DOM.navMobile.classList.add(CONFIG.CLASSES.OPEN);
            if (DOM.hamburger) DOM.hamburger.classList.add(CONFIG.CLASSES.OPEN);
            if (DOM.navOverlay) DOM.navOverlay.classList.add(CONFIG.CLASSES.OPEN);
            DOM.body.style.overflow = 'hidden';
            DOM.hamburger?.setAttribute('aria-expanded', 'true');
        },

        closeMobileNav() {
            DOM.navMobile.classList.remove(CONFIG.CLASSES.OPEN);
            if (DOM.hamburger) DOM.hamburger.classList.remove(CONFIG.CLASSES.OPEN);
            if (DOM.navOverlay) DOM.navOverlay.classList.remove(CONFIG.CLASSES.OPEN);
            DOM.body.style.overflow = '';
            DOM.hamburger?.setAttribute('aria-expanded', 'false');
        },

        setupActiveNav() {
            const currentPage = getCurrentPage();
            DOM.navLinks.forEach((link) => {
                const href = link.getAttribute('href');
                if (!href) return;
                // Match filename without extension
                const linkPage = href.replace(/\.[^.]+$/, '');
                if (linkPage === currentPage) {
                    link.classList.add(CONFIG.CLASSES.ACTIVE);
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.classList.remove(CONFIG.CLASSES.ACTIVE);
                    link.removeAttribute('aria-current');
                }
            });
        },

        setupStickyHeader() {
            if (!DOM.header) return;
            const handleScroll = throttle(() => {
                const scrollY = window.scrollY;
                if (scrollY > CONFIG.SCROLL_HEADER_OFFSET) {
                    DOM.header.classList.add(CONFIG.CLASSES.SCROLLED);
                } else {
                    DOM.header.classList.remove(CONFIG.CLASSES.SCROLLED);
                }
            }, CONFIG.THROTTLE_DELAY);
            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // initial check
        },

        setupSmartNavbar() {
            if (!DOM.header) return;
            const handleScroll = () => {
                if (!this.ticking) {
                    window.requestAnimationFrame(() => {
                        const currentScrollY = window.scrollY;
                        const scrollDelta = currentScrollY - this.lastScrollY;
                        if (currentScrollY > CONFIG.NAVBAR_HIDE_THRESHOLD) {
                            if (scrollDelta > 8 && !this.isNavHidden) {
                                DOM.header.classList.add(CONFIG.CLASSES.HIDDEN);
                                this.isNavHidden = true;
                            } else if (scrollDelta < -8 && this.isNavHidden) {
                                DOM.header.classList.remove(CONFIG.CLASSES.HIDDEN);
                                this.isNavHidden = false;
                            }
                        } else {
                            if (this.isNavHidden) {
                                DOM.header.classList.remove(CONFIG.CLASSES.HIDDEN);
                                this.isNavHidden = false;
                            }
                        }
                        this.lastScrollY = currentScrollY;
                        this.ticking = false;
                    });
                    this.ticking = true;
                }
            };
            const throttledScroll = throttle(handleScroll, 50);
            window.addEventListener('scroll', throttledScroll, { passive: true });
            this.lastScrollY = window.scrollY;
        },

        close() {
            this.closeMobileNav();
        },
    };

    // ============================================================
    //   6.  SCROLL REVEAL MODULE
    // ============================================================

    const ScrollReveal = {
        observer: null,
        observedElements: new Set(),

        init() {
            if (!('IntersectionObserver' in window)) {
                this.fallbackReveal();
                return;
            }
            const threshold = isMobile() ? CONFIG.REVEAL_THRESHOLD_MOBILE : CONFIG.REVEAL_THRESHOLD;
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add(CONFIG.CLASSES.VISIBLE);
                            this.observer.unobserve(entry.target);
                            this.observedElements.delete(entry.target);
                        }
                    });
                },
                {
                    threshold: threshold,
                    rootMargin: '0px 0px -40px 0px',
                }
            );
            this.observeAll();
        },

        observeAll() {
            DOM.revealElements.forEach((el) => {
                if (!this.observedElements.has(el)) {
                    this.observer.observe(el);
                    this.observedElements.add(el);
                }
            });
            DOM.staggerElements.forEach((el) => {
                if (!this.observedElements.has(el)) {
                    this.observer.observe(el);
                    this.observedElements.add(el);
                }
            });
            // Also observe any elements with data-reveal attribute
            document.querySelectorAll('[data-reveal]').forEach((el) => {
                if (!this.observedElements.has(el)) {
                    this.observer.observe(el);
                    this.observedElements.add(el);
                }
            });
        },

        fallbackReveal() {
            const revealAll = () => {
                DOM.revealElements.forEach((el) => el.classList.add(CONFIG.CLASSES.VISIBLE));
                DOM.staggerElements.forEach((el) => el.classList.add(CONFIG.CLASSES.VISIBLE));
                document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add(CONFIG.CLASSES.VISIBLE));
            };
            revealAll();
        },

        observe(el) {
            if (this.observer && !this.observedElements.has(el)) {
                this.observer.observe(el);
                this.observedElements.add(el);
            }
        },

        refresh() {
            this.observeAll();
        },
    };

    // ============================================================
    //   7.  COUNT-UP ANIMATION MODULE
    // ============================================================

    const CountUp = {
        observer: null,
        animated: new Set(),

        init() {
            if (!('IntersectionObserver' in window)) {
                this.animateAll();
                return;
            }
            this.observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting && !this.animated.has(entry.target)) {
                            this.animate(entry.target);
                            this.animated.add(entry.target);
                            this.observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.5 }
            );
            DOM.stats.forEach((el) => {
                this.observer.observe(el);
            });
            // Also observe any elements with data-count attribute (not already stat-number)
            document.querySelectorAll('[data-count]').forEach((el) => {
                if (!el.classList.contains('stat-number')) {
                    this.observer.observe(el);
                }
            });
        },

        animate(el) {
            const target = parseFloat(el.getAttribute('data-count'));
            if (isNaN(target)) return;
            const isFloat = target % 1 !== 0;
            const duration = CONFIG.COUNT_DURATION;
            const start = performance.now();
            const startValue = parseFloat(el.textContent) || 0;
            const from = target < 10 ? startValue : 0;
            const animateStep = (timestamp) => {
                const elapsed = timestamp - start;
                const progress = Math.min(elapsed / duration, 1);
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = from + (target - from) * easeOut;
                el.textContent = isFloat ? current.toFixed(2) : Math.round(current);
                if (progress < 1) {
                    requestAnimationFrame(animateStep);
                } else {
                    el.textContent = isFloat ? target.toFixed(2) : Math.round(target);
                }
            };
            requestAnimationFrame(animateStep);
        },

        animateAll() {
            DOM.stats.forEach((el) => {
                if (!this.animated.has(el)) {
                    this.animate(el);
                    this.animated.add(el);
                }
            });
        },

        animateElement(el) {
            if (!this.animated.has(el)) {
                this.animate(el);
                this.animated.add(el);
            }
        },
    };

    // ============================================================
    //   8.  SMOOTH SCROLL MODULE
    // ============================================================

    const SmoothScroll = {
        init() {
            DOM.smoothLinks.forEach((link) => {
                link.addEventListener('click', (e) => {
                    const targetId = link.getAttribute('href');
                    if (targetId === '#') return;
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        e.preventDefault();
                        this.scrollTo(targetElement);
                    }
                });
            });
        },

        scrollTo(target) {
            const headerHeight = DOM.header ? DOM.header.offsetHeight : 72;
            const targetPosition = target.getBoundingClientRect().top + window.scrollY - headerHeight - 20;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth',
            });
        },

        scrollToSelector(selector) {
            const target = document.querySelector(selector);
            if (target) {
                this.scrollTo(target);
            }
        },
    };

    // ============================================================
    //   9.  LOCAL TIME & SUN CALC MODULE
    // ============================================================

    const ClockModule = {
        timer: null,

        init() {
            if (!DOM.localTimeWrapper && !DOM.timeDigital) return;
            if (typeof SunCalc === 'undefined') {
                // Fallback: just update time without SunCalc
                this.updateClockOnly();
                this.timer = setInterval(() => this.updateClockOnly(), 1000);
                return;
            }
            this.update();
            this.timer = setInterval(() => this.update(), 1000);
        },

        update() {
            const now = new Date();
            this.updateTimeDisplay(now);
            this.updateTimeOfDay(now);
        },

        updateClockOnly() {
            const now = new Date();
            this.updateTimeDisplay(now);
        },

        updateTimeDisplay(now) {
            if (!DOM.timeDigital) return;
            const options = {
                timeZone: 'Asia/Dhaka',
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            };
            try {
                const timeStr = new Intl.DateTimeFormat('en-GB', options).format(now);
                DOM.timeDigital.textContent = timeStr;
            } catch (_) {
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                DOM.timeDigital.textContent = `${hours}:${minutes}:${seconds}`;
            }
        },

        updateTimeOfDay(now) {
            if (!DOM.localTimeWrapper || !DOM.astroDisplay) return;
            if (typeof SunCalc === 'undefined') return;
            const { DHAKA_LAT, DHAKA_LON } = CONFIG;
            try {
                const sunTimes = SunCalc.getTimes(now, DHAKA_LAT, DHAKA_LON);
                const sunrise = sunTimes.sunrise;
                const sunset = sunTimes.sunset;
                const nowTime = now.getTime();
                const dawnStart = new Date(sunrise.getTime() - 30 * 60 * 1000);
                const morningEnd = new Date(sunrise.getTime() + 2 * 60 * 60 * 1000);
                const noonStart = new Date(sunrise.getTime() + 2 * 60 * 60 * 1000);
                const noonEnd = new Date(sunset.getTime() - 2 * 60 * 60 * 1000);
                const afternoonStart = new Date(sunset.getTime() - 2 * 60 * 60 * 1000);
                const duskEnd = new Date(sunset.getTime() + 30 * 60 * 1000);
                const lightNightEnd = new Date(sunset.getTime() + 3 * 60 * 60 * 1000);
                let isDay = false;
                if (nowTime >= dawnStart.getTime() && nowTime < sunrise.getTime()) {
                    isDay = true;
                } else if (nowTime >= sunrise.getTime() && nowTime < morningEnd.getTime()) {
                    isDay = true;
                } else if (nowTime >= noonStart.getTime() && nowTime < noonEnd.getTime()) {
                    isDay = true;
                } else if (nowTime >= afternoonStart.getTime() && nowTime < sunset.getTime()) {
                    isDay = true;
                } else if (nowTime >= sunset.getTime() && nowTime < duskEnd.getTime()) {
                    isDay = true;
                } else {
                    isDay = false;
                }
                this.updateAstroDisplay(now, isDay);
            } catch (_) {
                const hour = now.getHours();
                const isDay = hour >= 6 && hour < 18;
                this.updateAstroDisplayFallback(isDay);
            }
        },

        updateAstroDisplay(now, isDay) {
            if (!DOM.astroDisplay) return;
            if (isDay) {
                DOM.astroDisplay.innerHTML = `<div class="sun"></div>`;
            } else {
                try {
                    const moonIllum = SunCalc.getMoonIllumination(now);
                    const phaseAngle = moonIllum.angle;
                    const fraction = moonIllum.fraction;
                    let sizeClass = 'size-medium';
                    if (fraction < 0.3) sizeClass = 'size-small';
                    else if (fraction > 0.7) sizeClass = 'size-large';
                    const rotationDeg = ((phaseAngle * 180) / Math.PI) % 360;
                    DOM.astroDisplay.innerHTML = `
                        <div class="moon ${sizeClass}" style="--rotation: ${rotationDeg}deg;"></div>
                    `;
                } catch (_) {
                    this.updateAstroDisplayFallback(false);
                }
            }
        },

        updateAstroDisplayFallback(isDay) {
            if (!DOM.astroDisplay) return;
            if (isDay) {
                DOM.astroDisplay.innerHTML = `<div class="sun"></div>`;
            } else {
                DOM.astroDisplay.innerHTML = `<div class="moon size-medium" style="--rotation: 180deg;"></div>`;
            }
        },

        destroy() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
    };

    // ============================================================
    //   10.  DISCORD COPY MODULE
    // ============================================================

    const DiscordCopy = {
        init() {
            const discordBtns = document.querySelectorAll('.discord-copy');
            if (!discordBtns.length) return;
            const username = 'naz.irt.k6';
            discordBtns.forEach((btn) => {
                const originalHTML = btn.innerHTML;
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(username)
                            .then(() => this.showFeedback(btn, originalHTML))
                            .catch(() => this.fallbackCopy(btn, originalHTML, username));
                    } else {
                        this.fallbackCopy(btn, originalHTML, username);
                    }
                });
            });
        },

        showFeedback(btn, originalHTML) {
            btn.innerHTML = '<i class="fas fa-check"></i> Username Copied!';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 1800);
        },

        fallbackCopy(btn, originalHTML, text) {
            const input = document.createElement('input');
            input.value = text;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            try {
                document.execCommand('copy');
                this.showFeedback(btn, originalHTML);
            } catch (_) {
                alert('Copy failed. Please copy manually: ' + text);
            }
            document.body.removeChild(input);
        },
    };

    // ============================================================
    //   11.  EDUCATION TOGGLE DETAILS MODULE
    // ============================================================

    const ToggleDetails = {
        init() {
            if (!DOM.toggleButtons.length) return;
            DOM.toggleButtons.forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggle(btn);
                });
                btn.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.toggle(btn);
                    }
                });
            });
        },

        toggle(btn) {
            const targetId = btn.getAttribute('data-target');
            if (!targetId) return;
            const target = document.getElementById(targetId);
            if (!target) return;
            const isOpen = target.classList.contains(CONFIG.CLASSES.OPEN);
            target.classList.toggle(CONFIG.CLASSES.OPEN);
            btn.classList.toggle(CONFIG.CLASSES.OPEN);
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = isOpen ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
            }
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            if (!isOpen) {
                setTimeout(() => {
                    const headerHeight = DOM.header ? DOM.header.offsetHeight : 72;
                    const top = target.getBoundingClientRect().top + window.scrollY - headerHeight - 20;
                    window.scrollTo({ top, behavior: 'smooth' });
                }, 50);
            }
        },

        open(targetId) {
            const target = document.getElementById(targetId);
            if (!target || target.classList.contains(CONFIG.CLASSES.OPEN)) return;
            const btn = document.querySelector(`.btn-toggle-details[data-target="${targetId}"]`);
            if (btn) this.toggle(btn);
        },

        close(targetId) {
            const target = document.getElementById(targetId);
            if (!target || !target.classList.contains(CONFIG.CLASSES.OPEN)) return;
            const btn = document.querySelector(`.btn-toggle-details[data-target="${targetId}"]`);
            if (btn) this.toggle(btn);
        },
    };

    // ============================================================
    //   12.  PERFORMANCE OPTIMISATIONS
    // ============================================================

    const Performance = {
        init() {
            this.setupPassiveListeners();
            this.setupResizeHandler();
        },

        setupPassiveListeners() {
            // All scroll listeners already use { passive: true }
        },

        setupResizeHandler() {
            const handleResize = debounce(() => {
                // Re-check scroll reveal on resize
                ScrollReveal.refresh();
                // Close mobile nav on resize to desktop
                if (!isMobile() && DOM.navMobile && DOM.navMobile.classList.contains(CONFIG.CLASSES.OPEN)) {
                    Navigation.close();
                }
            }, CONFIG.DEBOUNCE_DELAY);
            window.addEventListener('resize', handleResize, { passive: true });
        },
    };

    // ============================================================
    //   13.  ACCESSIBILITY UTILITIES
    // ============================================================

    const Accessibility = {
        init() {
            this.setupFocusTrap();
            // Skip link is handled by CSS and HTML; we ensure it works
        },

        setupFocusTrap() {
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Tab') return;
                if (!DOM.navMobile || !DOM.navMobile.classList.contains(CONFIG.CLASSES.OPEN)) return;
                const focusable = DOM.navMobile.querySelectorAll(
                    'a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                if (focusable.length === 0) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            });
        },
    };

    // ============================================================
    //   14.  MAIN INITIALISATION
    // ============================================================

    let isInitialized = false;

    function init() {
        if (isInitialized) return;
        isInitialized = true;

        // Cache DOM elements
        cacheDom();

        // Initialise modules
        Navigation.init();
        ScrollReveal.init();
        CountUp.init();
        SmoothScroll.init();
        ClockModule.init();
        DiscordCopy.init();
        DarkMode.init();
        ToggleDetails.init();
        Performance.init();
        Accessibility.init();

        // Watch for dynamic content changes (e.g., blog posts loaded)
        const observer = new MutationObserver(() => {
            // Re-check for new fade-up elements
            document.querySelectorAll('.fade-up:not(.visible)').forEach((el) => {
                ScrollReveal.observe(el);
            });
            // Re-check for new stagger elements
            document.querySelectorAll('.stagger-children:not(.visible)').forEach((el) => {
                ScrollReveal.observe(el);
            });
            // Re-check for new stat numbers
            document.querySelectorAll('.stat-number:not([data-observed])').forEach((el) => {
                el.setAttribute('data-observed', 'true');
                CountUp.animateElement(el);
            });
            // Re-check for new toggle buttons
            document.querySelectorAll('.btn-toggle-details:not([data-initialized])').forEach((btn) => {
                btn.setAttribute('data-initialized', 'true');
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    ToggleDetails.toggle(btn);
                });
            });
        });
        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });

        console.log(
            '%c✦ IrtiJa · Executive Portfolio %cv3.0',
            'background:#004643;color:#D4A853;padding:6px 14px;border-radius:4px 0 0 4px;font-weight:700;letter-spacing:0.5px;',
            'background:#D4A853;color:#004643;padding:6px 14px;border-radius:0 4px 4px 0;font-weight:600;'
        );
        console.log('%c🌿 Cleaned · Optimised · Accessible', 'color:#1A7A74;font-weight:500;');
    }

    // ============================================================
    //   15.  BOOTSTRAP
    // ============================================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ============================================================
    //   16.  EXPOSE PUBLIC API (for debugging / external use)
    // ============================================================

    window.IrtiJa = {
        Navigation,
        ScrollReveal,
        CountUp,
        SmoothScroll,
        ClockModule,
        DiscordCopy,
        DarkMode,
        ToggleDetails,
        Performance,
        Accessibility,
        isMobile,
        getCurrentPage,
        debounce,
        throttle,
        isInViewport,

        reinit() {
            cacheDom();
            ScrollReveal.refresh();
            CountUp.init();
            ToggleDetails.init();
            document.querySelectorAll('.stat-number:not([data-observed])').forEach((el) => {
                el.setAttribute('data-observed', 'true');
                CountUp.animateElement(el);
            });
        },

        toggleDarkMode() {
            DarkMode.toggle();
        },

        setDarkMode(theme) {
            DarkMode.setTheme(theme);
        },

        getCurrentTheme() {
            return DarkMode.currentTheme;
        },
    };

})();
