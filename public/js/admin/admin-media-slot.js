(() => {
    const cfgEl = document.getElementById('admin-tinymce-config');
    const mediaModalEl = document.getElementById('tinymce-media-modal');
    let mediaModalInst = null;
    let mediaTargetRow = null;

    if (!cfgEl || !mediaModalEl) {
        return;
    }

    let tinymceCfg = {};
    try {
        tinymceCfg = JSON.parse(cfgEl.textContent || '{}');
    } catch {
        tinymceCfg = {};
    }

    function mediaModal() {
        if (!mediaModalInst && mediaModalEl && window.bootstrap) {
            mediaModalInst = bootstrap.Modal.getOrCreateInstance(mediaModalEl);
        }

        return mediaModalInst;
    }

    function mediaJsonUrlForPage(page) {
        const url = new URL(tinymceCfg.mediaJsonUrl, window.location.origin);
        url.searchParams.set('page', String(page));

        return url.toString();
    }

    function renderMediaPicker(payload) {
        const grid = document.getElementById('tinymce-media-grid');
        const pager = document.getElementById('tinymce-media-pager');
        const empty = document.getElementById('tinymce-media-empty');
        if (!grid || !pager || !empty) {
            return;
        }
        const items = Array.isArray(payload.items) ? payload.items.filter((item) => item.exists !== false) : [];
        const images = items.filter((item) => String(item.mime || '').indexOf('image/') === 0);
        const page = Math.max(1, Number(payload.page) || 1);
        const totalPages = Math.max(1, Number(payload.totalPages) || 1);
        const total = Number(payload.total) || 0;
        grid.innerHTML = '';
        if (images.length === 0) {
            empty.textContent = total === 0 ? 'Aucune image dans la médiathèque.' : 'Aucune image sur cette page.';
            empty.classList.remove('d-none');
        } else {
            empty.classList.add('d-none');
            images.forEach((item) => {
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-light border w-100 p-2 cms-media-pick-result';
                btn.setAttribute('data-id', String(item.id || ''));
                btn.setAttribute('data-name', String(item.name || ''));
                btn.setAttribute('data-url', String(item.url || ''));
                const img = document.createElement('img');
                img.src = String(item.url || '');
                img.alt = String(item.name || '');
                img.className = 'img-fluid rounded w-100';
                img.loading = 'lazy';
                btn.appendChild(img);
                col.appendChild(btn);
                grid.appendChild(col);
            });
        }
        grid.onclick = (ev) => {
            const btn = ev.target.closest('.cms-media-pick-result');
            if (!btn || !mediaTargetRow) {
                return;
            }
            const mediaId = mediaTargetRow.querySelector('.cms-media-id-input');
            const mediaAlt = mediaTargetRow.querySelector('.cms-media-alt-input');
            if (mediaId) {
                mediaId.value = btn.getAttribute('data-id') || '';
            }
            if (mediaAlt && mediaAlt.value.trim() === '') {
                mediaAlt.value = btn.getAttribute('data-name') || '';
            }
            const preview = mediaTargetRow.querySelector('.cms-media-preview');
            const previewImg = preview ? preview.querySelector('img') : null;
            if (preview && previewImg) {
                const selectedUrl = btn.getAttribute('data-url') || '';
                previewImg.src = selectedUrl;
                preview.classList.toggle('d-none', selectedUrl === '');
            }
            const modal = mediaModal();
            if (modal) {
                modal.hide();
            }
        };
        pager.innerHTML = '';
        if (total > 0) {
            pager.classList.remove('d-none');
            pager.classList.add('d-flex');
            const meta = document.createElement('span');
            meta.className = 'text-muted';
            meta.textContent = totalPages > 1 ? 'Page ' + page + ' / ' + totalPages + ' · ' + total + ' fichier(s)' : total + ' fichier(s)';
            pager.appendChild(meta);
            if (totalPages > 1) {
                const buttons = document.createElement('span');
                buttons.className = 'd-inline-flex gap-2';
                const prev = document.createElement('button');
                prev.type = 'button';
                prev.className = 'btn btn-sm btn-outline-secondary';
                prev.textContent = 'Précédent';
                prev.disabled = page <= 1;
                prev.addEventListener('click', () => loadMediaPicker(page - 1));
                const next = document.createElement('button');
                next.type = 'button';
                next.className = 'btn btn-sm btn-outline-secondary';
                next.textContent = 'Suivant';
                next.disabled = page >= totalPages;
                next.addEventListener('click', () => loadMediaPicker(page + 1));
                buttons.appendChild(prev);
                buttons.appendChild(next);
                pager.appendChild(buttons);
            }
        } else {
            pager.classList.add('d-none');
            pager.classList.remove('d-flex');
        }
    }

    function loadMediaPicker(page) {
        const grid = document.getElementById('tinymce-media-grid');
        const loading = document.getElementById('tinymce-media-loading');
        const empty = document.getElementById('tinymce-media-empty');
        if (!tinymceCfg.mediaJsonUrl || !grid || !loading || !empty) {
            return;
        }
        grid.innerHTML = '';
        loading.classList.remove('d-none');
        empty.classList.add('d-none');
        fetch(mediaJsonUrlForPage(page), { credentials: 'same-origin' })
            .then((response) => response.json())
            .then((payload) => {
                loading.classList.add('d-none');
                renderMediaPicker(payload || {});
            })
            .catch(() => {
                loading.classList.add('d-none');
                empty.textContent = 'Impossible de charger la liste des médias.';
                empty.classList.remove('d-none');
            });
    }

    function bindMediaPickers(scope) {
        scope.querySelectorAll('.cms-pick-media').forEach((btn) => {
            if (btn.dataset.cmsMediaBound === '1') {
                return;
            }
            btn.dataset.cmsMediaBound = '1';
            btn.addEventListener('click', () => {
                mediaTargetRow = btn.closest('.cms-media-slot');
                const modal = mediaModal();
                if (!modal || !mediaTargetRow) {
                    return;
                }
                modal.show();
                loadMediaPicker(1);
            });
        });
        scope.querySelectorAll('.cms-clear-media').forEach((btn) => {
            if (btn.dataset.cmsMediaBound === '1') {
                return;
            }
            btn.dataset.cmsMediaBound = '1';
            btn.addEventListener('click', () => {
                const slot = btn.closest('.cms-media-slot');
                if (!slot) {
                    return;
                }
                const mediaId = slot.querySelector('.cms-media-id-input');
                const mediaAlt = slot.querySelector('.cms-media-alt-input');
                const preview = slot.querySelector('.cms-media-preview');
                const previewImg = preview ? preview.querySelector('img') : null;
                if (mediaId) {
                    mediaId.value = '';
                }
                if (mediaAlt) {
                    mediaAlt.value = '';
                }
                if (previewImg) {
                    previewImg.removeAttribute('src');
                }
                if (preview) {
                    preview.classList.add('d-none');
                }
            });
        });
    }

    bindMediaPickers(document);
})();
