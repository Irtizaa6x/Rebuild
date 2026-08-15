<?php
/**
 * blog.php
 *
 * Public blog listing page for IrtiJa portfolio.
 * Displays posts in a 3-column grid with category badges and hover overlays.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Page-specific variables for the header ---
$page_title       = 'Blog · IrtiJa';
$page_description = 'Insights, explorations, and thoughts on cybersecurity, technology, and personal growth by Md. Irtija Azad Talha.';
$page_canonical   = 'https://irtizaa6x.github.io/blog.php';
$current_page     = 'blog';

// --- Page-specific styles (to ensure new overlay elements are styled) ---
$page_styles = '
    .blog-card-excerpt-overlay {
        font-size: 0.85rem;
        line-height: 1.5;
        opacity: 0.9;
        margin: var(--space-2) 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: rgba(255,255,255,0.85);
    }

    .blog-card-overlay .blog-card-hint {
        font-size: 0.75rem;
        font-weight: 600;
        opacity: 0.9;
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        border-bottom: 1.5px dashed rgba(255,255,255,0.30);
        padding-bottom: var(--space-1);
        margin-top: var(--space-2);
    }

    /* Mobile overlay toggle state */
    .blog-card-overlay.mobile-expanded {
        opacity: 1;
        pointer-events: auto;
    }

    @media (max-width: 768px) {
        .blog-card-overlay {
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition);
        }
        .blog-card-overlay.mobile-expanded {
            opacity: 1;
            pointer-events: auto;
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
                <span class="section-tag">Blog</span>
                <h1 class="page-hero-title" id="page-hero-title">
                    Insights &amp; Explorations
                </h1>
                <p class="page-hero-subtitle">
                    Documenting my journey into cybersecurity, technology, and
                    personal growth — one post at a time.
                </p>
                <div class="page-hero-stats">
                    <div class="hero-stat" id="postCount">
                        <span class="hero-stat-number">0</span>
                        <span class="hero-stat-label">Posts</span>
                    </div>
                    <div class="hero-stat-divider"></div>
                    <div class="hero-stat">
                        <span class="hero-stat-number"><i class="fas fa-tags" style="font-size:1.2rem;"></i></span>
                        <span class="hero-stat-label">Topics</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         BLOG TOOLBAR (Search & Filters)
         ============================================================ -->
    <section class="blog-toolbar" aria-label="Blog search and filters">
        <div class="container">
            <div class="filter-bar">
                <div class="blog-search">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        id="blogSearch"
                        placeholder="Search posts..."
                        aria-label="Search blog posts"
                    />
                </div>
                <div class="blog-filters" id="blogFilters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="cybersecurity">Cybersecurity</button>
                    <button class="filter-btn" data-filter="web-dev">Web Dev</button>
                    <button class="filter-btn" data-filter="projects">Projects</button>
                    <button class="filter-btn" data-filter="events">Events &amp; Clubs</button>
                </div>
                <span class="blog-count" id="blogCount">0 posts</span>
            </div>
        </div>
    </section>

    <!-- ============================================================
         BLOG GRID
         ============================================================ -->
    <section class="blog-grid-section" aria-labelledby="blog-grid-title">
        <div class="container">
            <div class="blog-grid" id="blogGrid">
                <!-- Dynamically populated -->
            </div>
            <div class="no-results" id="noResults" style="display:none;">
                <i class="fas fa-book-open"></i>
                <h3>No posts found</h3>
                <p>Try adjusting your search or filter criteria.</p>
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
                    <h2 id="cta-title">Let's Connect</h2>
                    <p>
                        Have a question about a post? Want to collaborate on a
                        project? I'd love to hear from you.
                    </p>
                    <div class="cta-actions">
                        <a href="contact.php" class="btn btn-cta-primary">
                            <i class="fas fa-paper-plane"></i> Get in Touch
                        </a>
                        <a href="projects.php" class="btn btn-cta-secondary">
                            <i class="fas fa-code-branch"></i> Explore Projects
                        </a>
                    </div>
                </div>
                <div class="cta-decoration" aria-hidden="true">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
    </section>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>

<!-- ============================================================
     BLOG LOGIC: FETCH, RENDER, FILTER (Self-contained)
     ============================================================ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js" defer></script>
<script src="config.js" defer></script>

<script>
    (function() {
        'use strict';

        // ============================================================
        //  1.  CONFIGURATION
        // ============================================================

        const CONFIG = window.CONFIG || {
            GITHUB_USER: 'Irtizaa6x',
            GITHUB_REPO: 'Irtizaa6x.github.io',
            BRANCH: 'main',
            POSTS_PATH: 'src/posts',
            BLOG_DETAIL_PATH: '/blog-detail',
        };

        const RETRY_ATTEMPTS = 3;
        const RETRY_DELAY_MS = 1000;

        // ============================================================
        //  2.  DOM REFS
        // ============================================================

        const grid = document.getElementById('blogGrid');
        const noResults = document.getElementById('noResults');
        const searchInput = document.getElementById('blogSearch');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const countEl = document.getElementById('blogCount');
        const postCountStat = document.querySelector('#postCount .hero-stat-number');

        let allPosts = [];
        let filteredPosts = [];
        let currentFilter = 'all';
        let currentSearch = '';

        // ============================================================
        //  3.  HELPERS
        // ============================================================

        function slugify(text) {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function formatDate(dateStr) {
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            } catch (_) {
                return dateStr;
            }
        }

        function getCategoryFromTags(tags) {
            if (!tags || !tags.length) return 'General';
            const tag = tags[0].toLowerCase();
            const map = {
                'code': 'Code',
                'cybersecurity': 'Cybersecurity',
                'club': 'Club',
                'workshop': 'Workshop',
                'visit': 'Visit',
                'event': 'Event',
                'project': 'Project',
                'certification': 'Certification',
            };
            return map[tag] || tags[0].charAt(0).toUpperCase() + tags[0].slice(1);
        }

        function getSlug(title) {
            return slugify(title);
        }

        // ============================================================
        //  4.  FETCH WITH RETRY
        // ============================================================

        async function fetchWithRetry(url, retries = RETRY_ATTEMPTS, delay = RETRY_DELAY_MS) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response;
            } catch (error) {
                if (retries <= 0) {
                    throw error;
                }
                await new Promise((resolve) => setTimeout(resolve, delay));
                return fetchWithRetry(url, retries - 1, delay * 1.5);
            }
        }

        // ============================================================
        //  5.  FETCH POSTS
        // ============================================================

        async function fetchPosts() {
            try {
                const listUrl =
                    `https://api.github.com/repos/${CONFIG.GITHUB_USER}/${CONFIG.GITHUB_REPO}/contents/${CONFIG.POSTS_PATH}?ref=${CONFIG.BRANCH}&_t=${Date.now()}`;
                const listResponse = await fetchWithRetry(listUrl);
                const files = await listResponse.json();

                if (!Array.isArray(files)) {
                    console.warn('GitHub API returned unexpected response:', files);
                    return [];
                }

                const mdFiles = files.filter(f => f.name && f.name.endsWith('.md') && f.download_url);
                if (mdFiles.length === 0) {
                    console.log('📭 No .md files found in the posts folder.');
                    return [];
                }

                const fetchPromises = mdFiles.map(async (file) => {
                    try {
                        const contentRes = await fetchWithRetry(file.download_url);
                        const markdown = await contentRes.text();

                        const frontmatterMatch = markdown.match(/^---\s*([\s\S]*?)\s*---/);
                        let data = {};
                        let content = markdown;

                        if (frontmatterMatch) {
                            try {
                                data = jsyaml.load(frontmatterMatch[1]) || {};
                            } catch (_) {
                                const lines = frontmatterMatch[1].split('\n');
                                const fallbackData = {};
                                lines.forEach(line => {
                                    const colon = line.indexOf(':');
                                    if (colon > 0) {
                                        let key = line.slice(0, colon).trim();
                                        let val = line.slice(colon + 1).trim();
                                        if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val
                                            .endsWith("'"))) {
                                            val = val.slice(1, -1);
                                        }
                                        fallbackData[key] = val;
                                    }
                                });
                                data = fallbackData;
                            }
                            content = markdown.replace(match[0], '').trim();
                        }

                        if (!data.title) {
                            console.warn(`⏩ Skipping ${file.name} – missing "title"`);
                            return null;
                        }

                        let gallery = data.gallery || [];
                        if (Array.isArray(gallery) && gallery.length > 0 && typeof gallery[0] === 'object' && gallery[0]
                            .image) {
                            gallery = gallery.map(item => item.image);
                        }

                        return {
                            ...data,
                            gallery,
                            content: content,
                            slug: data.slug || file.name.replace('.md', ''),
                            download_url: file.download_url,
                            fileName: file.name,
                        };
                    } catch (err) {
                        console.warn(`⚠️ Error parsing ${file.name}:`, err.message);
                        return null;
                    }
                });

                const results = await Promise.all(fetchPromises);
                const posts = results.filter(p => p !== null);

                posts.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    if (!isNaN(dateA) && !isNaN(dateB)) {
                        return dateB - dateA;
                    }
                    return a.fileName.localeCompare(b.fileName);
                });

                console.log(`✅ Loaded ${posts.length} blog post(s).`);
                return posts;
            } catch (error) {
                console.error('❌ Failed to fetch blog posts:', error);
                return [];
            }
        }

        // ============================================================
        //  6.  RENDER POSTS (New Overlay Design)
        // ============================================================

        function renderPosts(posts) {
            if (!grid) return;

            const count = posts.length;
            countEl.textContent = count + (count === 1 ? ' post' : ' posts');
            if (postCountStat) postCountStat.textContent = count;

            if (count === 0) {
                grid.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }
            noResults.style.display = 'none';

            grid.innerHTML = posts.map(post => {
                const category = getCategoryFromTags(post.tags);
                const dateStr = post.date ? formatDate(post.date) : '';
                const coverUrl = post.coverImage || '';
                const title = post.title || 'Untitled';
                const preview = post.excerpt || post.previewText || 'Click to read more.';

                return `
                    <div class="blog-card" data-slug="${encodeURIComponent(post.slug)}" role="article">
                        <div class="blog-card-image-wrapper">
                            ${coverUrl ? `
                                <img src="${coverUrl}" alt="${title}" class="blog-card-image" loading="lazy" />
                            ` : `
                                <div class="blog-card-image" style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.15); font-size:3rem;">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            `}
                            <span class="blog-card-category">${category}</span>
                            <div class="blog-card-overlay">
                                <h3 class="blog-card-title">${title}</h3>
                                ${dateStr ? `<time class="blog-card-date">${dateStr}</time>` : ''}
                                <span class="blog-card-category-overlay">${category}</span>
                                <p class="blog-card-excerpt-overlay">${preview}</p>
                                <span class="blog-card-hint">Click for more details →</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // --- Attach interactions ---
            document.querySelectorAll('.blog-card').forEach(card => {
                const overlay = card.querySelector('.blog-card-overlay');
                const slug = card.dataset.slug;

                if (!overlay) return;

                let isMobile = window.innerWidth <= 768;
                let isExpanded = false;

                // Update isMobile on resize
                window.addEventListener('resize', () => {
                    isMobile = window.innerWidth <= 768;
                    if (!isMobile && isExpanded) {
                        overlay.classList.remove('mobile-expanded');
                        isExpanded = false;
                    }
                });

                // Desktop: hover shows overlay, click navigates
                card.addEventListener('mouseenter', function() {
                    if (!isMobile) {
                        overlay.style.opacity = '1';
                        overlay.style.pointerEvents = 'auto';
                    }
                });

                card.addEventListener('mouseleave', function() {
                    if (!isMobile) {
                        overlay.style.opacity = '';
                        overlay.style.pointerEvents = '';
                    }
                });

                // Mobile: tap to expand, tap again to navigate
                card.addEventListener('click', function(e) {
                    if (isMobile) {
                        e.preventDefault();
                        if (!isExpanded) {
                            overlay.classList.add('mobile-expanded');
                            isExpanded = true;
                        } else {
                            if (slug) {
                                window.location.href = `blog-detail.php?slug=${slug}`;
                            }
                        }
                    } else {
                        // Desktop click navigates
                        if (slug) {
                            window.location.href = `blog-detail.php?slug=${slug}`;
                        }
                    }
                });

                // Keyboard support: Enter key navigates
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const slug = this.dataset.slug;
                        if (slug) {
                            window.location.href = `blog-detail.php?slug=${slug}`;
                        }
                    }
                });

                // Make card focusable for keyboard users
                card.setAttribute('tabindex', '0');
                card.setAttribute('role', 'button');
            });

            // Re-trigger scroll reveal
            if (window.IrtiJa && window.IrtiJa.ScrollReveal) {
                setTimeout(() => {
                    window.IrtiJa.ScrollReveal.refresh();
                }, 100);
            }
        }

        // ============================================================
        //  7.  FILTER & SEARCH
        // ============================================================

        function filterAndSearch() {
            let result = allPosts;

            if (currentFilter !== 'all') {
                result = result.filter(p => {
                    if (!p.tags) return false;
                    return p.tags.some(t => t.toLowerCase().includes(currentFilter));
                });
            }

            if (currentSearch.trim()) {
                const query = currentSearch.trim().toLowerCase();
                result = result.filter(p =>
                    p.title.toLowerCase().includes(query) ||
                    (p.excerpt && p.excerpt.toLowerCase().includes(query)) ||
                    (p.tags && p.tags.some(t => t.toLowerCase().includes(query)))
                );
            }

            filteredPosts = result;
            renderPosts(result);

            filterBtns.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.filter === currentFilter);
            });
        }

        // ============================================================
        //  8.  INIT
        // ============================================================

        async function init() {
            // Show loading state
            grid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:var(--space-8) 0;color:var(--text-muted);">
                    <i class="fas fa-spinner" style="font-size:2rem;animation:spin 1s linear infinite;display:block;margin-bottom:var(--space-3);"></i>
                    <p>Loading posts...</p>
                </div>
            `;

            allPosts = await fetchPosts();

            if (allPosts.length === 0) {
                grid.innerHTML = `
                    <div class="blog-empty" style="grid-column:1/-1;">
                        <i class="fas fa-book-open"></i>
                        <p>No blog posts published yet.<br />Check back soon for updates on my journey!</p>
                    </div>
                `;
                countEl.textContent = '0 posts';
                if (postCountStat) postCountStat.textContent = '0';
                return;
            }

            // Ensure each post has a slug
            allPosts = allPosts.map(p => ({
                ...p,
                slug: p.slug || getSlug(p.title)
            }));

            filteredPosts = allPosts;
            renderPosts(allPosts);

            // Setup search
            searchInput.addEventListener('input', (e) => {
                currentSearch = e.target.value;
                filterAndSearch();
            });

            // Setup filters
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    currentFilter = btn.dataset.filter;
                    filterAndSearch();
                });
            });

            console.log(`%c📝 Blog loaded: ${allPosts.length} posts`, 'color:#D4A853;font-weight:600;');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        // Ensure spin animation is defined for loading state
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

    })();
</script>

</body>
</html>
