/**
 * Bengal IT Hub — Admin panel JS.
 * Small utility behaviours: confirm-before-delete, auto-hide flash alerts,
 * and slug preview while typing a title.
 */

(function () {
    'use strict';

    // Confirm destructive actions
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', function (e) {
            const msg = form.dataset.confirm || 'Are you sure?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide flash alerts after 5s
    document.querySelectorAll('.alert[data-autohide]').forEach((el) => {
        setTimeout(() => {
            el.style.transition = 'opacity 400ms ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });

    // Live slug preview
    document.querySelectorAll('[data-slug-source]').forEach((input) => {
        const targetSel = input.dataset.slugSource;
        const target = document.querySelector(targetSel);
        if (!target) return;
        input.addEventListener('input', () => {
            if (target.dataset.userEdited === '1') return;
            target.value = input.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        });
        target.addEventListener('input', () => { target.dataset.userEdited = '1'; });
    });
})();
