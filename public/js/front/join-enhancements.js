/* Page Rejoindre — intl-tel-input v25 : loadUtils (obligatoire) + validation E.164 avec separateDialCode */
(function () {
  'use strict';

  var ITI_UTILS_URL = '/assets/vendor/intl-tel-input/25/build/js/utils.js';

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
    var form = document.querySelector('.ggz-page-join .ggz-form');
    if (!form) {
      return;
    }

    var fieldMsgs = {
      sectorRequired: form.getAttribute('data-join-msg-sector') || '',
      fullNameRequired: form.getAttribute('data-join-msg-full-name') || '',
      emailRequired: form.getAttribute('data-join-msg-email-req') || '',
      emailInvalid: form.getAttribute('data-join-msg-email-invalid') || '',
    };

    var isEn =
      (document.documentElement.getAttribute('lang') || '')
        .toLowerCase()
        .indexOf('en') === 0;
    if (!fieldMsgs.sectorRequired) {
      fieldMsgs.sectorRequired = isEn
        ? 'Select at least one sector.'
        : 'Sélectionnez au moins un secteur.';
    }
    if (!fieldMsgs.fullNameRequired) {
      fieldMsgs.fullNameRequired = isEn
        ? 'Full name is required.'
        : 'Le nom complet est obligatoire.';
    }
    if (!fieldMsgs.emailRequired) {
      fieldMsgs.emailRequired = isEn
        ? 'Email address is required.'
        : 'L’adresse e-mail est obligatoire.';
    }
    if (!fieldMsgs.emailInvalid) {
      fieldMsgs.emailInvalid = isEn
        ? 'Please enter a valid email address.'
        : 'L’adresse e-mail n’est pas valide.';
    }

    function showFieldError(id, msg) {
      var el = document.getElementById(id);
      if (!el) {
        return;
      }
      if (msg) {
        el.textContent = msg;
        el.hidden = false;
      } else {
        el.textContent = '';
        el.hidden = true;
      }
    }

    function validateRequiredFields() {
      var sector = document.getElementById('sector');
      var fullName = document.getElementById('full_name');
      var email = document.getElementById('email');
      var firstFocus = null;

      showFieldError('sector-error', '');
      showFieldError('full_name-error', '');
      showFieldError('email-error', '');

      if (sector && sector.selectedOptions.length === 0) {
        showFieldError('sector-error', fieldMsgs.sectorRequired || '');
        firstFocus = sector;
      }
      if (fullName && !fullName.value.trim()) {
        showFieldError('full_name-error', fieldMsgs.fullNameRequired || '');
        if (!firstFocus) {
          firstFocus = fullName;
        }
      }
      if (email) {
        if (!email.value.trim()) {
          showFieldError('email-error', fieldMsgs.emailRequired || '');
          if (!firstFocus) {
            firstFocus = email;
          }
        } else if (!email.checkValidity()) {
          showFieldError(
            'email-error',
            fieldMsgs.emailInvalid || email.validationMessage
          );
          if (!firstFocus) {
            firstFocus = email;
          }
        }
      }

      if (firstFocus) {
        try {
          firstFocus.focus();
        } catch (eF) {
          /* ignore */
        }
        return false;
      }
      return true;
    }

    var sectorEl = document.getElementById('sector');
    if (sectorEl) {
      sectorEl.addEventListener('change', function () {
        if (sectorEl.selectedOptions.length > 0) {
          showFieldError('sector-error', '');
        }
      });
    }

    var fullNameEl = document.getElementById('full_name');
    if (fullNameEl) {
      fullNameEl.addEventListener('input', function () {
        if (fullNameEl.value.trim()) {
          showFieldError('full_name-error', '');
        }
      });
    }

    var emailEl = document.getElementById('email');
    if (emailEl) {
      emailEl.addEventListener('input', function () {
        if (emailEl.value.trim() && emailEl.checkValidity()) {
          showFieldError('email-error', '');
        }
      });
    }

    var phoneInput = document.getElementById('phone');
    var phoneCountry = document.getElementById('phone_country');
    var phoneErrorEl = document.getElementById('phone-error');
    var intlTelInit =
      typeof window !== 'undefined' ? window.intlTelInput : null;

    if (!phoneInput || !phoneCountry || typeof intlTelInit !== 'function') {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validateRequiredFields()) {
          return;
        }
        form.submit();
      });
      return;
    }

    var phoneMsgs = {};
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

    var iti = null;
    try {
      iti = intlTelInit(phoneInput, {
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
    } catch (eInit) {
      iti = null;
    }

    if (!iti) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!validateRequiredFields()) {
          return;
        }
        form.submit();
      });
      return;
    }

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
      showFieldError('phone_country-error', '');
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

      e.preventDefault();

      if (!validateRequiredFields()) {
        return;
      }

      syncCountryCode();

      const raw = phoneInput.value.replace(/\s/g, '').trim();

      if (raw === '') {
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
        return;
      }

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