(() => {
    const toggle = document.querySelector('.ggz-nav-toggle');
    const nav = document.querySelector('#ggz-site-nav');
    if (!toggle || !nav) {
        return;
    }

    const mq = window.matchMedia('(min-width: 768px)');

    function closeNav() {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', () => {
        const open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    mq.addEventListener('change', () => {
        if (mq.matches) {
            closeNav();
        }
    });

    nav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (!mq.matches) {
                closeNav();
            }
        });
    });
})();
