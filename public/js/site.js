/**
 * Bengal IT Hub — main site JS
 * Handles: dark/light theme toggle (animated, persisted), sticky/compact
 * header, scroll-spy nav highlighting, mobile drawer, scroll-reveal
 * animations, animated stat counters, FAQ accordion, and the contact form
 * (honeypot + reCAPTCHA v3 submission).
 */

(function () {
    'use strict';

    /* ---------------- Theme toggle ---------------- */
    const root = document.documentElement;
    const THEME_KEY = 'bith-theme';

    function applyTheme(theme) {
        if (theme === 'dark') {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.setAttribute('aria-pressed', theme === 'dark');
        });
    }

    function initTheme() {
        const stored = localStorage.getItem(THEME_KEY);
        if (stored) {
            applyTheme(stored);
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(prefersDark ? 'dark' : 'light');
        }
    }

    function toggleTheme() {
        const isDark = root.classList.contains('dark');
        const next = isDark ? 'light' : 'dark';
        applyTheme(next);
        localStorage.setItem(THEME_KEY, next);
    }

    initTheme();
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', toggleTheme);
    });

    /* ---------------- Sticky/compact header ---------------- */
    const header = document.querySelector('.site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('is-compact', window.scrollY > 80);
        }, { passive: true });
    }

    /* ---------------- Mobile nav drawer ---------------- */
    const hamburger = document.querySelector('[data-hamburger]');
    const mobileNav = document.querySelector('.mobile-nav');
    if (hamburger && mobileNav) {
        const setMobileNav = (open) => {
            mobileNav.classList.toggle('open', open);
            document.body.classList.toggle('mobile-nav-open', open);
            hamburger.setAttribute('aria-expanded', String(open));
        };
        hamburger.addEventListener('click', () => setMobileNav(!mobileNav.classList.contains('open')));
        mobileNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMobileNav(false));
        });
        const closeBtn = mobileNav.querySelector('[data-close-nav]');
        if (closeBtn) closeBtn.addEventListener('click', () => setMobileNav(false));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') setMobileNav(false);
        });
    }

    /* ---------------- Scroll-spy nav highlight ---------------- */
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.main-nav a[href*="#"]');
    if (sections.length && navLinks.length && 'IntersectionObserver' in window) {
        const spy = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    navLinks.forEach((l) => l.classList.remove('active'));
                    const match = document.querySelector(`.main-nav a[href$="#${entry.target.id}"]`);
                    if (match) match.classList.add('active');
                }
            });
        }, { rootMargin: '-40% 0px -50% 0px' });
        sections.forEach((s) => spy.observe(s));
    }

    /* ---------------- Scroll-reveal ----------------
       Generous rootMargin + threshold:0 so fast/jerky scrolling on long
       pages can't skip an element between intersection checks, plus a
       debounced fallback sweep that force-reveals anything still missed
       (e.g. very fast programmatic scrolls, reduced-motion edge cases) so
       content never gets permanently stuck invisible. */
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length) {
        if ('IntersectionObserver' in window) {
            const reveal = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0, rootMargin: '200px 0px 200px 0px' });
            revealEls.forEach((el) => reveal.observe(el));
        } else {
            revealEls.forEach((el) => el.classList.add('in-view'));
        }

        // Safety-net sweep: catches anything the observer missed (rapid
        // scroll, tab thrown into background then restored, etc). Runs on
        // every animation frame while scrolling (not just once scrolling
        // stops) so nothing can be scrolled past too fast to be caught.
        function sweepReveal() {
            const vh = window.innerHeight;
            revealEls.forEach((el) => {
                if (el.classList.contains('in-view')) return;
                const r = el.getBoundingClientRect();
                if (r.top < vh + 300 && r.bottom > -300) el.classList.add('in-view');
            });
        }
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => { sweepReveal(); ticking = false; });
                ticking = true;
            }
        }, { passive: true });
        window.addEventListener('load', sweepReveal);
        setTimeout(sweepReveal, 800);
        setTimeout(sweepReveal, 2000);
    }

    /* ---------------- Animated stat counters ---------------- */
    document.querySelectorAll('[data-counter]').forEach((el) => {
        const raw = el.textContent.trim();
        const numMatch = raw.match(/\d+/);
        if (!numMatch) return;
        const target = parseInt(numMatch[0], 10);
        const suffix = raw.replace(/^\d+/, '');
        let started = false;

        const obs = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && !started) {
                    started = true;
                    let current = 0;
                    const step = Math.max(1, Math.ceil(target / 40));
                    const interval = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(interval);
                        }
                        el.textContent = current + suffix;
                    }, 30);
                }
            });
        }, { threshold: 0.5 });
        obs.observe(el);
    });

    /* ---------------- FAQ accordion ---------------- */
    document.querySelectorAll('.faq-item .faq-question').forEach((q) => {
        q.addEventListener('click', () => {
            const item = q.closest('.faq-item');
            const wasOpen = item.classList.contains('open');
            item.parentElement.querySelectorAll('.faq-item').forEach((i) => i.classList.remove('open'));
            if (!wasOpen) item.classList.add('open');
        });
    });

    /* ---------------- Cursor glow (desktop only) ---------------- */
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        const glow = document.createElement('div');
        glow.className = 'cursor-glow';
        document.body.appendChild(glow);
        let gx = 0, gy = 0, cx = 0, cy = 0;
        window.addEventListener('mousemove', (e) => {
            gx = e.clientX; gy = e.clientY;
            glow.classList.add('active');
        }, { passive: true });
        (function loop() {
            cx += (gx - cx) * 0.12;
            cy += (gy - cy) * 0.12;
            glow.style.transform = `translate(${cx}px, ${cy}px) translate(-50%, -50%)`;
            requestAnimationFrame(loop);
        })();
    }

    /* ---------------- Magnetic buttons ---------------- */
    document.querySelectorAll('.btn-primary, .btn-gold').forEach((btn) => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = `translate(${x * 0.18}px, ${y * 0.3}px)`;
        });
        btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
    });

    /* ---------------- Bento / hero card tilt ---------------- */
    document.querySelectorAll('.bento-card, .hero-visual .frame').forEach((card) => {
        const restTransform = card.classList.contains('frame') ? 'rotate(2deg)' : '';
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width - 0.5;
            const py = (e.clientY - rect.top) / rect.height - 0.5;
            card.style.transform = `perspective(800px) rotateX(${py * -6}deg) rotateY(${px * 6}deg) translateY(-2px)`;
        });
        card.addEventListener('mouseleave', () => { card.style.transform = restTransform; });
    });

    /* ---------------- Contact form (honeypot + reCAPTCHA v3) ---------------- */
    const contactForm = document.querySelector('#contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            const siteKey = contactForm.dataset.recaptchaSiteKey;
            if (!siteKey || typeof grecaptcha === 'undefined') {
                return; // no reCAPTCHA configured yet — submit normally, server-side check handles it
            }
            e.preventDefault();
            const submitBtn = contactForm.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            grecaptcha.ready(function () {
                grecaptcha.execute(siteKey, { action: 'contact_form' }).then(function (token) {
                    document.getElementById('recaptcha_token').value = token;
                    contactForm.submit();
                });
            });
        });
    }
})();

