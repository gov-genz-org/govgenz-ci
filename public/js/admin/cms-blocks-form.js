(() => {
    const htmlRadio = document.querySelector('input[name="content_mode"][value="html"]');
    const blocksRadio = document.querySelector('input[name="content_mode"][value="blocks"]');
    const htmlPanel = document.getElementById('cms-html-panel');
    const blocksPanel = document.getElementById('cms-blocks-panel');
    const container = document.getElementById('cms-blocks-container');
    const protoRoot = document.getElementById('cms-proto-store');
    const tinymceCfg = readTinyMceConfig();
    const mediaModalEl = document.getElementById('tinymce-media-modal');
    let mediaModalInst = null;
    let mediaTargetRow = null;

    if (!htmlRadio || !blocksRadio || !htmlPanel || !blocksPanel || !container || !protoRoot) {
        return;
    }

    function readTinyMceConfig() {
        const cfgEl = document.getElementById('admin-tinymce-config');
        if (!cfgEl) {
            return {};
        }
        try {
            return JSON.parse(cfgEl.textContent || '{}');
        } catch {
            return {};
        }
    }

    function syncPanels() {
        const blocks = blocksRadio.checked;
        if (blocks && typeof tinymce !== 'undefined') {
            const pageEd = tinymce.get('body_html');
            if (pageEd) {
                pageEd.save();
            }
        }
        htmlPanel.classList.toggle('d-none', blocks);
        blocksPanel.classList.toggle('d-none', !blocks);
        if (!blocks && typeof window.adminEnsureTinyMce === 'function') {
            window.adminEnsureTinyMce('#body_html');
        }
    }

    /**
     * @param {HTMLElement} root
     */
    function reindexBlocks(root) {
        root.querySelectorAll(':scope > .cms-block-row').forEach((row, idx) => {
            row.querySelectorAll('[name^="blocks["]').forEach((el) => {
                el.name = el.name.replace(/^blocks\[[^\]]+\]/, 'blocks[' + idx + ']');
            });
            row.querySelectorAll('.cms-repeatable').forEach((wrap) => {
                reindexRepeatable(wrap);
            });
        });
    }

    /**
     * @param {HTMLElement} card
     * @returns {string|null}
     */
    function blockIndex(card) {
        const input = card.querySelector('.card-body input[type="hidden"][name^="blocks["][name$="[type]"]');
        if (!input || !input.name) {
            return null;
        }
        const match = input.name.match(/^blocks\[(\d+)\]/);

        return match ? match[1] : null;
    }

    /**
     * @param {HTMLElement} wrap
     */
    function reindexRepeatable(wrap) {
        const card = wrap.closest('.cms-block-row');
        const body = wrap.querySelector('.cms-repeat-body');
        const key = wrap.getAttribute('data-cms-repeat-key');
        if (!card || !body || !key) {
            return;
        }
        const idx = blockIndex(card);
        if (idx === null) {
            return;
        }
        const escapedKey = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const rowRe = new RegExp('^blocks\\[' + idx + '\\]\\[' + escapedKey + '\\]\\[\\d+\\]');
        body.querySelectorAll(':scope > .cms-repeat-row').forEach((row, ri) => {
            row.querySelectorAll('[name^="blocks["]').forEach((el) => {
                el.name = el.name.replace(rowRe, 'blocks[' + idx + '][' + key + '][' + ri + ']');
            });
        });
    }

    /**
     * @param {HTMLTemplateElement} template
     * @param {number} rowIndex
     * @returns {HTMLElement|null}
     */
    function cloneRepeatRow(template, rowIndex) {
        const clone = template.content.cloneNode(true);
        clone.querySelectorAll('[name]').forEach((el) => {
            if (el.name && el.name.indexOf('__RI__') !== -1) {
                el.name = el.name.replace(/__RI__/g, String(rowIndex));
            }
        });
        const row = clone.querySelector('.cms-repeat-row');
        if (row) {
            return row;
        }
        const first = clone.firstElementChild;
        if (first instanceof HTMLElement) {
            first.classList.add('cms-repeat-row');

            return first;
        }

        return null;
    }

    /**
     * @param {HTMLElement} wrap
     */
    function bindRepeatable(wrap) {
        wrap.querySelectorAll('.cms-repeat-add').forEach((btn) => {
            if (btn.dataset.cmsRepeatBound === '1') {
                return;
            }
            btn.dataset.cmsRepeatBound = '1';
            btn.addEventListener('click', () => {
                const body = wrap.querySelector('.cms-repeat-body');
                const template = wrap.querySelector('template.cms-repeat-template');
                if (!(template instanceof HTMLTemplateElement) || !body) {
                    return;
                }
                const rowIndex = body.querySelectorAll(':scope > .cms-repeat-row').length;
                const row = cloneRepeatRow(template, rowIndex);
                if (!row) {
                    return;
                }
                body.appendChild(row);
                bindRepeatable(wrap);
                bindMediaPickers(row);
                initFooterLinkSoon(row);
                reindexRepeatable(wrap);
                const blockRow = wrap.closest('.cms-block-row');
                if (blockRow) {
                    refreshCardsVariantFields(blockRow);
                }
            });
        });
        wrap.querySelectorAll('.cms-repeat-remove').forEach((btn) => {
            if (btn.dataset.cmsRepeatBound === '1') {
                return;
            }
            btn.dataset.cmsRepeatBound = '1';
            btn.addEventListener('click', () => {
                const row = btn.closest('.cms-repeat-row');
                if (row) {
                    row.remove();
                    reindexRepeatable(wrap);
                }
            });
        });
        reindexRepeatable(wrap);
    }

    /**
     * @param {HTMLElement} scope
     */
    function bindRepeatables(scope) {
        scope.querySelectorAll('.cms-repeatable').forEach((wrap) => {
            bindRepeatable(wrap);
        });
    }

    /**
     * @param {string} variant
     * @param {string} field
     * @returns {boolean}
     */
    function cardFieldIsVisible(variant, field) {
        if (variant === 'circle_cards') {
            return ['value', 'unit', 'title', 'subtitle', 'description', 'media', 'media2'].indexOf(field) !== -1;
        }
        if (variant === 'pillar_cards') {
            return ['eyebrow', 'value', 'title', 'bullets'].indexOf(field) !== -1;
        }
        if (variant === 'tile_grid') {
            return ['title', 'subtitle', 'href'].indexOf(field) !== -1;
        }

        return ['eyebrow', 'title', 'subtitle', 'description', 'bullets', 'href'].indexOf(field) !== -1;
    }

    /**
     * @param {HTMLElement} blockRow
     */
    function refreshCardsVariantFields(blockRow) {
        const variantInput = blockRow.querySelector('select[name^="blocks["][name$="[variant]"]');
        if (!(variantInput instanceof HTMLSelectElement)) {
            return;
        }
        const variant = String(variantInput.value || 'simple_cards');
        blockRow.querySelectorAll('.cms-repeat-row').forEach((cardRow) => {
            cardRow.querySelectorAll('[data-cms-card-field]').forEach((fieldNode) => {
                const field = fieldNode.getAttribute('data-cms-card-field') || '';
                const show = cardFieldIsVisible(variant, field);
                const target = fieldNode.classList.contains('cms-card-field-wrap') ? fieldNode : fieldNode.closest('.cms-card-field-wrap');
                if (target) {
                    target.classList.toggle('d-none', !show);
                }
            });
        });
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

    /**
     * @param {MouseEvent|DragEvent} pointer
     * @returns {HTMLElement|null}
     */
    function getDragAfterElement(pointer) {
        const rows = Array.from(container.querySelectorAll(':scope > .cms-block-row:not(.is-dragging)'));
        let closest = { offset: Number.NEGATIVE_INFINITY, element: null };

        rows.forEach((row) => {
            const box = row.getBoundingClientRect();
            const offset = pointer.clientY - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                closest = { offset: offset, element: row };
            }
        });

        return closest.element;
    }

    /**
     * @param {HTMLElement} row
     */
    function bindDrag(row) {
        if (row.dataset.cmsDragBound === '1') {
            return;
        }
        row.dataset.cmsDragBound = '1';
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', (ev) => {
            if (ev.target && ev.target.closest && !ev.target.closest('.cms-block-drag-handle')) {
                ev.preventDefault();
                return;
            }
            row.classList.add('is-dragging');
            if (ev.dataTransfer) {
                ev.dataTransfer.effectAllowed = 'move';
                ev.dataTransfer.setData('text/plain', '');
            }
        });

        row.addEventListener('dragend', () => {
            row.classList.remove('is-dragging');
            container.classList.remove('is-drag-over');
            reindexBlocks(container);
        });
    }

    /**
     * @param {HTMLElement} row
     */
    function bindRemove(row) {
        row.querySelectorAll('.cms-block-remove').forEach((btn) => {
            btn.addEventListener('click', () => {
                row.remove();
                reindexBlocks(container);
            });
        });
    }

    /**
     * @param {string} kind
     */
    function appendBlock(kind) {
        const wrap = protoRoot.querySelector('[data-cms-proto="' + kind + '"]');
        if (!wrap || !wrap.firstElementChild) {
            return;
        }
        let html = wrap.innerHTML;
        const idx = container.querySelectorAll(':scope > .cms-block-row').length;
        html = html.split('__I__').join(String(idx));
        const div = document.createElement('div');
        div.innerHTML = html.trim();
        const node = div.firstElementChild;
        if (!node) {
            return;
        }
        container.appendChild(node);
        reindexBlocks(container);
        bindRemove(node);
        bindDrag(node);
        bindRepeatables(node);
        bindMediaPickers(node);
        initFooterLinkSoon(node);
        const variantInput = node.querySelector('select[name^="blocks["][name$="[variant]"]');
        if (variantInput) {
            variantInput.addEventListener('change', () => {
                refreshCardsVariantFields(node);
            });
            refreshCardsVariantFields(node);
        }
    }

    /**
     * @param {HTMLInputElement} checkbox
     */
    function syncFooterLinkSoon(checkbox) {
        const row = checkbox.closest('.cms-repeat-row');
        if (!row) {
            return;
        }
        const hrefInput = row.querySelector('input[name*="[href]"]');
        if (!(hrefInput instanceof HTMLInputElement)) {
            return;
        }
        const soon = checkbox.checked;
        hrefInput.disabled = soon;
        if (soon) {
            hrefInput.value = '';
        }
    }

    container.addEventListener('change', (ev) => {
        const target = ev.target;
        if (target instanceof HTMLInputElement && target.classList.contains('js-footer-link-soon')) {
            syncFooterLinkSoon(target);
        }
    });

    function initFooterLinkSoon(scope) {
        scope.querySelectorAll('.js-footer-link-soon').forEach((el) => {
            if (el instanceof HTMLInputElement) {
                syncFooterLinkSoon(el);
            }
        });
    }

    htmlRadio.addEventListener('change', syncPanels);
    blocksRadio.addEventListener('change', syncPanels);

    container.querySelectorAll('.cms-block-row').forEach((row) => {
        bindRemove(row);
        bindDrag(row);
        bindRepeatables(row);
        bindMediaPickers(row);
        initFooterLinkSoon(row);
        const variantInput = row.querySelector('select[name^="blocks["][name$="[variant]"]');
        if (variantInput) {
            variantInput.addEventListener('change', () => {
                refreshCardsVariantFields(row);
            });
            refreshCardsVariantFields(row);
        }
    });

    container.addEventListener('dragover', (ev) => {
        ev.preventDefault();
        const dragging = container.querySelector(':scope > .cms-block-row.is-dragging');
        if (!dragging) {
            return;
        }
        container.classList.add('is-drag-over');
        const afterElement = getDragAfterElement(ev);
        if (afterElement === null) {
            container.appendChild(dragging);
        } else {
            container.insertBefore(dragging, afterElement);
        }
    });

    container.addEventListener('drop', (ev) => {
        ev.preventDefault();
        container.classList.remove('is-drag-over');
        reindexBlocks(container);
    });

    document.querySelectorAll('[data-cms-add]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const kind = btn.getAttribute('data-cms-add');
            if (kind) {
                appendBlock(kind);
            }
        });
    });

    blocksRadio.addEventListener('change', () => {
        if (blocksRadio.checked && container.querySelectorAll(':scope > .cms-block-row').length === 0) {
            appendBlock('section_text');
        }
    });

    syncPanels();

    if (blocksRadio.checked && container.querySelectorAll(':scope > .cms-block-row').length === 0) {
        appendBlock('section_text');
    }
})();
