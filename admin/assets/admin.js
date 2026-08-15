/**
 * admin/assets/admin.js
 *
 * Shared JavaScript for the IrtiJa admin panel.
 * Provides reusable functionality across all admin pages.
 *
 * @package IrtiJa
 * @version 1.0
 */

(function() {
    'use strict';

    // ============================================================
    //  1.  DOM READY CHECK
    // ============================================================

    /**
     * Execute a function when the DOM is ready.
     *
     * @param {Function} fn - The function to execute
     */
    function domReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    // ============================================================
    //  2.  UTILITY FUNCTIONS
    // ============================================================

    /**
     * Debounce a function call.
     *
     * @param {Function} fn - The function to debounce
     * @param {number} delay - Delay in milliseconds
     * @returns {Function} Debounced function
     */
    function debounce(fn, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    /**
     * Get a CSRF token from the page.
     * Looks for a meta tag, a global variable, or a hidden input.
     *
     * @returns {string|null} The CSRF token, or null if not found
     */
    function getCsrfToken() {
        // Check for global variable
        if (typeof window.csrfToken !== 'undefined') {
            return window.csrfToken;
        }

        // Check for meta tag
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            return meta.getAttribute('content');
        }

        // Check for hidden input
        const input = document.querySelector('input[name="csrf_token"]');
        if (input) {
            return input.value;
        }

        return null;
    }

    /**
     * Slugify a string for use in URLs.
     *
     * @param {string} text - The text to slugify
     * @returns {string} The slugified string
     */
    function slugify(text) {
        return text
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    /**
     * Format a file size in bytes to a human-readable string.
     *
     * @param {number} bytes - File size in bytes
     * @returns {string} Human-readable file size
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    /**
     * Check if an element is in the viewport.
     *
     * @param {Element} el - The element to check
     * @param {number} offset - Offset in pixels
     * @returns {boolean} True if the element is in the viewport
     */
    function isInViewport(el, offset) {
        if (!el) return false;
        const rect = el.getBoundingClientRect();
        const margin = offset || 0;
        return (
            rect.top < window.innerHeight - margin &&
            rect.bottom > margin &&
            rect.left < window.innerWidth - margin &&
            rect.right > margin
        );
    }

    // ============================================================
    //  3.  ADMIN MODULE
    // ============================================================

    const Admin = {
        /**
         * Initialize all admin functionality.
         */
        init: function() {
            this.sidebar.init();
            this.slug.init();
            this.confirm.init();
            this.gallery.init();
            this.preview.init();
            this.tags.init();
            this.unsaved.init();
            this.table.init();

            // Store CSRF token globally for AJAX
            window.csrfToken = getCsrfToken();

            // Log initialization
            console.log('%c⚙️ Admin JS initialized', 'color:#D4A853;font-weight:600;');
        },

        // ============================================================
        //  3a.  SIDEBAR MODULE (Mobile Toggle)
        // ============================================================

        sidebar: {
            /**
             * Initialize mobile sidebar functionality.
             */
            init: function() {
                const toggle = document.getElementById('adminSidebarToggle');
                const sidebar = document.querySelector('.admin-sidebar');

                if (!toggle || !sidebar) return;

                // Toggle on click
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('open');
                    const isOpen = sidebar.classList.contains('open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    toggle.innerHTML = isOpen ?
                        '<i class="fas fa-times"></i>' :
                        '<i class="fas fa-bars"></i>';
                });

                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });

                // Close on resize to desktop
                window.addEventListener('resize', debounce(function() {
                    if (window.innerWidth > 768 && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                }, 150));

                // Close on overlay click (if overlay exists)
                const overlay = document.querySelector('.admin-sidebar-overlay');
                if (overlay) {
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('open');
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.innerHTML = '<i class="fas fa-bars"></i>';
                    });
                }
            }
        },

        // ============================================================
        //  3b.  SLUG MODULE (Auto-generation)
        // ============================================================

        slug: {
            /**
             * Initialize slug auto-generation.
             */
            init: function() {
                const nameInputs = document.querySelectorAll('[data-slug-source]');
                const slugInputs = document.querySelectorAll('[data-slug-target]');

                if (!nameInputs.length || !slugInputs.length) return;

                nameInputs.forEach((source) => {
                    const targetId = source.getAttribute('data-slug-source');
                    const target = document.querySelector('[data-slug-target="' + targetId + '"]');
                    if (!target) return;

                    // Generate slug on blur
                    source.addEventListener('blur', function() {
                        if (!target.value.trim()) {
                            target.value = slugify(this.value);
                        }
                    });

                    // Also generate on input if the slug field is empty
                    source.addEventListener('input', function() {
                        if (!target.value.trim()) {
                            target.value = slugify(this.value);
                        }
                    });
                });

                // Manual slug generation trigger via button
                document.querySelectorAll('[data-slug-generate]').forEach((btn) => {
                    btn.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-slug-generate');
                        const target = document.querySelector('[data-slug-target="' + targetId + '"]');
                        const source = document.querySelector('[data-slug-source="' + targetId + '"]');
                        if (target && source) {
                            target.value = slugify(source.value);
                        }
                    });
                });
            },

            /**
             * Generate a slug from a string.
             *
             * @param {string} text - The text to slugify
             * @returns {string} The slugified string
             */
            generate: function(text) {
                return slugify(text);
            }
        },

        // ============================================================
        //  3c.  CONFIRM MODULE (Delete confirmations)
        // ============================================================

        confirm: {
            /**
             * Initialize delete confirmation dialogs.
             */
            init: function() {
                // Confirm buttons with data-confirm attribute
                document.querySelectorAll('[data-confirm]').forEach((el) => {
                    el.addEventListener('click', function(e) {
                        const message = this.getAttribute('data-confirm') || 'Are you sure?';
                        if (!confirm(message)) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });

                // Delete forms with confirmation class
                document.querySelectorAll('.confirm-delete-form').forEach((form) => {
                    form.addEventListener('submit', function(e) {
                        const message = this.getAttribute('data-confirm') ||
                            'Are you sure you want to delete this? This action cannot be undone.';
                        if (!confirm(message)) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });

                // Generic delete buttons
                document.querySelectorAll('.btn-delete, .delete-action').forEach((el) => {
                    el.addEventListener('click', function(e) {
                        const message = this.getAttribute('data-confirm') ||
                            'Are you sure you want to delete this? This action cannot be undone.';
                        if (!confirm(message)) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });
            },

            /**
             * Show a confirmation dialog.
             *
             * @param {string} message - The confirmation message
             * @param {Function} onConfirm - Callback on confirm
             * @param {Function} onCancel - Callback on cancel
             */
            show: function(message, onConfirm, onCancel) {
                if (confirm(message || 'Are you sure?')) {
                    if (typeof onConfirm === 'function') onConfirm();
                } else {
                    if (typeof onCancel === 'function') onCancel();
                }
            }
        },

        // ============================================================
        //  3d.  GALLERY MODULE (Management)
        // ============================================================

        gallery: {
            /**
             * Initialize gallery management.
             */
            init: function() {
                this.handleRemove();
                this.handleReorder();
                this.handleUpload();
            },

            /**
             * Handle gallery image removal.
             */
            handleRemove: function() {
                // Existing gallery removal
                document.querySelectorAll('.gallery-preview-item .remove-gallery').forEach((btn) => {
                    btn.addEventListener('click', function() {
                        const parent = this.closest('.gallery-preview-item');
                        const id = this.getAttribute('data-id');
                        if (!parent) return;

                        if (confirm('Remove this image from the gallery?')) {
                            // If there's a hidden input for this ID, remove it
                            const hidden = document.querySelector(
                                'input.keep-gallery-input[value="' + id + '"]'
                            );
                            if (hidden) {
                                hidden.remove();
                            }
                            // Remove the preview
                            parent.remove();
                        }
                    });
                });

                // Handle dynamic gallery removal (new uploads) via event delegation
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.gallery-preview-item .remove-gallery');
                    if (!btn) return;
                    if (!btn.hasAttribute('data-id') && btn.closest('#galleryPreviewGrid')) {
                        // New upload removal - handled by the upload preview logic
                        // The preview item should already have its own removal logic
                    }
                });
            },

            /**
             * Handle gallery reordering (if supported).
             */
            handleReorder: function() {
                const grid = document.getElementById('galleryPreviewGrid');
                if (!grid) return;

                // Simple drag-and-drop reorder (basic implementation)
                // This is a placeholder - full drag-and-drop would require additional libraries
                // For now, we'll just support sortable via drag and drop if needed
                // Most admin interfaces use simple reorder via arrow buttons

                // Reorder buttons
                document.querySelectorAll('.gallery-reorder-up, .gallery-reorder-down').forEach((btn) => {
                    btn.addEventListener('click', function() {
                        const item = this.closest('.gallery-preview-item');
                        if (!item) return;
                        const grid = item.parentElement;
                        const siblings = Array.from(grid.children);
                        const index = siblings.indexOf(item);

                        if (this.classList.contains('gallery-reorder-up') && index > 0) {
                            grid.insertBefore(item, siblings[index - 1]);
                        } else if (this.classList.contains('gallery-reorder-down') && index < siblings.length - 1) {
                            grid.insertBefore(item, siblings[index + 2]);
                        }
                    });
                });
            },

            /**
             * Handle gallery upload preview.
             */
            handleUpload: function() {
                const input = document.getElementById('gallery_images');
                const previewGrid = document.getElementById('galleryPreviewGrid');

                if (!input || !previewGrid) return;

                input.addEventListener('change', function() {
                    const files = this.files;
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const div = document.createElement('div');
                            div.className = 'gallery-preview-item';
                            const img = document.createElement('img');
                            img.src = event.target.result;
                            img.alt = file.name;
                            div.appendChild(img);

                            const removeBtn = document.createElement('button');
                            removeBtn.className = 'remove-gallery';
                            removeBtn.innerHTML = '&times;';
                            removeBtn.setAttribute('type', 'button');
                            removeBtn.setAttribute('title', 'Remove this image');
                            removeBtn.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Remove the file from the input
                                const dt = new DataTransfer();
                                const filesList = input.files;
                                const index = Array.from(previewGrid.children).indexOf(div);
                                for (let j = 0; j < filesList.length; j++) {
                                    if (j !== index) {
                                        dt.items.add(filesList[j]);
                                    }
                                }
                                input.files = dt.files;
                                // Remove the preview
                                div.remove();
                            });
                            div.appendChild(removeBtn);
                            previewGrid.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },

            /**
             * Add a gallery item to the preview grid.
             *
             * @param {string} imageUrl - The image URL
             * @param {string} id - Optional image ID (for existing images)
             * @param {number} sortOrder - Optional sort order
             */
            addItem: function(imageUrl, id, sortOrder) {
                const grid = document.getElementById('galleryPreviewGrid');
                if (!grid) return;

                const div = document.createElement('div');
                div.className = 'gallery-preview-item';
                if (id) {
                    div.setAttribute('data-id', id);
                }
                if (sortOrder !== undefined) {
                    div.setAttribute('data-sort', sortOrder);
                }

                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = 'Gallery image';
                div.appendChild(img);

                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-gallery';
                removeBtn.innerHTML = '&times;';
                removeBtn.setAttribute('type', 'button');
                removeBtn.setAttribute('title', 'Remove this image');
                removeBtn.addEventListener('click', function() {
                    if (confirm('Remove this image from the gallery?')) {
                        const id = div.getAttribute('data-id');
                        if (id) {
                            const hidden = document.querySelector(
                                'input.keep-gallery-input[value="' + id + '"]'
                            );
                            if (hidden) hidden.remove();
                        }
                        div.remove();
                    }
                });
                div.appendChild(removeBtn);
                grid.appendChild(div);
            },

            /**
             * Remove a gallery item by ID.
             *
             * @param {string|number} id - The gallery item ID
             */
            removeItem: function(id) {
                const item = document.querySelector('.gallery-preview-item[data-id="' + id + '"]');
                if (item) {
                    item.remove();
                }
                const hidden = document.querySelector('input.keep-gallery-input[value="' + id + '"]');
                if (hidden) {
                    hidden.remove();
                }
            },

            /**
             * Get all gallery item data.
             *
             * @returns {Array} Array of gallery item data
             */
            getItems: function() {
                const items = [];
                document.querySelectorAll('.gallery-preview-item').forEach((el) => {
                    const id = el.getAttribute('data-id');
                    const sort = el.getAttribute('data-sort');
                    items.push({
                        id: id || null,
                        sort: sort !== null ? parseInt(sort, 10) : null,
                        element: el
                    });
                });
                return items;
            }
        },

        // ============================================================
        //  3e.  PREVIEW MODULE (Image previews)
        // ============================================================

        preview: {
            /**
             * Initialize image preview functionality.
             */
            init: function() {
                this.setupCoverPreview();
                this.setupGalleryPreview();
            },

            /**
             * Setup cover image preview.
             */
            setupCoverPreview: function() {
                const input = document.getElementById('cover_image');
                const preview = document.getElementById('coverPreview');
                const previewImg = document.getElementById('coverPreviewImg');

                if (!input || !preview || !previewImg) return;

                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) {
                        preview.style.display = 'none';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImg.src = event.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            },

            /**
             * Setup gallery preview.
             */
            setupGalleryPreview: function() {
                // Handled by gallery module
            },

            /**
             * Show a preview of an uploaded image.
             *
             * @param {File} file - The uploaded file
             * @param {string} containerId - The container element ID
             */
            showImage: function(file, containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;

                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.alt = file.name;
                    img.className = 'preview-image';
                    container.innerHTML = '';
                    container.appendChild(img);
                    container.style.display = 'block';
                };
                reader.readAsDataURL(file);
            },

            /**
             * Clear an image preview.
             *
             * @param {string} containerId - The container element ID
             */
            clearImage: function(containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;
                container.innerHTML = '';
                container.style.display = 'none';
            }
        },

        // ============================================================
        //  3f.  TAGS MODULE (Tag input handling)
        // ============================================================

        tags: {
            /**
             * Initialize tag input functionality.
             */
            init: function() {
                const input = document.getElementById('tags');
                if (!input) return;

                // Add visual hint for tag input
                input.setAttribute('placeholder', input.getAttribute('placeholder') || 'Comma separated: cybersecurity, web-dev');

                // Auto-complete for common tags? We'll keep it simple.
                // Users can type comma-separated values.
            },

            /**
             * Get tags as an array from the input.
             *
             * @param {string} inputId - The input element ID
             * @returns {Array} Array of tag strings
             */
            getTags: function(inputId) {
                const input = document.getElementById(inputId) || document.querySelector('[name="tags"]');
                if (!input) return [];
                return input.value.split(',').map(t => t.trim()).filter(t => t !== '');
            },

            /**
             * Set tags from an array.
             *
             * @param {string} inputId - The input element ID
             * @param {Array} tags - Array of tag strings
             */
            setTags: function(inputId, tags) {
                const input = document.getElementById(inputId) || document.querySelector('[name="tags"]');
                if (!input) return;
                input.value = tags.join(', ');
            },

            /**
             * Add a tag to the input.
             *
             * @param {string} inputId - The input element ID
             * @param {string} tag - The tag to add
             */
            addTag: function(inputId, tag) {
                const input = document.getElementById(inputId) || document.querySelector('[name="tags"]');
                if (!input) return;
                const current = this.getTags(inputId);
                if (!current.includes(tag)) {
                    current.push(tag);
                    this.setTags(inputId, current);
                }
            },

            /**
             * Remove a tag from the input.
             *
             * @param {string} inputId - The input element ID
             * @param {string} tag - The tag to remove
             */
            removeTag: function(inputId, tag) {
                const input = document.getElementById(inputId) || document.querySelector('[name="tags"]');
                if (!input) return;
                const current = this.getTags(inputId);
                const filtered = current.filter(t => t !== tag);
                this.setTags(inputId, filtered);
            }
        },

        // ============================================================
        //  3g.  UNSAVED CHANGES MODULE
        // ============================================================

        unsaved: {
            /**
             * Initialize unsaved changes warning.
             */
            init: function() {
                const forms = document.querySelectorAll('.form-card form, .unsaved-warning-form');
                if (!forms.length) return;

                let isDirty = false;

                forms.forEach((form) => {
                    // Mark form as dirty on any input change
                    form.addEventListener('input', function() {
                        isDirty = true;
                    });

                    // Mark form as dirty on select change
                    form.addEventListener('change', function() {
                        isDirty = true;
                    });

                    // Reset on form submit
                    form.addEventListener('submit', function() {
                        isDirty = false;
                    });

                    // Handle tinyMCE changes
                    if (typeof tinymce !== 'undefined') {
                        tinymce.on('AddEditor', function(e) {
                            e.editor.on('change', function() {
                                isDirty = true;
                            });
                        });
                    }
                });

                // Warn before leaving the page
                window.addEventListener('beforeunload', function(e) {
                    if (isDirty) {
                        e.preventDefault();
                        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                        return e.returnValue;
                    }
                });

                // Also warn on navigation within the admin
                document.querySelectorAll('a:not([target="_blank"]):not([data-no-warn])').forEach((link) => {
                    link.addEventListener('click', function(e) {
                        if (isDirty && this.href) {
                            const targetUrl = this.href;
                            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                                e.preventDefault();
                                return false;
                            }
                            // Reset dirty state if user proceeds
                            isDirty = false;
                        }
                    });
                });
            },

            /**
             * Mark the form as dirty.
             */
            setDirty: function() {
                window._adminUnsavedDirty = true;
            },

            /**
             * Mark the form as clean.
             */
            setClean: function() {
                window._adminUnsavedDirty = false;
            },

            /**
             * Check if the form has unsaved changes.
             *
             * @returns {boolean} True if there are unsaved changes
             */
            isDirty: function() {
                return window._adminUnsavedDirty === true;
            }
        },

        // ============================================================
        //  3h.  TABLE MODULE (Table utilities)
        // ============================================================

        table: {
            /**
             * Initialize table functionality.
             */
            init: function() {
                this.handleRowClick();
                this.handleSort();
            },

            /**
             * Handle row click for navigation.
             */
            handleRowClick: function() {
                document.querySelectorAll('.clickable-row').forEach((row) => {
                    row.addEventListener('click', function() {
                        const url = this.getAttribute('data-href');
                        if (url) {
                            window.location.href = url;
                        }
                    });
                });
            },

            /**
             * Handle table sorting (basic implementation).
             */
            handleSort: function() {
                document.querySelectorAll('.sortable-table .sortable-header').forEach((th) => {
                    th.addEventListener('click', function() {
                        const table = this.closest('table');
                        const col = Array.from(this.parentElement.children).indexOf(this);
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.children);
                        const ascending = this.classList.contains('asc');

                        // Clear sort indicators
                        table.querySelectorAll('.sortable-header').forEach((header) => {
                            header.classList.remove('asc', 'desc');
                        });

                        // Sort rows
                        rows.sort((a, b) => {
                            const aVal = a.children[col]?.textContent?.trim() || '';
                            const bVal = b.children[col]?.textContent?.trim() || '';
                            return ascending ?
                                aVal.localeCompare(bVal) :
                                bVal.localeCompare(aVal);
                        });

                        // Reorder rows
                        rows.forEach((row) => tbody.appendChild(row));

                        // Update sort indicator
                        this.classList.toggle('asc', !ascending);
                        this.classList.toggle('desc', ascending);
                    });
                });
            }
        },

        // ============================================================
        //  3i.  FORM MODULE (Form utilities)
        // ============================================================

        form: {
            /**
             * Validate a form field.
             *
             * @param {Element} field - The form field to validate
             * @param {Object} rules - Validation rules
             * @returns {boolean} True if valid
             */
            validateField: function(field, rules) {
                let valid = true;
                const value = field.value.trim();

                if (rules.required && !value) {
                    field.classList.add('error');
                    valid = false;
                } else if (rules.minLength && value.length < rules.minLength) {
                    field.classList.add('error');
                    valid = false;
                } else if (rules.maxLength && value.length > rules.maxLength) {
                    field.classList.add('error');
                    valid = false;
                } else if (rules.pattern && !rules.pattern.test(value)) {
                    field.classList.add('error');
                    valid = false;
                } else {
                    field.classList.remove('error');
                }

                return valid;
            },

            /**
             * Validate an entire form.
             *
             * @param {Element} form - The form to validate
             * @param {Object} rules - Validation rules per field
             * @returns {boolean} True if all fields are valid
             */
            validateForm: function(form, rules) {
                let allValid = true;
                for (const [selector, fieldRules] of Object.entries(rules)) {
                    const field = form.querySelector(selector);
                    if (field) {
                        const isValid = this.validateField(field, fieldRules);
                        if (!isValid) allValid = false;
                    }
                }
                return allValid;
            },

            /**
             * Show a validation error message.
             *
             * @param {Element} field - The field with an error
             * @param {string} message - The error message
             */
            showError: function(field, message) {
                field.classList.add('error');
                const parent = field.closest('.form-group');
                if (parent) {
                    const existing = parent.querySelector('.validation-error');
                    if (existing) existing.remove();
                    const error = document.createElement('div');
                    error.className = 'validation-error';
                    error.textContent = message;
                    error.style.color = 'var(--admin-error)';
                    error.style.fontSize = '0.8rem';
                    error.style.marginTop = '0.25rem';
                    parent.appendChild(error);
                }
            },

            /**
             * Clear validation errors from a field.
             *
             * @param {Element} field - The field to clear errors from
             */
            clearError: function(field) {
                field.classList.remove('error');
                const parent = field.closest('.form-group');
                if (parent) {
                    const error = parent.querySelector('.validation-error');
                    if (error) error.remove();
                }
            },

            /**
             * Clear all validation errors from a form.
             *
             * @param {Element} form - The form to clear errors from
             */
            clearErrors: function(form) {
                form.querySelectorAll('.error').forEach((field) => {
                    field.classList.remove('error');
                });
                form.querySelectorAll('.validation-error').forEach((el) => {
                    el.remove();
                });
            }
        },

        // ============================================================
        //  3j.  NOTIFICATION MODULE (Toast notifications)
        // ============================================================

        notify: {
            /**
             * Show a notification toast.
             *
             * @param {string} message - The notification message
             * @param {string} type - The notification type (success, error, warning, info)
             * @param {number} duration - Duration in milliseconds
             */
            show: function(message, type, duration) {
                type = type || 'info';
                duration = duration || 3000;

                const container = document.getElementById('adminNotifications') || (function() {
                    const el = document.createElement('div');
                    el.id = 'adminNotifications';
                    el.style.cssText = `
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        z-index: 9999;
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                        max-width: 380px;
                        width: 100%;
                        pointer-events: none;
                    `;
                    document.body.appendChild(el);
                    return el;
                })();

                const colors = {
                    success: '#2B8C6E',
                    error: '#C44A4A',
                    warning: '#D4A853',
                    info: '#4A8AA8'
                };

                const bgColors = {
                    success: 'rgba(43,140,110,0.08)',
                    error: 'rgba(196,74,74,0.08)',
                    warning: 'rgba(212,168,83,0.08)',
                    info: 'rgba(74,138,168,0.08)'
                };

                const toast = document.createElement('div');
                toast.style.cssText = `
                    background: var(--admin-bg-card, #FCFAF5);
                    border-left: 4px solid ${colors[type] || colors.info};
                    border-radius: 8px;
                    padding: 12px 16px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
                    border: 1px solid var(--admin-border-subtle, rgba(213,207,196,0.20));
                    pointer-events: auto;
                    animation: slideIn 0.3s ease;
                    font-size: 0.875rem;
                    color: var(--admin-text-primary, #1A1A1A);
                `;
                toast.textContent = message;

                // Add icon
                const iconMap = {
                    success: 'fa-check-circle',
                    error: 'fa-exclamation-circle',
                    warning: 'fa-exclamation-triangle',
                    info: 'fa-info-circle'
                };
                const icon = document.createElement('i');
                icon.className = 'fas ' + (iconMap[type] || iconMap.info);
                icon.style.cssText = `
                    margin-right: 8px;
                    color: ${colors[type] || colors.info};
                `;
                toast.prepend(icon);

                container.appendChild(toast);

                // Auto-remove after duration
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(20px)';
                    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, duration);

                // Add animation styles
                if (!document.getElementById('adminNotificationStyles')) {
                    const styles = document.createElement('style');
                    styles.id = 'adminNotificationStyles';
                    styles.textContent = `
                        @keyframes slideIn {
                            from {
                                opacity: 0;
                                transform: translateX(20px);
                            }
                            to {
                                opacity: 1;
                                transform: translateX(0);
                            }
                        }
                    `;
                    document.head.appendChild(styles);
                }
            },

            /**
             * Show a success notification.
             *
             * @param {string} message - The notification message
             * @param {number} duration - Duration in milliseconds
             */
            success: function(message, duration) {
                this.show(message, 'success', duration);
            },

            /**
             * Show an error notification.
             *
             * @param {string} message - The notification message
             * @param {number} duration - Duration in milliseconds
             */
            error: function(message, duration) {
                this.show(message, 'error', duration || 5000);
            },

            /**
             * Show a warning notification.
             *
             * @param {string} message - The notification message
             * @param {number} duration - Duration in milliseconds
             */
            warning: function(message, duration) {
                this.show(message, 'warning', duration);
            },

            /**
             * Show an info notification.
             *
             * @param {string} message - The notification message
             * @param {number} duration - Duration in milliseconds
             */
            info: function(message, duration) {
                this.show(message, 'info', duration);
            }
        },

        // ============================================================
        //  3k.  UTILITY HELPERS
        // ============================================================

        /**
         * Get the CSRF token.
         *
         * @returns {string|null} The CSRF token
         */
        getCsrfToken: function() {
            return getCsrfToken();
        },

        /**
         * Slugify a string.
         *
         * @param {string} text - The text to slugify
         * @returns {string} The slugified string
         */
        slugify: function(text) {
            return slugify(text);
        },

        /**
         * Format a file size.
         *
         * @param {number} bytes - File size in bytes
         * @returns {string} Human-readable file size
         */
        formatFileSize: function(bytes) {
            return formatFileSize(bytes);
        }
    };

    // ============================================================
    //  4.  EXPOSE ADMIN GLOBALLY
    // ============================================================

    // Expose Admin object globally
    window.Admin = Admin;

    // --- Initialize on DOM ready ---
    domReady(function() {
        Admin.init();
    });

    // ============================================================
    //  5.  CONSOLE BRANDING
    // ============================================================

    console.log('%c🔐 IrtiJa Admin Panel v1.0', 'color:#D4A853;font-weight:700;font-size:14px;');
    console.log('%c📦 Admin JS loaded', 'color:#1A7A74;font-weight:500;');

})();
