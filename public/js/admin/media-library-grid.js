(() => {
    const search = document.getElementById('media-search');
    const grid = document.getElementById('media-library-grid');
    if (search && grid) {
        search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            const items = grid.querySelectorAll('.js-media-item');
            items.forEach((el) => {
                const hay = (el.getAttribute('data-filter') || '').toLowerCase();
                el.classList.toggle('d-none', q !== '' && hay.indexOf(q) === -1);
            });
        });
    }

    document.addEventListener('click', (ev) => {
        const btn = ev.target.closest('.js-copy-media-url');
        if (!btn) {
            return;
        }
        const url = btn.getAttribute('data-url') || '';
        if (!url || !navigator.clipboard) {
            window.prompt('Copiez cette URL :', url);
            return;
        }
        navigator.clipboard.writeText(url).then(() => {
            const prev = btn.textContent;
            btn.textContent = 'Copié !';
            btn.disabled = true;
            setTimeout(() => {
                btn.textContent = prev;
                btn.disabled = false;
            }, 1600);
        });
    });
})();
