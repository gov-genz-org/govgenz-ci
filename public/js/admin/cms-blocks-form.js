(() => {
    const htmlRadio = document.querySelector('input[name="content_mode"][value="html"]');
    const blocksRadio = document.querySelector('input[name="content_mode"][value="blocks"]');
    const htmlPanel = document.getElementById('cms-html-panel');
    const blocksPanel = document.getElementById('cms-blocks-panel');
    const container = document.getElementById('cms-blocks-container');
    const protoMetrics = document.getElementById('cms-proto-metrics');
    const protoHtml = document.getElementById('cms-proto-html');
    const addMetrics = document.getElementById('cms-add-metrics');
    const addHtml = document.getElementById('cms-add-html');

    if (!htmlRadio || !blocksRadio || !htmlPanel || !blocksPanel || !container || !protoMetrics || !protoHtml) {
        return;
    }

    function syncPanels() {
        const blocks = blocksRadio.checked;
        htmlPanel.classList.toggle('d-none', blocks);
        blocksPanel.classList.toggle('d-none', !blocks);
    }

    /**
     * @param {HTMLElement} root
     */
    function reindexBlocks(root) {
        root.querySelectorAll(':scope > .cms-block-row').forEach((row, idx) => {
            row.querySelectorAll('[name^="blocks["]').forEach((el) => {
                el.name = el.name.replace(/^blocks\[[^\]]+\]/, `blocks[${idx}]`);
            });
        });
    }

    /**
     * @param {'metrics' | 'html'} kind
     */
    function appendBlock(kind) {
        const proto = kind === 'metrics' ? protoMetrics.firstElementChild : protoHtml.firstElementChild;
        if (!proto) {
            return;
        }
        const clone = proto.cloneNode(true);
        container.appendChild(clone);
        reindexBlocks(container);
        clone.querySelectorAll('.cms-block-remove').forEach((btn) => {
            btn.addEventListener('click', () => {
                clone.remove();
                reindexBlocks(container);
            });
        });
    }

    htmlRadio.addEventListener('change', syncPanels);
    blocksRadio.addEventListener('change', syncPanels);

    container.querySelectorAll('.cms-block-row').forEach((row) => {
        row.querySelectorAll('.cms-block-remove').forEach((btn) => {
            btn.addEventListener('click', () => {
                row.remove();
                reindexBlocks(container);
            });
        });
    });

    if (addMetrics) {
        addMetrics.addEventListener('click', () => appendBlock('metrics'));
    }
    if (addHtml) {
        addHtml.addEventListener('click', () => appendBlock('html'));
    }

    blocksRadio.addEventListener('change', () => {
        if (blocksRadio.checked && container.querySelectorAll(':scope > .cms-block-row').length === 0) {
            appendBlock('metrics');
        }
    });

    syncPanels();

    if (blocksRadio.checked && container.querySelectorAll(':scope > .cms-block-row').length === 0) {
        appendBlock('metrics');
    }
})();
