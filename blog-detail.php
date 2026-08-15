<?php
/**
 * blog-detail.php
 *
 * Blog detail page for IrtiJa portfolio.
 * Displays a single blog post with full content, gallery, and metadata.
 *
 * @package IrtiJa
 * @version 1.0
 */

// --- Get the slug from the query string ---
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// --- Page-specific variables (will be updated after fetching post) ---
$page_title = 'Blog Post · IrtiJa';
$page_description = 'Read the full blog post by Md. Irtija Azad Talha — insights on cybersecurity, technology, and personal growth.';
$page_canonical = 'https://irtizaa6x.github.io/blog-detail.php?slug=' . urlencode($slug);
$current_page = 'blog';

// --- Include the shared header ---
include 'header.php';
?>

<!-- ============================================================
     BLOG DETAIL PAGE
     ============================================================ -->

<!-- Reading Progress Bar -->
<div class="reading-progress" role="progressbar" aria-label="Reading progress" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
    <div class="reading-progress-bar" id="readingProgressBar"></div>
</div>

<!-- Lightbox (gallery viewer) -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
    <button class="lightbox-close" id="lightboxClose" aria-label="Close image viewer">
        <i class="fas fa-times"></i>
    </button>
    <img id="lightboxImage" src="" alt="Gallery image" />
</div>

<main class="blog-detail-page" id="blogDetailPage">
    <article class="blog-detail-container" id="blogDetailContainer" aria-label="Blog post content">

        <!-- Loading state -->
        <div class="blog-loading" id="loadingState">
            <i class="fas fa-spinner"></i>
            <p>Loading post...</p>
        </div>

        <!-- Error state (hidden by default) -->
        <div class="blog-error" id="errorState" style="display:none;">
            <i class="fas fa-exclamation-circle"></i>
            <h2>Post Not Found</h2>
            <p>The blog post you're looking for doesn't exist or couldn't be loaded.</p>
            <a href="blog.php" class="btn btn-primary" style="margin-top:var(--space-4);">
                <i class="fas fa-arrow-left"></i> Back to Blog
            </a>
        </div>

        <!-- Dynamic content will be injected here -->
        <div id="postContent" style="display:none;">
            <!-- Back button -->
            <a href="blog.php" class="back-to-blog">
                <i class="fas fa-arrow-left"></i> Back to Blog
            </a>

            <!-- Cover image -->
            <div id="postCover"></div>

            <!-- Title -->
            <h1 class="detail-title" id="postTitle"></h1>

            <!-- Meta -->
            <div class="detail-meta" id="postMeta"></div>

            <!-- Tags -->
            <div class="detail-tags" id="postTags"></div>

            <!-- Table of Contents -->
            <div class="toc-container" id="tocContainer" style="display:none;">
                <div class="toc-title">
                    <i class="fas fa-list-ul"></i> Table of Contents
                </div>
                <ul class="toc-list" id="tocList"></ul>
            </div>

            <!-- Body -->
            <div class="detail-body" id="postBody"></div>

            <!-- Gallery -->
            <div class="detail-gallery-container" id="galleryContainer" style="display:none;">
                <button class="gallery-toggle-btn" id="galleryToggle">
                    <i class="fas fa-images"></i> Hide Gallery
                </button>
                <div class="detail-gallery" id="galleryGrid"></div>
            </div>

            <!-- Video -->
            <div class="detail-video" id="videoContainer" style="display:none;">
                <iframe id="videoIframe" src="" frameborder="0" allowfullscreen loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>

            <!-- Certificate -->
            <div id="certContainer"></div>

            <!-- Share -->
            <div class="share-section">
                <span class="share-label"><i class="fas fa-share-alt"></i> Share this post</span>
                <div class="share-buttons" id="shareButtons"></div>
            </div>

            <!-- Previous / Next -->
            <nav class="post-navigation" id="postNavigation" aria-label="Post navigation"></nav>
        </div>

    </article>
</main>

<?php
// --- Include the shared footer ---
include 'footer.php';
?>

