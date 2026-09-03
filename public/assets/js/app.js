// Trofeu Hub — UI helpers
document.querySelectorAll('dialog.modal').forEach(dialog => {
    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) dialog.close();
    });
});

const navToggle = document.getElementById('nav-toggle');
const mainNav = document.getElementById('main-nav');
if (navToggle && mainNav) {
    navToggle.addEventListener('click', () => {
        const open = mainNav.classList.toggle('is-open');
        navToggle.classList.toggle('is-open', open);
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    mainNav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            mainNav.classList.remove('is-open');
            navToggle.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', 'false');
        });
    });
}

/* Admin mobile sidebar drawer */
(function () {
    const shell = document.getElementById('admin-shell');
    const menuBtn = document.getElementById('admin-menu-btn');
    const closeBtn = document.getElementById('admin-sidebar-close');
    const backdrop = document.getElementById('admin-sidebar-backdrop');
    if (!shell || !menuBtn) return;

    const setOpen = (open) => {
        shell.classList.toggle('sidebar-open', open);
        document.body.classList.toggle('admin-nav-open', open);
        if (backdrop) backdrop.hidden = !open;
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    menuBtn.addEventListener('click', () => setOpen(!shell.classList.contains('sidebar-open')));
    closeBtn?.addEventListener('click', () => setOpen(false));
    backdrop?.addEventListener('click', () => setOpen(false));
    document.getElementById('admin-sidebar')?.querySelectorAll('a').forEach((a) => {
        a.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 960px)').matches) setOpen(false);
        });
    });
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });
})();
