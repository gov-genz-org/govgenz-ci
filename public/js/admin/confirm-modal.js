(() => {
    const modalEl = document.getElementById('adminConfirmModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }

    document.querySelectorAll('form.js-confirm-submit').forEach((form) => {
        form.addEventListener('submit', (ev) => {
            if (form.getAttribute('data-skip-confirm') === 'yes') {
                form.removeAttribute('data-skip-confirm');
                return;
            }
            ev.preventDefault();
            const msg = form.getAttribute('data-confirm-message') || 'Confirmer cette action ?';
            const body = modalEl.querySelector('[data-confirm-body]');
            if (body) {
                body.textContent = msg;
            }
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const yesBtn = modalEl.querySelector('[data-confirm-yes]');
            const clone = yesBtn.cloneNode(true);
            yesBtn.parentNode.replaceChild(clone, yesBtn);
            clone.addEventListener('click', () => {
                modal.hide();
                form.setAttribute('data-skip-confirm', 'yes');
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
            modal.show();
        });
    });
})();
