/**
 * Sibiri Innovation — main site JS
 * Handles: dark/light theme toggle (animated, persisted), sticky/compact
 * header, scroll-spy nav highlighting, mobile drawer, scroll-reveal
 * animations, animated stat counters, FAQ accordion, and the contact form
 * (honeypot + reCAPTCHA v3 submission).
 */

(function () {
    'use strict';

    /* ---------------- Theme toggle ---------------- */
    const root = document.documentElement;
    const THEME_KEY = 'sibiri-theme';

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
        hamburger.addEventListener('click', () => mobileNav.classList.toggle('open'));
        mobileNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => mobileNav.classList.remove('open'));
        });
        const closeBtn = mobileNav.querySelector('[data-close-nav]');
        if (closeBtn) closeBtn.addEventListener('click', () => mobileNav.classList.remove('open'));
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

    /* ---------------- Scroll-reveal ---------------- */
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const reveal = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealEls.forEach((el) => reveal.observe(el));
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