/* ---------------- Mega menu / dropdown nav ---------------- */
(function () {
    'use strict';
    const navItems = document.querySelectorAll('.nav-item[data-has-panel]');

    function closeAll(except) {
        navItems.forEach((item) => { if (item !== except) item.classList.remove('open'); });
    }

    navItems.forEach((item) => {
        const trigger = item.querySelector(':scope > button, :scope > a');
        if (!trigger) return;
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const isOpen = item.classList.contains('open');
            closeAll();
            if (!isOpen) item.classList.add('open');
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.nav-item[data-has-panel]')) closeAll();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });

    /* Mobile accordion groups */
    document.querySelectorAll('.nav-group-mobile > button').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('.nav-group-mobile').classList.toggle('open');
        });
    });

    /* Lead-gen radio card selection (Let's Talk form) */
    document.querySelectorAll('.lead-option').forEach((input) => {
        input.addEventListener('change', () => {
            document.querySelectorAll('.lead-option-label').forEach((l) => l.style.transform = '');
        });
    });

    /* Filter pills (visual only in this demo) */
    document.querySelectorAll('.filter-row').forEach((row) => {
        row.querySelectorAll('.filter-pill').forEach((pill) => {
            pill.addEventListener('click', () => {
                row.querySelectorAll('.filter-pill').forEach((p) => p.classList.remove('active'));
                pill.classList.add('active');
            });
        });
    });
})();

/* ---------------- Ecosystem tab switcher (homepage only) ---------------- */
(function () {
    'use strict';
    const tabs = document.querySelectorAll('[data-eco-tab]');
    if (!tabs.length) return;
    tabs.forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.ecoTab;
            tabs.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('[data-eco-panel]').forEach((panel) => {
                panel.classList.toggle('active', panel.dataset.ecoPanel === target);
            });
        });
    });
})();
