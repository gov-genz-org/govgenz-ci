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