<!-- ============================================================
     BLOG DETAIL LOGIC (Self-contained)
     ============================================================ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/15.0.4/marked.min.js" defer></script>

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
            SITE_URL: 'https://irtizaa6x.github.io',
        };

        const RETRY_ATTEMPTS = 3;
        const RETRY_DELAY_MS = 1000;

        // ============================================================
        //  2.  DOM REFS
        // ============================================================

        const loadingState = document.getElementById('loadingState');
        const errorState = document.getElementById('errorState');
        const postContent = document.getElementById('postContent');
        const postTitle = document.getElementById('postTitle');
        const postMeta = document.getElementById('postMeta');
        const postTags = document.getElementById('postTags');
        const postBody = document.getElementById('postBody');
        const postCover = document.getElementById('postCover');
        const tocContainer = document.getElementById('tocContainer');
        const tocList = document.getElementById('tocList');
        const galleryContainer = document.getElementById('galleryContainer');
        const galleryGrid = document.getElementById('galleryGrid');
        const galleryToggle = document.getElementById('galleryToggle');
        const videoContainer = document.getElementById('videoContainer');
        const videoIframe = document.getElementById('videoIframe');
        const certContainer = document.getElementById('certContainer');
        const shareButtons = document.getElementById('shareButtons');
        const postNavigation = document.getElementById('postNavigation');
        const readingProgressBar = document.getElementById('readingProgressBar');
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxClose = document.getElementById('lightboxClose');

        let allPosts = [];
        let currentPost = null;
        let currentSlug = null;

        // ============================================================
        //  3.  HELPERS
        // ============================================================

        function getParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
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

        function slugify(text) {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function getSlug(title) {
            return slugify(title);
        }

        function getHeadingId(text) {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
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
        //  5.  FETCH POST LIST
        // ============================================================

        async function fetchPostList() {
            try {
                const listUrl =
                    `https://api.github.com/repos/${CONFIG.GITHUB_USER}/${CONFIG.GITHUB_REPO}/contents/${CONFIG.POSTS_PATH}?ref=${CONFIG.BRANCH}&_t=${Date.now()}`;
                const listResponse = await fetchWithRetry(listUrl);
                const files = await listResponse.json();

                if (!Array.isArray(files)) {
                    return [];
                }

                const mdFiles = files.filter(f => f.name && f.name.endsWith('.md') && f.download_url);
                if (mdFiles.length === 0) {
                    return [];
                }

                const posts = [];
                for (const file of mdFiles) {
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
                            continue;
                        }

                        let gallery = data.gallery || [];
                        if (Array.isArray(gallery) && gallery.length > 0 && typeof gallery[0] === 'object' && gallery[0]
                            .image) {
                            gallery = gallery.map(item => item.image);
                        }

                        posts.push({
                            ...data,
                            gallery,
                            content: content,
                            slug: data.slug || file.name.replace('.md', ''),
                            download_url: file.download_url,
                            fileName: file.name,
                        });
                    } catch (err) {
                        console.warn('⚠️ Error parsing', file.name, err.message);
                    }
                }

                posts.sort((a, b) => {
                    const dateA = new Date(a.date);
                    const dateB = new Date(b.date);
                    if (!isNaN(dateA) && !isNaN(dateB)) {
                        return dateB - dateA;
                    }
                    return a.fileName.localeCompare(b.fileName);
                });

                return posts;
            } catch (error) {
                console.error('❌ Failed to fetch posts:', error);
                return [];
            }
        }

        // ============================================================
        //  6.  ENHANCE CODE BLOCKS
        // ============================================================

        function enhanceCodeBlocks(html) {
            const div = document.createElement('div');
            div.innerHTML = html;

            const preElements = div.querySelectorAll('pre');
            preElements.forEach((pre) => {
                const code = pre.querySelector('code');
                if (!code) return;

                if (pre.parentElement && pre.parentElement.classList.contains('code-wrapper')) return;

                let lang = '';
                if (code.className) {
                    const match = code.className.match(/language-([a-zA-Z0-9_-]+)/);
                    if (match) lang = match[1];
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'code-wrapper';

                const header = document.createElement('div');
                header.className = 'code-header';
                header.innerHTML = `
                    <span>${lang || 'code'}</span>
                    <button class="copy-btn" data-code="${code.textContent.replace(/"/g, '&quot;')}">
                        <i class="far fa-copy"></i> Copy
                    </button>
                `;

                const parent = pre.parentNode;
                wrapper.appendChild(header);
                wrapper.appendChild(pre.cloneNode(true));
                parent.replaceChild(wrapper, pre);

                const copyBtn = wrapper.querySelector('.copy-btn');
                copyBtn.addEventListener('click', function() {
                    const text = this.dataset.code;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(() => {
                            this.innerHTML = '<i class="fas fa-check"></i> Copied!';
                            this.classList.add('copied');
                            setTimeout(() => {
                                this.innerHTML = '<i class="far fa-copy"></i> Copy';
                                this.classList.remove('copied');
                            }, 2000);
                        }).catch(() => {
                            fallbackCopy(text, this);
                        });
                    } else {
                        fallbackCopy(text, this);
                    }
                });
            });

            return div.innerHTML;
        }

        function fallbackCopy(text, btn) {
            const input = document.createElement('input');
            input.value = text;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            try {
                document.execCommand('copy');
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = '<i class="far fa-copy"></i> Copy';
                    btn.classList.remove('copied');
                }, 2000);
            } catch (_) {
                alert('Could not copy code. Please copy manually.');
            }
            document.body.removeChild(input);
        }

        // ============================================================
        //  7.  GENERATE TABLE OF CONTENTS
        // ============================================================

        function generateTOC(html) {
            const div = document.createElement('div');
            div.innerHTML = html;

            const headings = div.querySelectorAll('h2, h3, h4');
            if (headings.length < 2) {
                tocContainer.style.display = 'none';
                return;
            }

            tocContainer.style.display = 'block';
            tocList.innerHTML = '';

            headings.forEach((heading) => {
                const level = heading.tagName.toLowerCase();
                const text = heading.textContent;
                const id = heading.id || getHeadingId(text);

                if (!heading.id) {
                    heading.id = id;
                }

                const li = document.createElement('li');
                li.className = `toc-${level}`;

                const a = document.createElement('a');
                a.href = `#${id}`;
                a.innerHTML = `<span class="toc-level">${level.toUpperCase()}</span> ${text}`;

                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    const target = document.getElementById(id);
                    if (target) {
                        const offset = 88;
                        const top = target.getBoundingClientRect().top + window.scrollY - offset;
                        window.scrollTo({ top, behavior: 'smooth' });
                    }
                });

                li.appendChild(a);
                tocList.appendChild(li);
            });
        }

        // ============================================================
        //  8.  RENDER POST
        // ============================================================

        function renderPost(post, allPostsList) {
            if (!post) {
                loadingState.style.display = 'none';
                errorState.style.display = 'block';
                postContent.style.display = 'none';
                return;
            }

            currentPost = post;
            loadingState.style.display = 'none';
            errorState.style.display = 'none';
            postContent.style.display = 'block';

            // Update page title
            document.title = `${post.title} · IrtiJa`;

            // Update meta tags
            const metaDesc = document.querySelector('meta[name="description"]');
            if (metaDesc) {
                metaDesc.content = post.excerpt || `Read "${post.title}" by Md. Irtija Azad Talha.`;
            }

            const ogTitle = document.querySelector('meta[property="og:title"]');
            if (ogTitle) ogTitle.content = post.title;

            const ogDesc = document.querySelector('meta[property="og:description"]');
            if (ogDesc) ogDesc.content = post.excerpt || `Read "${post.title}" by Md. Irtija Azad Talha.`;

            // Cover image
            if (post.coverImage) {
                postCover.innerHTML = `
                    <img src="${post.coverImage}" alt="${post.title}" class="detail-cover" loading="lazy" />
                `;
            } else {
                postCover.innerHTML = '';
            }

            // Title
            postTitle.textContent = post.title;

            // Meta
            const dateStr = post.date ? formatDate(post.date) : '';
            postMeta.innerHTML = `
                <span><i class="far fa-calendar-alt"></i> ${dateStr || 'No date'}</span>
                <span><i class="far fa-clock"></i> <span id="readingTime">~5 min read</span></span>
                <span><i class="fas fa-user"></i> <span class="detail-author">Md. Irtija Azad Talha</span></span>
            `;

            // Tags
            if (post.tags && post.tags.length) {
                postTags.innerHTML = post.tags.map(t =>
                    `<a href="blog.php?tag=${encodeURIComponent(t)}" class="tag">#${t}</a>`
                ).join('');
            } else {
                postTags.innerHTML = '';
            }

            // Body (Markdown -> HTML)
            let bodyHtml = '';
            if (typeof marked !== 'undefined') {
                try {
                    bodyHtml = marked.parse(post.content || '');
                } catch (err) {
                    console.warn('Markdown parsing failed:', err);
                    bodyHtml = `<p>${post.content || 'No content available.'}</p>`;
                }
            } else {
                // Fallback if marked.js isn't loaded
                bodyHtml = `<p>${post.content || 'No content available.'}</p>`;
            }

            // Enhance code blocks with copy buttons
            bodyHtml = enhanceCodeBlocks(bodyHtml);

            postBody.innerHTML = bodyHtml;

            // Generate Table of Contents
            generateTOC(postBody.innerHTML);

            // Gallery
            renderGallery(post);

            // Video
            renderVideo(post);

            // Certificate
            renderCertificate(post);

            // Share buttons
            renderShareButtons(post);

            // Previous / Next navigation
            renderNavigation(post, allPostsList);

            // Calculate reading time
            const wordCount = post.content ? post.content.split(/\s+/).length : 0;
            const readingTime = Math.max(1, Math.round(wordCount / 200));
            const readingTimeEl = document.getElementById('readingTime');
            if (readingTimeEl) {
                readingTimeEl.textContent = `~${readingTime} min read`;
            }

            // Re-run scroll reveal for new content
            if (window.IrtiJa && window.IrtiJa.ScrollReveal) {
                setTimeout(() => {
                    window.IrtiJa.ScrollReveal.refresh();
                }, 100);
            }
        }

        // ============================================================
        //  9.  RENDER GALLERY
        // ============================================================

        function renderGallery(post) {
            const gallery = post.gallery || [];
            if (!gallery || gallery.length === 0) {
                galleryContainer.style.display = 'none';
                return;
            }

            galleryContainer.style.display = 'block';
            let isVisible = true;

            galleryGrid.innerHTML = gallery.map(img =>
                `<img src="${img}" alt="Gallery image" loading="lazy" data-lightbox />`
            ).join('');

            const galleryImages = galleryGrid.querySelectorAll('img[data-lightbox]');
            galleryImages.forEach((img) => {
                img.addEventListener('click', () => {
                    openLightbox(img.src);
                });
            });

            galleryToggle.innerHTML = '<i class="fas fa-times"></i> Hide Gallery';
            galleryToggle.onclick = function() {
                isVisible = !isVisible;
                galleryGrid.style.display = isVisible ? 'grid' : 'none';
                this.innerHTML = isVisible ?
                    '<i class="fas fa-times"></i> Hide Gallery' :
                    `<i class="fas fa-images"></i> View Gallery (${gallery.length} images)`;
            };
        }

        // ============================================================
        //  10. RENDER VIDEO
        // ============================================================

        function renderVideo(post) {
            if (!post.videoURL) {
                videoContainer.style.display = 'none';
                return;
            }

            let embedUrl = post.videoURL;
            if (post.videoURL.includes('watch?v=')) {
                embedUrl = post.videoURL.replace('watch?v=', 'embed/');
            } else if (post.videoURL.includes('youtu.be/')) {
                embedUrl = post.videoURL.replace('youtu.be/', 'youtube.com/embed/');
            } else if (post.videoURL.includes('vimeo.com/')) {
                const id = post.videoURL.split('/').pop();
                embedUrl = `https://player.vimeo.com/video/${id}`;
            }

            if (embedUrl.includes('youtube') || embedUrl.includes('vimeo')) {
                videoContainer.style.display = 'block';
                videoIframe.src = embedUrl;
            } else {
                videoContainer.style.display = 'none';
            }
        }

        // ============================================================
        //  11. RENDER CERTIFICATE
        // ============================================================

        function renderCertificate(post) {
            if (!post.certificate) {
                certContainer.innerHTML = '';
                return;
            }

            certContainer.innerHTML = `
                <a href="${post.certificate}" target="_blank" rel="noopener noreferrer" class="detail-cert-btn">
                    <i class="fas fa-certificate"></i> View Certificate
                </a>
            `;
        }

        // ============================================================
        //  12. RENDER SHARE BUTTONS
        // ============================================================

        function renderShareButtons(post) {
            const url = encodeURIComponent(`${CONFIG.SITE_URL}/blog-detail.php?slug=${post.slug}`);
            const title = encodeURIComponent(post.title);

            const buttons = [
                { name: 'Twitter', icon: 'fab fa-x-twitter', url: `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
                    class: 'twitter' },
                { name: 'LinkedIn', icon: 'fab fa-linkedin-in', url: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
                    class: 'linkedin' },
                { name: 'Facebook', icon: 'fab fa-facebook-f', url: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
                    class: 'facebook' },
                { name: 'WhatsApp', icon: 'fab fa-whatsapp', url: `https://api.whatsapp.com/send?text=${title}%20${url}`,
                    class: 'whatsapp' },
                { name: 'Copy Link', icon: 'fas fa-link', url: '#', class: 'copy-link' },
            ];

            shareButtons.innerHTML = buttons.map((btn) => {
                const onClick = btn.name === 'Copy Link' ?
                    `onclick="event.preventDefault(); navigator.clipboard.writeText('${CONFIG.SITE_URL}/blog-detail.php?slug=${post.slug}').then(() => { this.innerHTML = '<i class=\\'fas fa-check\\'></i> Copied!'; setTimeout(() => { this.innerHTML = '<i class=\\'fas fa-link\\'></i> Copy Link'; }, 2000); });"` :
                    `onclick="window.open('${btn.url}','_blank','width=600,height=400'); return false;"`;

                return `
                    <button class="share-btn ${btn.class}" ${onClick}>
                        <i class="${btn.icon}"></i>
                        <span>${btn.name}</span>
                    </button>
                `;
            }).join('');
        }

        // ============================================================
        //  13. RENDER PREVIOUS / NEXT NAVIGATION
        // ============================================================

        function renderNavigation(post, allPostsList) {
            if (!allPostsList || allPostsList.length < 2) {
                postNavigation.innerHTML = '';
                return;
            }

            const index = allPostsList.findIndex(p => p.slug === post.slug);
            const prev = index > 0 ? allPostsList[index - 1] : null;
            const next = index < allPostsList.length - 1 ? allPostsList[index + 1] : null;

            if (!prev && !next) {
                postNavigation.innerHTML = '';
                return;
            }

            postNavigation.innerHTML = `
                ${prev ? `
                    <a href="blog-detail.php?slug=${prev.slug}" class="post-nav-item prev">
                        <span class="post-nav-label"><i class="fas fa-arrow-left post-nav-icon"></i> Previous</span>
                        <span class="post-nav-title">${prev.title}</span>
                    </a>
                ` : `<span></span>`}
                ${next ? `
                    <a href="blog-detail.php?slug=${next.slug}" class="post-nav-item next">
                        <span class="post-nav-label">Next <i class="fas fa-arrow-right post-nav-icon"></i></span>
                        <span class="post-nav-title">${next.title}</span>
                    </a>
                ` : `<span></span>`}
            `;
        }

        // ============================================================
        //  14. LIGHTBOX
        // ============================================================

        function openLightbox(src) {
            lightboxImage.src = src;
            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
        }

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        lightboxClose.addEventListener('click', closeLightbox);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeLightbox();
            }
        });

        // ============================================================
        //  15. READING PROGRESS
        // ============================================================

        function updateReadingProgress() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            if (readingProgressBar) {
                readingProgressBar.style.width = `${Math.min(progress, 100)}%`;
                readingProgressBar.setAttribute('aria-valuenow', Math.round(progress));
            }
        }

        // ============================================================
        //  16. INIT
        // ============================================================

        async function init() {
            currentSlug = getParam('slug');

            if (!currentSlug) {
                loadingState.style.display = 'none';
                errorState.style.display = 'block';
                errorState.querySelector('h2').textContent = 'No Post Selected';
                errorState.querySelector('p').textContent = 'Please select a blog post to read.';
                postContent.style.display = 'none';
                return;
            }

            allPosts = await fetchPostList();

            if (allPosts.length === 0) {
                loadingState.style.display = 'none';
                errorState.style.display = 'block';
                errorState.querySelector('h2').textContent = 'No Posts Available';
                errorState.querySelector('p').textContent = 'There are no blog posts to display at the moment.';
                postContent.style.display = 'none';
                return;
            }

            const post = allPosts.find(p => p.slug === currentSlug);

            if (!post) {
                loadingState.style.display = 'none';
                errorState.style.display = 'block';
                postContent.style.display = 'none';
                return;
            }

            renderPost(post, allPosts);

            window.addEventListener('scroll', updateReadingProgress, { passive: true });
            updateReadingProgress();
        }

        // ============================================================
        //  17. BOOTSTRAP
        // ============================================================

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

    })();
</script>

</body>
</html>
