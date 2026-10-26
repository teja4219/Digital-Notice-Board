// assets/js/script.js

document.addEventListener('DOMContentLoaded', function () {

    /* ── Mobile nav toggle (public) ── */
    const hamburger  = document.getElementById('navHamburger');
    const navLinks   = document.getElementById('siteNavLinks');
    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            hamburger.setAttribute('aria-expanded', navLinks.classList.contains('open'));
        });
    }

    /* ── Admin sidebar toggle (mobile) ── */
    const adminToggle = document.getElementById('adminNavToggle');
    const adminLinks  = document.getElementById('adminNavLinks');
    if (adminToggle && adminLinks) {
        adminToggle.addEventListener('click', () => {
            adminLinks.classList.toggle('open');
        });
    }

    /* ── Delete confirmation modal ── */
    const deleteModal   = document.getElementById('deleteModal');
    const deleteForm    = document.getElementById('deleteForm');
    const cancelDelete  = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');

    document.querySelectorAll('[data-delete-id]').forEach(btn => {
        btn.addEventListener('click', function () {
            const id    = this.dataset.deleteId;
            const title = this.dataset.deleteTitle || 'this notice';
            if (deleteModal && deleteForm) {
                deleteModal.querySelector('#deleteTargetName').textContent = title;
                deleteForm.querySelector('[name="id"]').value = id;
                deleteModal.classList.add('show');
            }
        });
    });

    if (cancelDelete) {
        cancelDelete.addEventListener('click', () => deleteModal.classList.remove('show'));
    }
    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === this) this.classList.remove('show');
        });
    }

    /* ── Category filter buttons (public board) ── */
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const category = this.dataset.category || '';
            const url      = new URL(window.location.href);
            if (category) {
                url.searchParams.set('category', category);
            } else {
                url.searchParams.delete('category');
            }
            url.searchParams.delete('page'); // reset pagination on filter change
            window.location.href = url.toString();
        });
    });

    /* ── Password visibility toggle ── */
    document.querySelectorAll('.input-group-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('input');
            if (!input) return;
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            this.innerHTML = isText
                ? `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                       stroke-linecap="round" stroke-linejoin="round">
                       <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                       <circle cx="12" cy="12" r="3"/>
                   </svg>`
                : `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                       stroke-linecap="round" stroke-linejoin="round">
                       <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                       <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                       <line x1="1" y1="1" x2="23" y2="23"/>
                   </svg>`;
        });
    });

    /* ── Client-side form validation helpers ── */
    document.querySelectorAll('form.validate-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            let valid = true;
            this.querySelectorAll('[required]').forEach(field => {
                const group    = field.closest('.form-group');
                const feedback = group ? group.querySelector('.invalid-feedback') : null;
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    if (feedback) feedback.classList.add('show');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                    if (feedback) feedback.classList.remove('show');
                }
            });
            if (!valid) e.preventDefault();
        });
    });

    /* ── Auto-dismiss alerts after 5 s ── */
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    /* ── Character counter for textarea ── */
    document.querySelectorAll('textarea[data-max]').forEach(ta => {
        const max     = parseInt(ta.dataset.max);
        const counter = document.createElement('div');
        counter.className = 'form-hint';
        counter.textContent = `0 / ${max}`;
        ta.parentNode.appendChild(counter);
        ta.addEventListener('input', () => {
            const len = ta.value.length;
            counter.textContent = `${len} / ${max}`;
            counter.style.color = len > max ? 'var(--error)' : '';
        });
    });

    /* ── Search: live debounce highlight ── */
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }

    /* ── Expiry date minimum (must be after published_at or now) ── */
    const publishedAt = document.getElementById('published_at');
    const expiresAt   = document.getElementById('expires_at');
    if (publishedAt && expiresAt) {
        publishedAt.addEventListener('change', function () {
            expiresAt.min = this.value;
        });
    }

    /* ── Confirm unpin/pin toggle text (cosmetic) ── */
    document.querySelectorAll('[data-pin-form]').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('form').submit();
        });
    });

});
