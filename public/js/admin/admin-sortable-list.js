(() => {
    /**
     * @param {MouseEvent|DragEvent} pointer
     * @param {HTMLElement} tbody
     * @returns {HTMLElement|null}
     */
    function getDragAfterRow(pointer, tbody) {
        const rows = Array.from(tbody.querySelectorAll(':scope > tr[data-sortable-id]:not(.is-dragging)'));
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
     * @param {HTMLElement} tbody
     */
    function refreshSortOrderCells(tbody) {
        tbody.querySelectorAll(':scope > tr[data-sortable-id]').forEach((row, idx) => {
            const cell = row.querySelector('[data-sort-order-cell]');
            if (cell) {
                cell.textContent = String((idx + 1) * 10);
            }
        });
    }

    /**
     * @param {HTMLElement} tbody
     */
    async function persistOrder(tbody) {
        const url = tbody.dataset.reorderUrl || '';
        const tokenName = tbody.dataset.csrfName || '';
        const tokenHash = tbody.dataset.csrfHash || '';
        if (url === '' || tokenName === '' || tokenHash === '') {
            return;
        }

        const body = new FormData();
        body.append(tokenName, tokenHash);
        tbody.querySelectorAll(':scope > tr[data-sortable-id]').forEach((row) => {
            const id = row.dataset.sortableId || '';
            if (id !== '') {
                body.append('ids[]', id);
            }
        });

        const response = await fetch(url, {
            method: 'POST',
            body: body,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        refreshSortOrderCells(tbody);
    }

    /**
     * @param {HTMLElement} row
     * @param {HTMLElement} tbody
     */
    function bindSortableRow(row, tbody) {
        if (row.dataset.sortableBound === '1') {
            return;
        }
        row.dataset.sortableBound = '1';
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', (ev) => {
            if (ev.target && ev.target.closest && !ev.target.closest('.admin-sortable-handle')) {
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
            tbody.classList.remove('is-drag-over');
            void persistOrder(tbody);
        });
    }

    document.querySelectorAll('[data-admin-sortable-list]').forEach((tbody) => {
        if (!(tbody instanceof HTMLElement)) {
            return;
        }

        tbody.querySelectorAll(':scope > tr[data-sortable-id]').forEach((row) => {
            if (row instanceof HTMLElement) {
                bindSortableRow(row, tbody);
            }
        });

        tbody.addEventListener('dragover', (ev) => {
            ev.preventDefault();
            const dragging = tbody.querySelector(':scope > tr.is-dragging');
            if (!(dragging instanceof HTMLElement)) {
                return;
            }
            tbody.classList.add('is-drag-over');
            const afterElement = getDragAfterRow(ev, tbody);
            if (afterElement === null) {
                tbody.appendChild(dragging);
            } else {
                tbody.insertBefore(dragging, afterElement);
            }
        });

        tbody.addEventListener('drop', (ev) => {
            ev.preventDefault();
            tbody.classList.remove('is-drag-over');
        });
    });
})();
