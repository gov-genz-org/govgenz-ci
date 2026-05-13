/* Page Rejoindre — intl-tel-input v25 : loadUtils (obligatoire) + validation E.164 avec separateDialCode */
(function () {
  'use strict';

  const ITI_UTILS_URL = 'https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/js/utils.js';

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
      return;
    }
    fn();
  }

  function digitsOnly(s) {
    return (s || '').replace(/\D/g, '');
  }

  /**
   * Avec separateDialCode, ITI concatène +indicatif + champ sans retirer le préfixe national (0…).
   * On construit un E.164 correct pour libphonenumber.
   */
  function buildE164ForValidation(iti, inputEl) {
    const country = iti.getSelectedCountryData();

    if (!country || !country.iso2 || !country.dialCode) {
      return '';
    }

    const raw = (inputEl.value || '').trim();

    if (raw.indexOf('+') === 0) {
      return raw.replace(/[^\d+]/g, '');
    }

    const dial = String(country.dialCode);
    const np = country.nationalPrefix;
    let digits = digitsOnly(raw);

    if (!digits) {
      return '';
    }

    if (
      np &&
      digits.length > np.length &&
      digits.substring(0, np.length) === np
    ) {
      digits = digits.substring(np.length);
    }

    return '+' + dial + digits;
  }

  function getIntlUtils() {
    return typeof window !== 'undefined' &&
      window.intlTelInput &&
      window.intlTelInput.utils
      ? window.intlTelInput.utils
      : null;
  }

  function isPhoneValid(iti, inputEl) {
    const raw = (inputEl.value || '').replace(/\s/g, '').trim();

    if (raw === '') {
      return true;
    }

    const utils = getIntlUtils();

    if (!utils) {
      return false;
    }

    const full = buildE164ForValidation(iti, inputEl);

    if (!full || digitsOnly(full).length < 6) {
      return false;
    }

    const iso = iti.getSelectedCountryData().iso2;

    return utils.isValidNumber(full, iso, null);
  }

  function getPhoneValidationErrorCode(iti, inputEl) {
    const utils = getIntlUtils();

    if (!utils) {
      return -99;
    }

    return utils.getValidationError(
      buildE164ForValidation(iti, inputEl),
      iti.getSelectedCountryData().iso2
    );
  }

  onReady(function () {
    const form = document.querySelector('.ggz-page-join .ggz-form');
    const phoneInput = document.getElementById('phone');
    const phoneCountry = document.getElementById('phone_country');
    const phoneErrorEl = document.getElementById('phone-error');
    const intlTelInit =
      typeof window !== 'undefined' ? window.intlTelInput : null;

    if (
      !form ||
      !phoneInput ||
      !phoneCountry ||
      typeof intlTelInit !== 'function'
    ) {
      return;
    }

    let phoneMsgs = {};

    try {
      phoneMsgs = JSON.parse(form.getAttribute('data-phone-msgs') || '{}');
    } catch (e1) {
      phoneMsgs = {};
    }

    function phoneValidationMessage(code) {
      const key = String(code);

      if (
        Object.prototype.hasOwnProperty.call(phoneMsgs, key) &&
        phoneMsgs[key]
      ) {
        return phoneMsgs[key];
      }

      return phoneMsgs.generic || '';
    }

    function showPhoneError(msg) {
      if (!phoneErrorEl) {
        return;
      }

      if (msg) {
        phoneErrorEl.textContent = msg;
        phoneErrorEl.hidden = false;
      } else {
        phoneErrorEl.textContent = '';
        phoneErrorEl.hidden = true;
      }
    }

    /* v25 : utilsScript / loadUtilsOnInit sont ignorés — il faut loadUtils → import dynamique du module utils */
    const iti = intlTelInit(phoneInput, {
      initialCountry: 'mg',
      separateDialCode: true,
      autoPlaceholder: 'aggressive',
      placeholderNumberType: 'MOBILE',
      validationNumberTypes: null,

      /* attachUtils attend une promesse qui résout vers le module ES ({ default: utils }), pas l’objet utils seul */
      loadUtils: function () {
        return import(ITI_UTILS_URL);
      },
    });

    function syncCountryCode() {
      const country = iti.getSelectedCountryData();

      if (!country || !country.dialCode) {
        return;
      }

      phoneCountry.value = '+' + country.dialCode;
    }

    syncCountryCode();

    iti.promise
      .then(function () {
        syncCountryCode();
      })
      .catch(function () {
        showPhoneError(phoneMsgs.generic || '');
      });

    let showPhoneValidation = false;

    function withUtils(fn) {
      if (iti.promise && typeof iti.promise.then === 'function') {
        iti.promise.then(fn).catch(function () {});
      } else {
        fn();
      }
    }

    function runPhoneValidationImmediate() {
      const raw = phoneInput.value.replace(/\s/g, '');

      if (raw === '') {
        showPhoneError('');
        return;
      }

      if (!isPhoneValid(iti, phoneInput)) {
        showPhoneError(
          phoneValidationMessage(
            getPhoneValidationErrorCode(iti, phoneInput)
          )
        );
        return;
      }

      showPhoneError('');
    }

    function runPhoneValidation() {
      if (!showPhoneValidation || !phoneErrorEl) {
        return;
      }

      withUtils(runPhoneValidationImmediate);
    }

    phoneInput.addEventListener('countrychange', function () {
      syncCountryCode();
      runPhoneValidation();
    });

    phoneInput.addEventListener('blur', function () {
      showPhoneValidation = true;
      runPhoneValidation();
    });

    phoneInput.addEventListener('input', function () {
      if (showPhoneValidation) {
        runPhoneValidation();
      } else {
        showPhoneError('');
      }
    });

    let allowItiValidatedSubmit = false;

    form.addEventListener('submit', function (e) {
      if (allowItiValidatedSubmit) {
        return;
      }

      syncCountryCode();

      const raw = phoneInput.value.replace(/\s/g, '').trim();

      if (raw === '') {
        showPhoneError('');
        return;
      }

      e.preventDefault();
      showPhoneValidation = true;

      function afterUtils() {
        if (!isPhoneValid(iti, phoneInput)) {
          showPhoneError(
            phoneValidationMessage(
              getPhoneValidationErrorCode(iti, phoneInput)
            )
          );

          phoneInput.focus();
          return;
        }

        showPhoneError('');
        allowItiValidatedSubmit = true;

        window.setTimeout(function () {
          try {
            if (typeof form.requestSubmit === 'function') {
              form.requestSubmit();
            } else {
              form.submit();
            }
          } finally {
            allowItiValidatedSubmit = false;
          }
        }, 0);
      }

      if (iti.promise && typeof iti.promise.then === 'function') {
        iti.promise
          .then(afterUtils)
          .catch(function () {
            showPhoneError(phoneMsgs.generic || '');
          });
      } else {
        afterUtils();
      }
    });
  });
})();