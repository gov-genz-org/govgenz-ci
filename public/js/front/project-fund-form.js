(() => {
    var modal = document.querySelector('[data-fund-modal]');
    var form = document.querySelector('[data-project-fund-form]');
    if (!form) {
        return;
    }

    var typeInput = form.querySelector('[data-fund-type-input]');
    var radios = form.querySelectorAll('[data-fund-type-radio]');
    var panels = form.querySelectorAll('[data-fund-panel]');
    var openTriggers = document.querySelectorAll('[data-fund-modal-open]');
    var closeTriggers = document.querySelectorAll('[data-fund-modal-close]');
    var errorsBox = form.querySelector('[data-fund-errors]');
    var phoneInstances = {};
    var ITI_UTILS_URL = 'https://cdn.jsdelivr.net/npm/intl-tel-input@25/build/js/utils.js';

    var phoneMsgs = {};
    var fieldMsgs = {};
    var validateRules = { budget: [], material: [] };
    try {
        phoneMsgs = JSON.parse(form.getAttribute('data-fund-phone-msgs') || '{}');
    } catch (eParse) {
        phoneMsgs = {};
    }
    try {
        fieldMsgs = JSON.parse(form.getAttribute('data-fund-field-msgs') || '{}');
    } catch (eFieldMsgs) {
        fieldMsgs = {};
    }
    try {
        validateRules = JSON.parse(form.getAttribute('data-fund-validate-rules') || '{}');
    } catch (eRules) {
        validateRules = { budget: [], material: [] };
    }

    function fieldMsg(fieldName, rule) {
        var bucket = fieldMsgs[fieldName];
        if (bucket && Object.prototype.hasOwnProperty.call(bucket, rule) && bucket[rule]) {
            return bucket[rule];
        }
        return '';
    }

    function showFieldError(errorEl, msg) {
        if (!errorEl) {
            return;
        }
        if (msg) {
            errorEl.textContent = msg;
            errorEl.hidden = false;
        } else {
            errorEl.textContent = '';
            errorEl.hidden = true;
        }
    }

    function syncClientErrorsSummary() {
        if (!errorsBox) {
            return;
        }
        var lines = [];
        var visible = form.querySelectorAll('.project-fund-form__field-error:not([hidden])');
        for (var i = 0; i < visible.length; i++) {
            var text = (visible[i].textContent || '').trim();
            if (text) {
                lines.push(text);
            }
        }
        if (lines.length === 0) {
            errorsBox.hidden = true;
            errorsBox.innerHTML = '';
            return;
        }
        var html = '<ul>';
        for (var j = 0; j < lines.length; j++) {
            html += '<li>' + lines[j].replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</li>';
        }
        html += '</ul>';
        errorsBox.innerHTML = html;
        errorsBox.removeAttribute('hidden');
    }

    function validateRule(rule) {
        var input = document.getElementById(rule.input);
        var errorEl = document.getElementById(rule.error);
        if (!input || !errorEl || input.disabled) {
            showFieldError(errorEl, '');
            return true;
        }
        var val = (input.value || '').trim();
        var name = rule.name || '';
        if (rule.required && val === '') {
            showFieldError(errorEl, fieldMsg(name, 'required'));
            return false;
        }
        if (rule.max && val.length > rule.max) {
            showFieldError(errorEl, fieldMsg(name, 'max_length'));
            return false;
        }
        if (rule.amount && val !== '' && !/\d/.test(val)) {
            showFieldError(errorEl, fieldMsg(name, 'invalid'));
            return false;
        }
        if (rule.email && val !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            showFieldError(errorEl, fieldMsg(name, 'invalid'));
            return false;
        }
        showFieldError(errorEl, '');
        return true;
    }

    var itemsList = form.querySelector('[data-fund-items-list]');
    var itemsListError = form.querySelector('[data-fund-items-list-error]');
    var itemsAddBtn = form.querySelector('[data-fund-items-add]');
    var itemsTemplate = document.getElementById('fund-material-item-template');
    var ITEMS_MAX_ROWS = 20;
    var itemRemoveLabel = form.getAttribute('data-fund-remove-label') || 'Remove';

    function setRowRemoveAction(row, showRemove) {
        var removeCol = row.querySelector('.project-fund-form__item-input--remove');
        if (!removeCol) {
            return;
        }
        var removeBtn = removeCol.querySelector('[data-fund-item-remove]');
        var spacer = removeCol.querySelector('.project-fund-form__item-remove-spacer');
        if (showRemove) {
            if (spacer) {
                spacer.remove();
            }
            if (!removeBtn) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'project-fund-form__item-remove';
                removeBtn.setAttribute('data-fund-item-remove', '');
                removeBtn.setAttribute('aria-label', itemRemoveLabel);
                removeBtn.textContent = '×';
                removeCol.appendChild(removeBtn);
                removeBtn.addEventListener('click', function () {
                    var rows = itemsList ? itemsList.querySelectorAll('[data-fund-item-row]') : [];
                    if (rows.length <= 1) {
                        return;
                    }
                    row.remove();
                    reindexMaterialRows();
                    clearErrors();
                });
            }
            return;
        }
        if (removeBtn) {
            removeBtn.remove();
        }
        if (!spacer) {
            spacer = document.createElement('span');
            spacer.className = 'project-fund-form__item-remove-spacer';
            spacer.setAttribute('aria-hidden', 'true');
            removeCol.appendChild(spacer);
        }
    }

    function reindexMaterialRows() {
        if (!itemsList) {
            return;
        }
        var rows = itemsList.querySelectorAll('[data-fund-item-row]');
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var nameInput = row.querySelector('[data-fund-item-name]');
            var qtyInput = row.querySelector('[data-fund-item-qty]');
            if (nameInput) {
                nameInput.id = 'fund-material-item-' + i;
            }
            if (qtyInput) {
                qtyInput.id = 'fund-material-qty-' + i;
            }
            setRowRemoveAction(row, i > 0);
        }
    }

    function bindMaterialRow(row) {
        if (!row) {
            return;
        }
        var nameInput = row.querySelector('[data-fund-item-name]');
        var qtyInput = row.querySelector('[data-fund-item-qty]');
        var nameErr = row.querySelector('[data-fund-item-name-error]');
        var qtyErr = row.querySelector('[data-fund-item-qty-error]');
        var removeBtn = row.querySelector('[data-fund-item-remove]');
        function clearRowFieldErrors() {
            showFieldError(nameErr, '');
            showFieldError(qtyErr, '');
            showFieldError(itemsListError, '');
            if (errorsBox) {
                errorsBox.hidden = true;
            }
        }
        if (nameInput) {
            nameInput.addEventListener('input', clearRowFieldErrors);
        }
        if (qtyInput) {
            qtyInput.addEventListener('input', clearRowFieldErrors);
        }
        if (removeBtn) {
            removeBtn.setAttribute('aria-label', itemRemoveLabel);
            removeBtn.addEventListener('click', function () {
                var rows = itemsList ? itemsList.querySelectorAll('[data-fund-item-row]') : [];
                if (rows.length <= 1) {
                    return;
                }
                row.remove();
                reindexMaterialRows();
                clearErrors();
            });
        }
    }

    function addMaterialRow() {
        if (!itemsList || !itemsTemplate) {
            return;
        }
        var rows = itemsList.querySelectorAll('[data-fund-item-row]');
        if (rows.length >= ITEMS_MAX_ROWS) {
            return;
        }
        var html = itemsTemplate.innerHTML.replace(/__INDEX__/g, String(rows.length));
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var row = wrap.firstElementChild;
        if (!row) {
            return;
        }
        itemsList.appendChild(row);
        bindMaterialRow(row);
        reindexMaterialRows();
    }

    function validateMaterialItemsList() {
        if (!itemsList) {
            return true;
        }
        var rows = itemsList.querySelectorAll('[data-fund-item-row]');
        var hasLine = false;
        var ok = true;
        var firstFocus = null;
        showFieldError(itemsListError, '');
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var nameInput = row.querySelector('[data-fund-item-name]');
            var qtyInput = row.querySelector('[data-fund-item-qty]');
            var nameErr = row.querySelector('[data-fund-item-name-error]');
            var qtyErr = row.querySelector('[data-fund-item-qty-error]');
            if (!nameInput || !qtyInput) {
                continue;
            }
            var item = (nameInput.value || '').trim();
            var qty = (qtyInput.value || '').trim();
            showFieldError(nameErr, '');
            showFieldError(qtyErr, '');
            if (item === '' && qty === '') {
                continue;
            }
            hasLine = true;
            if (item === '') {
                showFieldError(nameErr, fieldMsg('material_items', 'required'));
                ok = false;
                if (!firstFocus) {
                    firstFocus = nameInput;
                }
            } else if (item.length > 255) {
                showFieldError(nameErr, fieldMsg('material_items', 'max_length'));
                ok = false;
                if (!firstFocus) {
                    firstFocus = nameInput;
                }
            }
            if (qty === '') {
                showFieldError(qtyErr, fieldMsg('material_quantity', 'required'));
                ok = false;
                if (!firstFocus) {
                    firstFocus = qtyInput;
                }
            } else if (qty.length > 120) {
                showFieldError(qtyErr, fieldMsg('material_quantity', 'max_length'));
                ok = false;
                if (!firstFocus) {
                    firstFocus = qtyInput;
                }
            }
        }
        if (!hasLine) {
            showFieldError(itemsListError, fieldMsg('material_items', 'required'));
            ok = false;
        }
        if (firstFocus) {
            try {
                firstFocus.focus();
            } catch (eMatFocus) {
                /* ignore */
            }
        }
        return ok;
    }

    function initMaterialItemsList() {
        if (!itemsList) {
            return;
        }
        var rows = itemsList.querySelectorAll('[data-fund-item-row]');
        for (var i = 0; i < rows.length; i++) {
            bindMaterialRow(rows[i]);
        }
        reindexMaterialRows();
        if (itemsAddBtn) {
            itemsAddBtn.addEventListener('click', function () {
                addMaterialRow();
            });
        }
    }

    function validateActivePanel() {
        var t = activeType();
        if (t === 'material') {
            var rules = validateRules.material || [];
            var ok = true;
            var firstFocus = null;
            for (var i = 0; i < rules.length; i++) {
                if (!validateRule(rules[i])) {
                    ok = false;
                    if (!firstFocus) {
                        var inp = document.getElementById(rules[i].input);
                        if (inp) {
                            firstFocus = inp;
                        }
                    }
                }
            }
            if (!validateMaterialItemsList()) {
                ok = false;
            }
            if (firstFocus) {
                try {
                    firstFocus.focus();
                } catch (eFocus) {
                    /* ignore */
                }
            }
            return ok;
        }
        var rulesBudget = validateRules[t] || [];
        var okBudget = true;
        var firstFocusBudget = null;
        for (var j = 0; j < rulesBudget.length; j++) {
            if (!validateRule(rulesBudget[j])) {
                okBudget = false;
                if (!firstFocusBudget) {
                    var inputB = document.getElementById(rulesBudget[j].input);
                    if (inputB) {
                        firstFocusBudget = inputB;
                    }
                }
            }
        }
        if (firstFocusBudget) {
            try {
                firstFocusBudget.focus();
            } catch (eFocusB) {
                /* ignore */
            }
        }
        return okBudget;
    }

    function bindValidateInputs() {
        var inputs = form.querySelectorAll('[data-fund-validate-input]');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener('input', function () {
                var err = document.getElementById(this.id + '-error');
                showFieldError(err, '');
                if (errorsBox) {
                    errorsBox.hidden = true;
                }
            });
        }
    }

    function activeType() {
        for (var i = 0; i < radios.length; i++) {
            if (radios[i].checked) {
                return radios[i].value;
            }
        }
        if (typeInput && typeInput.value) {
            return typeInput.value;
        }

        return 'budget';
    }

    function clearErrors() {
        if (errorsBox) {
            errorsBox.hidden = true;
        }
        var fieldErrors = form.querySelectorAll('.project-fund-form__field-error');
        for (var i = 0; i < fieldErrors.length; i++) {
            fieldErrors[i].hidden = true;
            fieldErrors[i].textContent = '';
        }
    }

    function setPanelState(panel, show) {
        if (!panel) {
            return;
        }
        if (show) {
            panel.removeAttribute('hidden');
        } else {
            panel.setAttribute('hidden', 'hidden');
        }
        var fields = panel.querySelectorAll('input, textarea, select');
        for (var j = 0; j < fields.length; j++) {
            var f = fields[j];
            if (show) {
                if (f.dataset.fundRequired === '1') {
                    f.setAttribute('required', 'required');
                }
                f.removeAttribute('disabled');
            } else {
                f.removeAttribute('required');
                f.setAttribute('disabled', 'disabled');
            }
        }
    }

    function markRequiredFields() {
        for (var p = 0; p < panels.length; p++) {
            var panel = panels[p];
            var fields = panel.querySelectorAll('[required]');
            for (var i = 0; i < fields.length; i++) {
                fields[i].dataset.fundRequired = '1';
                fields[i].removeAttribute('required');
            }
        }
    }

    function syncPanels(clearValidation) {
        var t = activeType();
        if (typeInput) {
            typeInput.value = t;
        }
        for (var i = 0; i < panels.length; i++) {
            var panel = panels[i];
            var key = panel.getAttribute('data-fund-panel');
            setPanelState(panel, key === t);
        }
        if (clearValidation) {
            clearErrors();
        }
    }

    function openModal() {
        if (!modal || typeof modal.showModal !== 'function') {
            return;
        }
        if (!modal.open) {
            modal.showModal();
        }
        document.documentElement.classList.add('project-fund-modal-open');
        var flashAlert = form.querySelector('.project-fund-dialog__alert');
        if (flashAlert && typeof flashAlert.scrollIntoView === 'function') {
            flashAlert.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function closeModal() {
        if (!modal) {
            return;
        }
        if (modal.open) {
            modal.close();
        }
        document.documentElement.classList.remove('project-fund-modal-open');
    }

    function digitsOnly(s) {
        return (s || '').replace(/\D/g, '');
    }

    function buildE164ForValidation(iti, inputEl) {
        var country = iti.getSelectedCountryData();
        if (!country || !country.iso2 || !country.dialCode) {
            return '';
        }
        var raw = (inputEl.value || '').trim();
        if (raw.indexOf('+') === 0) {
            return raw.replace(/[^\d+]/g, '');
        }
        var dial = String(country.dialCode);
        var np = country.nationalPrefix;
        var digits = digitsOnly(raw);
        if (!digits) {
            return '';
        }
        if (np && digits.length > np.length && digits.substring(0, np.length) === np) {
            digits = digits.substring(np.length);
        }
        return '+' + dial + digits;
    }

    function phoneValidationMessage(code) {
        var key = String(code);
        if (Object.prototype.hasOwnProperty.call(phoneMsgs, key) && phoneMsgs[key]) {
            return phoneMsgs[key];
        }
        return phoneMsgs.generic || '';
    }

    function initPhoneWrap(wrap) {
        var prefix = wrap.getAttribute('data-fund-phone-prefix');
        if (!prefix || phoneInstances[prefix]) {
            return;
        }
        var input = wrap.querySelector('[data-fund-phone-input]');
        var countryInput = wrap.querySelector('[data-fund-phone-country]');
        var errorEl = wrap.querySelector('.project-fund-form__field-error');
        var intlTelInit = typeof window !== 'undefined' ? window.intlTelInput : null;
        if (!input || !countryInput || typeof intlTelInit !== 'function') {
            return;
        }
        try {
            var iti = intlTelInit(input, {
                initialCountry: 'mg',
                separateDialCode: true,
                autoPlaceholder: 'aggressive',
                placeholderNumberType: 'MOBILE',
                loadUtils: function () {
                    return import(ITI_UTILS_URL);
                },
            });
            function syncCountry() {
                var country = iti.getSelectedCountryData();
                if (country && country.dialCode) {
                    countryInput.value = '+' + country.dialCode;
                }
            }
            syncCountry();
            input.addEventListener('countrychange', function () {
                syncCountry();
                if (errorEl) {
                    errorEl.hidden = true;
                }
            });
            input.addEventListener('input', function () {
                if (errorEl) {
                    errorEl.hidden = true;
                }
                clearErrors();
            });
            phoneInstances[prefix] = { iti: iti, input: input, errorEl: errorEl, syncCountry: syncCountry };
            if (iti.promise && typeof iti.promise.then === 'function') {
                iti.promise.then(syncCountry).catch(function () {});
            }
        } catch (eInit) {
            /* ignore */
        }
    }

    function validateActivePhone() {
        var t = activeType();
        var inst = phoneInstances[t];
        if (!inst || !inst.input.value.replace(/\s/g, '').trim()) {
            if (inst && inst.errorEl) {
                inst.errorEl.textContent = fieldMsgs.phone_required || phoneMsgs.generic || '';
                inst.errorEl.hidden = false;
            }
            return false;
        }
        var utils = window.intlTelInput && window.intlTelInput.utils ? window.intlTelInput.utils : null;
        if (!utils) {
            return true;
        }
        var full = buildE164ForValidation(inst.iti, inst.input);
        var iso = inst.iti.getSelectedCountryData().iso2;
        if (!utils.isValidNumber(full, iso, null)) {
            var code = utils.getValidationError(full, iso);
            if (inst.errorEl) {
                inst.errorEl.textContent = phoneValidationMessage(code);
                inst.errorEl.hidden = false;
            }
            return false;
        }
        inst.syncCountry();
        return true;
    }

    initMaterialItemsList();
    markRequiredFields();
    syncPanels(false);
    if (errorsBox && errorsBox.querySelector('li')) {
        errorsBox.removeAttribute('hidden');
    }

    var phoneWraps = form.querySelectorAll('[data-fund-phone-wrap]');
    for (var w = 0; w < phoneWraps.length; w++) {
        initPhoneWrap(phoneWraps[w]);
    }

    bindValidateInputs();

    for (var r = 0; r < radios.length; r++) {
        radios[r].addEventListener('change', function () {
            syncPanels(true);
        });
    }

    form.addEventListener('submit', function (ev) {
        syncPanels(false);
        var panelOk = validateActivePanel();
        var phoneOk = validateActivePhone();
        if (!panelOk || !phoneOk) {
            ev.preventDefault();
            syncClientErrorsSummary();
            var firstErr = form.querySelector('.project-fund-form__field-error:not([hidden])');
            if (firstErr && typeof firstErr.scrollIntoView === 'function') {
                firstErr.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
            return;
        }
        if (errorsBox) {
            errorsBox.hidden = true;
            errorsBox.innerHTML = '';
        }
    });

    for (var o = 0; o < openTriggers.length; o++) {
        openTriggers[o].addEventListener('click', function (ev) {
            ev.preventDefault();
            openModal();
        });
    }

    for (var c = 0; c < closeTriggers.length; c++) {
        closeTriggers[c].addEventListener('click', function () {
            closeModal();
        });
    }

    if (modal) {
        modal.addEventListener('cancel', function (ev) {
            ev.preventDefault();
            closeModal();
        });
        modal.addEventListener('click', function (ev) {
            if (ev.target === modal) {
                closeModal();
            }
        });
    }

    if (modal && modal.hasAttribute('data-fund-modal-autopen')) {
        openModal();
    } else if (window.location.hash === '#project-fund') {
        openModal();
    }
})();
