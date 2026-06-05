/**
 * Bascule thème clair / sombre (front public).
 * Le thème initial est appliqué par un script inline dans <head> (évite le flash).
 */
(function () {
  var STORAGE_KEY = 'ggz-theme';
  var root = document.documentElement;
  var metaTheme = document.querySelector('meta[name="theme-color"]');
  var ICON_LIGHT = '\u2600';
  var ICON_DARK = '\u263E';

  function currentTheme() {
    return root.dataset.theme === 'light' ? 'light' : 'dark';
  }

  function applyTheme(theme, persist) {
    var next = theme === 'light' ? 'light' : 'dark';
    root.dataset.theme = next;

    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, next);
      } catch (e) {
        /* localStorage indisponible */
      }
    }

    if (metaTheme) {
      var color = getComputedStyle(root).getPropertyValue('--ggz-meta-theme-color').trim();
      if (color !== '') {
        metaTheme.setAttribute('content', color);
      }
    }

    updateToggleUi(next);

    try {
      document.dispatchEvent(
        new CustomEvent('ggz-theme-change', { detail: { theme: next } })
      );
    } catch (e) {
      /* CustomEvent indisponible */
    }
  }

  function updateToggleUi(theme) {
    var btn = document.getElementById('theme-toggle');
    if (!btn) {
      return;
    }

    var titleLight = btn.getAttribute('data-title-light') || 'Light';
    var titleDark = btn.getAttribute('data-title-dark') || 'Dark';
    var iconEl = btn.querySelector('.theme-toggle__icon');

    if (theme === 'light') {
      btn.setAttribute('aria-pressed', 'true');
      btn.setAttribute('title', titleDark);
      btn.setAttribute('aria-label', titleDark);
      if (iconEl) {
        iconEl.textContent = ICON_DARK;
      }
    } else {
      btn.setAttribute('aria-pressed', 'false');
      btn.setAttribute('title', titleLight);
      btn.setAttribute('aria-label', titleLight);
      if (iconEl) {
        iconEl.textContent = ICON_LIGHT;
      }
    }
  }

  function initThemeToggle() {
    applyTheme(currentTheme(), false);

    var btn = document.getElementById('theme-toggle');
    if (!btn) {
      return;
    }

    btn.addEventListener('click', function () {
      applyTheme(currentTheme() === 'light' ? 'dark' : 'light', true);
    });
  }

  window.GgzTheme = {
    apply: applyTheme,
    current: currentTheme,
  };

  document.addEventListener('DOMContentLoaded', initThemeToggle);
})();
