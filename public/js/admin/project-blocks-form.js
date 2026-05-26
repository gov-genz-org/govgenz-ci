(() => {
    const htmlRadio = document.querySelector('input[name="body_content_mode"][value="html"]');
    const blocksRadio = document.querySelector('input[name="body_content_mode"][value="blocks"]');
    const hiddenMode = document.querySelector('input[type="hidden"][name="body_content_mode"]');
    const htmlPanel = document.getElementById('pp-html-panel');
    const blocksPanel = document.getElementById('pp-blocks-panel');
    const container = document.getElementById('pp-blocks-container');
    const protoRoot = document.getElementById('pp-proto-store');

    if (!blocksPanel || !container || !protoRoot) {
        return;
    }

    function isBlocksMode() {
        if (blocksRadio) {
            return blocksRadio.checked;
        }
        if (htmlRadio) {
            return !htmlRadio.checked;
        }
        if (hiddenMode) {
            return hiddenMode.value === 'blocks';
        }

        return !blocksPanel.classList.contains('d-none');
    }

    function syncPanels() {
        if (!htmlPanel || (!htmlRadio && !blocksRadio)) {
            if (htmlPanel) {
                htmlPanel.classList.add('d-none');
            }
            blocksPanel.classList.remove('d-none');

            return;
        }
        var blocks = blocksRadio.checked;
        if (blocks && typeof tinymce !== 'undefined') {
            var ppEd = tinymce.get('pp-body');
            if (ppEd) {
                ppEd.save();
            }
        }
        htmlPanel.classList.toggle('d-none', blocks);
        blocksPanel.classList.toggle('d-none', !blocks);
        if (!blocks && typeof window.adminEnsureTinyMce === 'function') {
            window.adminEnsureTinyMce('#pp-body');
            var ppTa = document.getElementById('pp-body');
            var ppEd = typeof tinymce !== 'undefined' ? tinymce.get('pp-body') : null;
            if (ppEd && ppTa && ppTa.value && !ppEd.getContent({ format: 'text' }).trim()) {
                ppEd.setContent(ppTa.value);
            }
        }
    }

    /**
     * @param {HTMLElement} root
     */
    function reindexBlocks(root) {
        root.querySelectorAll(':scope > .project-block-row').forEach((row, idx) => {
            row.querySelectorAll('[name^="blocks["]').forEach((el) => {
                el.name = el.name.replace(/^blocks\[[^\]]+\]/, `blocks[${idx}]`);
            });
        });
    }

    /**
     * @param {HTMLElement} pointer
     * @returns {HTMLElement|null}
     */
    function getDragAfterElement(pointer) {
        var rows = Array.from(container.querySelectorAll(':scope > .project-block-row:not(.is-dragging)'));
        var closest = { offset: Number.NEGATIVE_INFINITY, element: null };

        rows.forEach((row) => {
            var box = row.getBoundingClientRect();
            var offset = pointer.clientY - box.top - box.height / 2;
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
        if (row.dataset.ppDragBound === '1') {
            return;
        }
        row.dataset.ppDragBound = '1';
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', (ev) => {
            if (ev.target && ev.target.closest && !ev.target.closest('.project-block-drag-handle')) {
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
        row.querySelectorAll('.project-block-remove').forEach((btn) => {
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
        const wrap = protoRoot.querySelector(`[data-pp-proto="${kind}"]`);
        if (!wrap || !wrap.firstElementChild) {
            return;
        }
        var html = wrap.innerHTML;
        var idx = container.querySelectorAll(':scope > .project-block-row').length;
        html = html.split('__I__').join(String(idx));
        var div = document.createElement('div');
        div.innerHTML = html.trim();
        var node = div.firstElementChild;
        if (!node) {
            return;
        }
        container.appendChild(node);
        reindexBlocks(container);
        bindRemove(node);
        bindDrag(node);
    }

    if (htmlRadio) {
        htmlRadio.addEventListener('change', syncPanels);
    }
    if (blocksRadio) {
        blocksRadio.addEventListener('change', syncPanels);
        blocksRadio.addEventListener('change', () => {
            if (blocksRadio.checked && container.querySelectorAll(':scope > .project-block-row').length === 0) {
                appendBlock('section_rich');
            }
        });
    }

    container.querySelectorAll(':scope > .project-block-row').forEach((row) => {
        bindRemove(row);
        bindDrag(row);
    });

    container.addEventListener('dragover', (ev) => {
        ev.preventDefault();
        var dragging = container.querySelector(':scope > .project-block-row.is-dragging');
        if (!dragging) {
            return;
        }
        container.classList.add('is-drag-over');
        var afterElement = getDragAfterElement(ev);
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

    document.querySelectorAll('[data-pp-add]').forEach((btn) => {
        btn.addEventListener('click', () => {
            var kind = btn.getAttribute('data-pp-add');
            if (kind) {
                appendBlock(kind);
            }
        });
    });

    syncPanels();

    if (isBlocksMode() && container.querySelectorAll(':scope > .project-block-row').length === 0) {
        appendBlock('section_rich');
    }
})();
