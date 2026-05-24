/**
 * Listes répétables des blocs projet (admin) : + Ligne, retirer, réindexation des names.
 */
(() => {
  const root =
    document.getElementById('pp-blocks-container') ||
    document.getElementById('pp-blocks-panel');
  if (!root) {
    return;
  }

  /**
   * @param {HTMLElement} card
   * @returns {string|null}
   */
  function blockIndex(card) {
    const inp = card.querySelector('.card-body input[type="hidden"][name^="blocks["][name$="[type]"]');
    if (!inp || !inp.name) {
      return null;
    }
    const m = inp.name.match(/^blocks\[(\d+)\]/);
    return m ? m[1] : null;
  }

  /**
   * @param {HTMLElement} wrap
   * @returns {HTMLElement|null}
   */
  function repeatBody(wrap) {
    return wrap.querySelector('.pp-repeat-body');
  }

  /**
   * @param {HTMLElement} body
   * @returns {NodeListOf<HTMLElement>}
   */
  function repeatRows(body) {
    return body.querySelectorAll('.pp-repeat-row');
  }

  /**
   * @param {HTMLElement} wrap
   */
  function reindexRepeatable(wrap) {
    const card = wrap.closest('.project-block-row');
    if (!card) {
      return;
    }
    const idx = blockIndex(card);
    const key = wrap.getAttribute('data-pp-repeat-key');
    if (idx === null || !key) {
      return;
    }
    const body = repeatBody(wrap);
    if (!body) {
      return;
    }
    const escaped = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const rowRe = new RegExp(`^blocks\\[${idx}\\]\\[${escaped}\\]\\[\\d+\\]`);
    repeatRows(body).forEach((row, ri) => {
      row.querySelectorAll('[name^="blocks["]').forEach((el) => {
        el.name = el.name.replace(rowRe, `blocks[${idx}][${key}][${ri}]`);
      });
    });
  }

  /**
   * @param {HTMLTemplateElement} template
   * @param {number} rowIndex
   * @returns {HTMLElement|null}
   */
  function cloneRowFromTemplate(template, rowIndex) {
    const clone = template.content.cloneNode(true);
    clone.querySelectorAll('[name]').forEach((el) => {
      if (el.name && el.name.indexOf('__RI__') !== -1) {
        el.name = el.name.replace(/__RI__/g, String(rowIndex));
      }
    });

    const row = clone.querySelector('.pp-repeat-row');
    if (row) {
      return row;
    }

    const first = clone.firstElementChild;
    if (first instanceof HTMLElement) {
      first.classList.add('pp-repeat-row');

      return first;
    }

    return null;
  }

  /**
   * @param {HTMLElement} wrap
   */
  function ensureMinOneRow(wrap) {
    const body = repeatBody(wrap);
    const template = wrap.querySelector('template.pp-repeat-template');
    if (!body || !template || repeatRows(body).length > 0) {
      return;
    }
    const row = cloneRowFromTemplate(template, 0);
    if (row) {
      body.appendChild(row);
    }
  }

  /**
   * @param {HTMLElement} wrap
   */
  function bindRepeatableControls(wrap) {
    wrap.querySelectorAll('.pp-repeat-add').forEach((btn) => {
      if (btn.dataset.ppRepeatBound === '1') {
        return;
      }
      btn.dataset.ppRepeatBound = '1';
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        addRow(btn);
      });
    });

    wrap.querySelectorAll('button.pp-repeat-remove').forEach((btn) => {
      if (btn.dataset.ppRepeatBound === '1') {
        return;
      }
      btn.dataset.ppRepeatBound = '1';
      btn.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        removeRow(btn);
      });
    });
  }

  /**
   * @param {HTMLElement} btn
   */
  function addRow(btn) {
    const wrap = btn.closest('.pp-repeatable');
    if (!wrap) {
      return;
    }
    const body = repeatBody(wrap);
    const template = wrap.querySelector('template.pp-repeat-template');
    if (!body || !template) {
      return;
    }
    const ri = repeatRows(body).length;
    const row = cloneRowFromTemplate(template, ri);
    if (!row) {
      return;
    }
    body.appendChild(row);
    reindexRepeatable(wrap);
    bindRepeatableControls(wrap);
    triggerBudgetSync(wrap);
  }

  /**
   * @param {HTMLElement} scope
   */
  function triggerBudgetSync(scope) {
    const montant = scope.querySelector('.pp-budget-line-montant');
    if (montant) {
      montant.dispatchEvent(new Event('input', { bubbles: true }));
    }
  }

  /**
   * @param {HTMLElement} btn
   */
  function removeRow(btn) {
    const wrap = btn.closest('.pp-repeatable');
    const row = btn.closest('.pp-repeat-row');
    if (!wrap || !row) {
      return;
    }
    const body = repeatBody(wrap);
    if (!body) {
      return;
    }

    row.remove();
    ensureMinOneRow(wrap);
    reindexRepeatable(wrap);
    bindRepeatableControls(wrap);
    triggerBudgetSync(wrap);
  }

  /**
   * @param {HTMLElement} wrap
   */
  function initRepeatable(wrap) {
    ensureMinOneRow(wrap);
    reindexRepeatable(wrap);
    bindRepeatableControls(wrap);
    wrap.dataset.ppRepeatInit = '1';
  }

  function scanRepeatables() {
    root.querySelectorAll('.pp-repeatable').forEach((wrap) => {
      initRepeatable(wrap);
    });
  }

  const observer = new MutationObserver(() => {
    scanRepeatables();
  });
  observer.observe(root, { childList: true, subtree: true });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scanRepeatables);
  } else {
    scanRepeatables();
  }
})();
