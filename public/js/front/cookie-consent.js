(function () {
  var COOKIE_NAME = 'ggz_consent';
  var COOKIE_MAX_AGE_DAYS = 395;
  var CONSENT_VERSION = '1';

  var root = document.getElementById('ggz-cookie-consent');
  if (!root) {
    return;
  }

  var measurementId = root.getAttribute('data-ga4-id') || '';
  if (measurementId === '') {
    return;
  }

  function readConsent() {
    var raw = '';
    try {
      raw = document.cookie.split(';').map(function (s) {
        return s.trim();
      }).filter(function (s) {
        return s.indexOf(COOKIE_NAME + '=') === 0;
      })[0] || '';
      raw = raw ? decodeURIComponent(raw.split('=').slice(1).join('=')) : '';
    } catch (e) {
      raw = '';
    }
    if (raw === '') {
      return null;
    }
    try {
      return JSON.parse(raw);
    } catch (err) {
      return null;
    }
  }

  function writeConsent(analyticsGranted) {
    var payload = {
      v: CONSENT_VERSION,
      analytics: analyticsGranted === true,
      ts: Date.now(),
    };
    var encoded = encodeURIComponent(JSON.stringify(payload));
    var maxAge = COOKIE_MAX_AGE_DAYS * 24 * 60 * 60;
    var secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = COOKIE_NAME + '=' + encoded + '; Path=/; Max-Age=' + maxAge + '; SameSite=Lax' + secure;
    return payload;
  }

  function hideBanner() {
    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
  }

  function showBanner() {
    root.hidden = false;
    root.setAttribute('aria-hidden', 'false');
  }

  function applyConsent(payload) {
    if (payload && payload.analytics === true && typeof window.ggzLoadAnalytics === 'function') {
      window.ggzLoadAnalytics(measurementId);
    }
  }

  var existing = readConsent();
  if (existing && existing.v === CONSENT_VERSION) {
    hideBanner();
    applyConsent(existing);
    return;
  }

  showBanner();

  root.addEventListener('click', function (e) {
    var t = e.target;
    if (!t || !t.closest) {
      return;
    }
    var accept = t.closest('[data-ggz-consent="accept"]');
    var reject = t.closest('[data-ggz-consent="reject"]');
    if (!accept && !reject) {
      return;
    }
    var granted = !!accept;
    var payload = writeConsent(granted);
    hideBanner();
    applyConsent(payload);
  });
})();
