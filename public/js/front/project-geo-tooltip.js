(() => {
    document.addEventListener(
        'click',
        (e) => {
            var info = e.target.closest('.project-geo__info');
            if (!info) {
                return;
            }
            if (info.closest('a.projects-program-page__card')) {
                e.preventDefault();
                e.stopPropagation();
            }
        },
        true
    );
})();
