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
    const THEME_KEY = 'cso-theme';

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

    /* ---------------- Profile menu hover ---------------- */
    if (window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('details.auth-portal').forEach((portal) => {
            let closeTimer;
            portal.addEventListener('mouseenter', () => {
                clearTimeout(closeTimer);
                portal.open = true;
            });
            portal.addEventListener('mouseleave', () => {
                closeTimer = setTimeout(() => { portal.open = false; }, 120);
            });
        });
    }

    /* ---------------- Search suggestions ---------------- */
    document.querySelectorAll('[data-search-form]').forEach((form) => {
        const input = form.querySelector('input[name="q"]');
        const category = form.querySelector('[data-search-category]');
        const panel = form.querySelector('[data-search-suggestions]');
        let timer;

        async function loadSuggestions() {
            clearTimeout(timer);
            if (input.value.trim().length < 2) {
                panel.hidden = true;
                return;
            }
            timer = setTimeout(async () => {
                const url = new URL(form.dataset.suggestionsUrl, window.location.origin);
                url.searchParams.set('q', input.value.trim());
                if (category.value) url.searchParams.set('category', category.value);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const books = await response.json();
                panel.replaceChildren();
                books.forEach((book) => {
                    const link = document.createElement('a');
                    link.href = book.url;
                    if (book.cover) {
                        const image = document.createElement('img');
                        image.src = book.cover;
                        image.alt = '';
                        link.append(image);
                    }
                    const text = document.createElement('span');
                    const title = document.createElement('strong');
                    const meta = document.createElement('small');
                    title.textContent = book.title;
                    meta.textContent = book.meta;
                    text.append(title, meta);
                    link.append(text);
                    panel.append(link);
                });
                if (!books.length) panel.textContent = 'No matching books found';
                panel.hidden = false;
            }, 250);
        }

        input.addEventListener('input', loadSuggestions);
        category.addEventListener('change', loadSuggestions);
        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) panel.hidden = true;
        });
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

    /* ---------------- Customer sidebar drawer ---------------- */
    const custTriggers = document.querySelectorAll('[data-customer-sidebar-toggle]');
    const custSidebar = document.querySelector('[data-customer-sidebar]');
    const custOverlay = document.querySelector('.customer-sidebar-overlay');
    const custCloseBtns = document.querySelectorAll('[data-customer-sidebar-close]');

    if (custSidebar) {
        const toggleCustSidebar = (open) => {
            custSidebar.classList.toggle('open', open);
            if (custOverlay) custOverlay.classList.toggle('open', open);
            document.body.classList.toggle('customer-sidebar-open', open);
        };

        custTriggers.forEach((btn) => {
            btn.addEventListener('click', () => {
                toggleCustSidebar(!custSidebar.classList.contains('open'));
            });
        });

        custSidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => toggleCustSidebar(false));
        });

        custCloseBtns.forEach((btn) => {
            btn.addEventListener('click', () => toggleCustSidebar(false));
        });

        if (custOverlay) {
            custOverlay.addEventListener('click', () => toggleCustSidebar(false));
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') toggleCustSidebar(false);
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

    /* ---------------- Scroll-reveal ---------------- */
    function initScrollReveal() {
        const revealEls = document.querySelectorAll('.reveal');
        if (!revealEls.length) return;

        if ('IntersectionObserver' in window) {
            const reveal = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0, rootMargin: '200px 0px 200px 0px' });
            revealEls.forEach((el) => {
                if (!el.classList.contains('in-view')) {
                    reveal.observe(el);
                }
            });
        } else {
            revealEls.forEach((el) => el.classList.add('in-view'));
        }

        function sweepReveal() {
            const vh = window.innerHeight;
            document.querySelectorAll('.reveal:not(.in-view)').forEach((el) => {
                const r = el.getBoundingClientRect();
                if (r.top < vh + 300 && r.bottom > -300) el.classList.add('in-view');
            });
        }

        sweepReveal();
        setTimeout(sweepReveal, 100);
        setTimeout(sweepReveal, 400);
    }
    window.csoInitScrollReveal = initScrollReveal;
    initScrollReveal();
    window.addEventListener('scroll', () => {
        window.csoInitScrollReveal && window.csoInitScrollReveal();
    }, { passive: true });

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

/* ---------------- Instant Seamless SPA Page Navigation ---------------- */
(function () {
    'use strict';
    
    // Create top progress bar element
    const bar = document.createElement('div');
    bar.id = 'cso-pjax-bar';
    bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--accent-gold,#c59b27);box-shadow:0 0 10px var(--accent-gold,#c59b27);z-index:99999;width:0%;opacity:0;transition:width 0.2s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;pointer-events:none;';
    document.body.appendChild(bar);

    function startProgress() {
        bar.style.opacity = '1';
        bar.style.width = '35%';
        setTimeout(() => { if (bar.style.opacity === '1') bar.style.width = '75%'; }, 100);
    }

    function endProgress() {
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => { bar.style.width = '0%'; }, 200);
        }, 100);
    }

    async function navigateTo(url, push = true) {
        startProgress();
        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) {
                window.location.href = url;
                return;
            }
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newMain = doc.querySelector('main');
            const currentMain = document.querySelector('main');
            if (!newMain || !currentMain) {
                window.location.href = url;
                return;
            }

            // Update title
            document.title = doc.title || document.title;

            // Update active navbar states
            const currentPath = new URL(url, window.location.origin).pathname;
            document.querySelectorAll('.cso-nav-item').forEach((item) => {
                const itemHref = item.getAttribute('href');
                if (!itemHref) return;
                const itemPath = new URL(itemHref, window.location.origin).pathname;
                const isMatch = (itemPath === '/' && currentPath === '/') || (itemPath !== '/' && currentPath.startsWith(itemPath));
                item.classList.toggle('active', isMatch);
            });

            // Smoothly swap main content
            currentMain.replaceWith(newMain);
            window.scrollTo({ top: 0, behavior: 'instant' });

            if (window.csoInitScrollReveal) {
                window.csoInitScrollReveal();
            }

            if (push) {
                history.pushState({ url }, '', url);
            }

            // Close mobile menu & customer profile drawer if open
            document.querySelector('.mobile-nav')?.classList.remove('open');
            document.querySelector('[data-customer-sidebar]')?.classList.remove('open');
            document.querySelector('.customer-sidebar-overlay')?.classList.remove('open');
            document.body.classList.remove('customer-sidebar-open', 'mobile-nav-open');

            endProgress();
        } catch (err) {
            window.location.href = url;
        }
    }

    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a');
        if (!anchor) return;
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || anchor.target || anchor.hasAttribute('download')) return;

        try {
            const urlObj = new URL(anchor.href, window.location.origin);
            if (urlObj.origin !== window.location.origin) return;

            const pathname = urlObj.pathname.toLowerCase();
            // Bypass SPA for Admin/Publisher portals and documents that use separate dedicated layouts/stylesheets
            if (
                pathname.startsWith('/admin') ||
                pathname.startsWith('/publisher') ||
                pathname.includes('/invoice') ||
                pathname.includes('/download')
            ) {
                return;
            }

            // Avoid intercepting forms, auth submit, or profile drawer toggles
            if (anchor.closest('form') || anchor.hasAttribute('data-no-pjax') || anchor.hasAttribute('data-customer-sidebar-toggle')) return;

            e.preventDefault();
            if (window.location.href !== urlObj.href) {
                navigateTo(urlObj.href);
            }
        } catch (err) {}
    });

    window.addEventListener('popstate', () => {
        navigateTo(window.location.href, false);
    });
})();


