// ============================================================
//   BLOGS.JS — Frontend Blog Enhancement
//   Version 3.0 · Lightweight Client-Side Helper
//   The blog data is now managed by the PHP + SQLite admin system.
//   This file provides ONLY frontend enhancements (search, filter).
//
//   DO NOT add blog data, fetch logic, or API calls here.
//   The database is the single source of truth.
// ============================================================

(function() {
    'use strict';

    // ============================================================
    //  1.  CONFIGURATION
    // ============================================================

    const CONFIG = {
        // Selectors for DOM elements
        selectors: {
            grid: '#blogGrid',
            noResults: '#noResults',
            searchInput: '#blogSearch',
            filterBtns: '.filter-btn',
            countEl: '#blogCount',
            postCountStat: '#postCount .hero-stat-number',
            blogCard: '.blog-card',
            blogCardOverlay: '.blog-card-overlay',
        },
        // Debounce delay for search input
        searchDelay: 300,
    };

    // ============================================================
    //  2.  DOM REFS
    // ============================================================

    let grid = null;
    let noResults = null;
    let searchInput = null;
    let filterBtns = [];
    let countEl = null;
    let postCountStat = null;
    let currentFilter = 'all';
    let currentSearch = '';

    // ============================================================
    //  3.  CACHE DOM ELEMENTS
    // ============================================================

    function cacheDom() {
        const s = CONFIG.selectors;
        grid = document.querySelector(s.grid);
        noResults = document.querySelector(s.noResults);
        searchInput = document.querySelector(s.searchInput);
        filterBtns = document.querySelectorAll(s.filterBtns);
        countEl = document.querySelector(s.countEl);
        postCountStat = document.querySelector(s.postCountStat);
    }

    // ============================================================
    //  4.  UTILITY FUNCTIONS
    // ============================================================

    /**
     * Debounce a function call.
     */
    function debounce(fn, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    /**
     * Get the current filter value from active button.
     */
    function getActiveFilter() {
        let active = 'all';
        filterBtns.forEach(btn => {
            if (btn.classList.contains('active')) {
                active = btn.dataset.filter || 'all';
            }
        });
        return active;
    }

    /**
     * Check if a card matches the current search and filter.
     */
    function cardMatches(card, filter, search) {
        // Filter check
        if (filter !== 'all') {
            const tags = card.dataset.tags || '';
            if (!tags.toLowerCase().includes(filter)) {
                return false;
            }
        }

        // Search check
        if (search.trim()) {
            const query = search.trim().toLowerCase();
            const title = card.querySelector('.blog-card-title')?.textContent?.toLowerCase() || '';
            const excerpt = card.querySelector('.blog-card-excerpt-overlay')?.textContent?.toLowerCase() || '';
            const tags = card.dataset.tags || '';
            if (!title.includes(query) && !excerpt.includes(query) && !tags.toLowerCase().includes(query)) {
                return false;
            }
        }

        return true;
    }

    // ============================================================
    //  5.  FILTER & SEARCH
    // ============================================================

    /**
     * Apply current filter and search to the blog grid.
     */
    function applyFilters() {
        if (!grid) return;

        const cards = grid.querySelectorAll(CONFIG.selectors.blogCard);
        let visibleCount = 0;

        cards.forEach(card => {
            const matches = cardMatches(card, currentFilter, currentSearch);
            card.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        // Update count
        if (countEl) {
            countEl.textContent = visibleCount + (visibleCount === 1 ? ' post' : ' posts');
        }
        if (postCountStat) {
            postCountStat.textContent = visibleCount;
        }

        // Show/hide no results
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Update active filter button state
        filterBtns.forEach(btn => {
            const isActive = btn.dataset.filter === currentFilter;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    /**
     * Set the current filter and re-apply.
     */
    function setFilter(filter) {
        currentFilter = filter;
        applyFilters();
    }

    /**
     * Set the current search query and re-apply.
     */
    function setSearch(query) {
        currentSearch = query;
        applyFilters();
    }

    // ============================================================
    //  6.  MOBILE INTERACTION (Tap to expand overlay)
    // ============================================================

    /**
     * Setup mobile overlay interactions for blog cards.
     * On mobile: first tap expands overlay, second tap navigates.
     */
    function setupMobileInteractions() {
        if (!grid) return;

        const cards = grid.querySelectorAll(CONFIG.selectors.blogCard);
        let isMobile = window.innerWidth <= 768;

        // Update on resize
        window.addEventListener('resize', debounce(function() {
            const wasMobile = isMobile;
            isMobile = window.innerWidth <= 768;
            // Reset expanded states when switching to desktop
            if (!isMobile && wasMobile) {
                cards.forEach(card => {
                    const overlay = card.querySelector(CONFIG.selectors.blogCardOverlay);
                    if (overlay) {
                        overlay.classList.remove('mobile-expanded');
                        card._expanded = false;
                    }
                });
            }
        }, 250));

        cards.forEach(card => {
            const overlay = card.querySelector(CONFIG.selectors.blogCardOverlay);
            if (!overlay) return;

            let expanded = false;
            card._expanded = false;

            // Click handler for mobile
            card.addEventListener('click', function(e) {
                const slug = this.dataset.slug;

                if (isMobile) {
                    e.preventDefault();

                    // If not expanded yet, expand and prevent navigation
                    if (!expanded) {
                        overlay.classList.add('mobile-expanded');
                        expanded = true;
                        card._expanded = true;
                    } else {
                        // Already expanded – navigate to detail
                        if (slug) {
                            window.location.href = 'blog-detail.php?slug=' + encodeURIComponent(slug);
                        }
                    }
                } else {
                    // Desktop: click navigates directly
                    if (slug) {
                        window.location.href = 'blog-detail.php?slug=' + encodeURIComponent(slug);
                    }
                }
            });

            // Keyboard support: Enter key navigates
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const slug = this.dataset.slug;
                    if (slug) {
                        window.location.href = 'blog-detail.php?slug=' + encodeURIComponent(slug);
                    }
                }
            });

            // Make card focusable for keyboard users
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
        });
    }

    // ============================================================
    //  7.  INIT
    // ============================================================

    function init() {
        // Cache DOM elements
        cacheDom();

        // If grid doesn't exist, this page doesn't need this script
        if (!grid) {
            console.log('📭 Blog grid not found — skipping blogs.js');
            return;
        }

        // Get initial filter from active button
        currentFilter = getActiveFilter();

        // Setup search with debounce
        if (searchInput) {
            const debouncedSearch = debounce(function(e) {
                setSearch(e.target.value);
            }, CONFIG.searchDelay);
            searchInput.addEventListener('input', debouncedSearch);
        }

        // Setup filter buttons
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                setFilter(this.dataset.filter);
            });
        });

        // Setup mobile interactions
        setupMobileInteractions();

        // Apply initial filters (posts are already rendered by PHP)
        applyFilters();

        console.log('✅ blogs.js loaded — frontend enhancements active');
        console.log('   ↳ Search + filter enabled');
        console.log('   ↳ Mobile tap-to-expand enabled');
    }

    // ============================================================
    //  8.  BOOTSTRAP
    // ============================================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
