/**
 * Somme des lignes du bloc « Tableau budget » → champs Budget carte (montant + aperçu).
 */
(() => {
  const amountEl = document.getElementById('pp-budget-amount');
  const scaleEl = document.getElementById('pp-budget-scale');
  const blocksPanel = document.getElementById('pp-blocks-panel');

  if (!amountEl || !scaleEl || !blocksPanel) {
    return;
  }

  function parseMontantToAriary(raw) {
    const s = String(raw || '').trim();
    if (s === '') {
      return 0;
    }

    const lower = s.toLowerCase();
    let numPart = s;
    let mult = 1;

    if (/\bmds?\b|milliard|billion|\bbn\b/.test(lower)) {
      mult = 1_000_000_000;
      numPart = s.replace(/[^\d\s.,]/gi, '');
    } else if (/\bm\s*ar\b/.test(lower) || /\d\s+m\b/i.test(s)) {
      mult = 1_000_000;
      numPart = s.replace(/[^\d\s.,]/gi, '');
    } else if (/\bk\s*ar\b/.test(lower)) {
      mult = 1_000;
      numPart = s.replace(/[^\d\s.,]/gi, '');
    } else if (/\bar\b/i.test(lower)) {
      numPart = s.replace(/[^\d\s.,]/gi, '');
    } else {
      numPart = s.replace(/[^\d\s.,]/gi, '');
    }

    const compact = numPart.replace(/[\s\u00a0\u202f]/g, '').replace(',', '.');
    const n = parseFloat(compact);
    if (Number.isNaN(n) || n <= 0) {
      return 0;
    }

    return n * mult;
  }

  function sumBudgetTables() {
    let sum = 0;
    let count = 0;
    blocksPanel.querySelectorAll('.pp-budget-table-block').forEach((block) => {
      block.querySelectorAll('.pp-repeat-body .pp-repeat-row').forEach((tr) => {
        const montantInput = tr.querySelector('input.pp-budget-line-montant[name$="[montant]"]');
        if (!montantInput) {
          return;
        }
        const poste = tr.querySelector('input[name$="[poste]"]');
        const detail = tr.querySelector('input[name$="[detail]"]');
        const hasContent =
          (poste && poste.value.trim() !== '') ||
          (detail && detail.value.trim() !== '') ||
          montantInput.value.trim() !== '';
        if (!hasContent) {
          return;
        }
        const part = parseMontantToAriary(montantInput.value);
        if (part > 0) {
          sum += part;
          count += 1;
        }
      });
    });

    return count > 0 ? sum : null;
  }

  function partsFromAriary(ariary) {
    if (ariary >= 1_000_000_000) {
      return { amount: ariary / 1_000_000_000, scale: 'billion' };
    }
    if (ariary >= 1_000_000) {
      return { amount: ariary / 1_000_000, scale: 'million' };
    }
    if (ariary >= 1_000) {
      return { amount: ariary / 1_000, scale: 'thousand' };
    }

    return { amount: ariary, scale: 'ariary' };
  }

  function syncCardFromTables() {
    const sum = sumBudgetTables();
    if (sum === null) {
      return;
    }
    const parts = partsFromAriary(sum);
    const dec = parts.amount % 1 === 0 ? 0 : 4;
    amountEl.value = parts.amount.toFixed(dec).replace(/\.?0+$/, '');
    scaleEl.value = parts.scale;
    amountEl.dispatchEvent(new Event('input', { bubbles: true }));
    amountEl.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function bindBudgetBlock(block) {
    block.querySelectorAll('input.pp-budget-line-montant[name$="[montant]"]').forEach((input) => {
      if (input.dataset.ppBudgetBound === '1') {
        return;
      }
      input.dataset.ppBudgetBound = '1';
      input.addEventListener('input', syncCardFromTables);
      input.addEventListener('change', syncCardFromTables);
    });
    block.querySelectorAll('input[name$="[poste]"], input[name$="[detail]"]').forEach((input) => {
      if (input.dataset.ppBudgetBound === '1') {
        return;
      }
      input.dataset.ppBudgetBound = '1';
      input.addEventListener('input', syncCardFromTables);
      input.addEventListener('change', syncCardFromTables);
    });
  }

  function scanBlocks() {
    blocksPanel.querySelectorAll('.pp-budget-table-block').forEach(bindBudgetBlock);
    syncCardFromTables();
  }

  const observer = new MutationObserver(() => {
    scanBlocks();
  });
  observer.observe(blocksPanel, { childList: true, subtree: true });

  blocksPanel.addEventListener('input', (ev) => {
    const t = ev.target;
    if (!(t instanceof HTMLElement)) {
      return;
    }
    if (
      t.matches('input.pp-budget-line-montant[name$="[montant]"]') ||
      t.matches('input[name$="[poste]"]') ||
      t.matches('input[name$="[detail]"]')
    ) {
      syncCardFromTables();
    }
  });

  scanBlocks();
})();
