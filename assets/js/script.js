document.addEventListener('DOMContentLoaded', function () {
    highlightSidebarLink();
    highlightNavbarLink();        // NEW – for home page
    setupPasswordToggles();
    setupDeleteConfirmations();
    setupAlertDismiss();
    setupFormValidations();
    setupNavbarScroll();
    setupHamburgerMenu();
    setupBackToTop();
    setupCounters();
    setupScrollAnimations();
});

/* -------------------------------------------------------
   1. SIDEBAR ACTIVE LINK (dashboard)
   ------------------------------------------------------- */
function highlightSidebarLink() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar .sidebar-nav a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) link.classList.add('active');
    });
}

/* -------------------------------------------------------
   2. HOME PAGE NAVBAR – Scroll Spy
   ------------------------------------------------------- */
function highlightNavbarLink() {
    const sections = document.querySelectorAll('section[id], footer[id]'); // include footer with id
    const navLinks = document.querySelectorAll('.navbar .nav-links a');
    if (!sections.length || !navLinks.length) return;

    window.addEventListener('scroll', () => {
        const scrollY = window.scrollY + 120;
        const docHeight = document.documentElement.scrollHeight;
        const viewHeight = window.innerHeight;
        let current = '';

        // Check if we are at the very bottom of the page → force 'contact'
        if (window.scrollY + viewHeight >= docHeight - 5) {
            current = 'contact';
        } else {
            // Otherwise find the section currently in view
            sections.forEach(section => {
                const top = section.offsetTop;
                const height = section.offsetHeight;
                if (scrollY >= top && scrollY < top + height) {
                    current = section.getAttribute('id');
                }
            });
        }

        // Update active class on matching nav link
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            const isActive = (href === '#' + current) ||
                             (href === 'index.php' && current === 'home');
            if (isActive) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    });
}

/* -------------------------------------------------------
   3. PASSWORD SHOW / HIDE
   ------------------------------------------------------- */
function setupPasswordToggles() {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.getAttribute('data-target'));
            if (!target) return;
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            this.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    });
}

/* -------------------------------------------------------
   4. DELETE CONFIRMATION
   ------------------------------------------------------- */
function setupDeleteConfirmations() {
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) e.preventDefault();
        });
    });
}

/* -------------------------------------------------------
   5. AUTO‑DISMISS ALERTS
   ------------------------------------------------------- */
function setupAlertDismiss() {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 500); }, 5000);
    });
}

/* -------------------------------------------------------
   6. FORM VALIDATION
   ------------------------------------------------------- */
function setupFormValidations() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', event => { if (!form.checkValidity()) { event.preventDefault(); event.stopPropagation(); } });
    });
}

/* -------------------------------------------------------
   7. NAVBAR SCROLL EFFECT
   ------------------------------------------------------- */
function setupNavbarScroll() {
    const navbar = document.getElementById('navbar');
    if (!navbar) return;
    window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 50));
}

/* -------------------------------------------------------
   8. HAMBURGER MENU (mobile)
   ------------------------------------------------------- */
function setupHamburgerMenu() {
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');
    if (!hamburger || !navLinks) return;
    hamburger.addEventListener('click', () => navLinks.classList.toggle('open'));
}

/* -------------------------------------------------------
   9. BACK‑TO‑TOP BUTTON
   ------------------------------------------------------- */
function setupBackToTop() {
    const btn = document.getElementById('backToTop');
    if (!btn) return;
    window.addEventListener('scroll', () => btn.classList.toggle('visible', window.scrollY > 500));
    btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* -------------------------------------------------------
   10. ANIMATED COUNTERS
   ------------------------------------------------------- */
function setupCounters() {
    document.querySelectorAll('.counter').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const interval = setInterval(() => {
                        current += step;
                        if (current >= target) { counter.textContent = target; clearInterval(interval); }
                        else counter.textContent = Math.floor(current);
                    }, 16);
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });
        observer.observe(counter);
    });
}

/* -------------------------------------------------------
   11. SCROLL ANIMATIONS
   ------------------------------------------------------- */
function setupScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
    }, { threshold: 0.15 });
    document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
}