(function () {
  function bindAccordion(scope) {
    var root = scope || document;
    var labelOpen = '';
    var labelClose = '';
    var programRoot = root.querySelector
      ? root.querySelector('.js-positions-program-root')
      : null;
    if (programRoot) {
      labelOpen = programRoot.getAttribute('data-label-toggle-open') || '';
      labelClose = programRoot.getAttribute('data-label-toggle-close') || '';
    }

    var cards = root.querySelectorAll ? root.querySelectorAll('.js-positions-avis-card') : [];
    var ci;
    for (ci = 0; ci < cards.length; ci++) {
      var card = cards[ci];
      var btn = card.querySelector('.js-positions-toggle');
      if (!btn || btn.getAttribute('data-bound') === '1') {
        continue;
      }
      btn.setAttribute('data-bound', '1');
      btn.addEventListener('click', function () {
        var c = this.closest('.js-positions-avis-card');
        if (!c) {
          return;
        }
        var open = c.classList.toggle('is-open');
        this.setAttribute('aria-expanded', open ? 'true' : 'false');
        var lab = this.querySelector('.js-positions-toggle-label');
        if (lab) {
          if (open && labelClose !== '') {
            lab.textContent = labelClose;
          } else if (!open && labelOpen !== '') {
            lab.textContent = labelOpen;
          }
        }
      });
    }
  }

  window.positionsBindAccordion = bindAccordion;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      bindAccordion(document);
    });
  } else {
    bindAccordion(document);
  }
})();
