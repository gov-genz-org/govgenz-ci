(() => {
    const cfg = document.getElementById('pp-budget-live');
    const amountEl = document.getElementById('pp-budget-amount');
    const scaleEl = document.getElementById('pp-budget-scale');
    const previewEl = document.getElementById('pp-budget-preview');
    const localeSelect = document.getElementById('pp-locale');

    if (!cfg || !amountEl || !scaleEl || !previewEl) {
        return;
    }

    /** @type {Record<string, Record<string, string>>} */
    let allSuffixes = {};
    try {
        allSuffixes = JSON.parse(cfg.dataset.suffixes || '{}');
    } catch (e) {
        allSuffixes = {};
    }

    let locale = cfg.dataset.locale === 'en' ? 'en' : 'fr';

    function currentSuffixes() {
        return allSuffixes[locale] || allSuffixes.fr || {
            ariary: 'Ar',
            thousand: 'k Ar',
            million: 'M Ar',
            billion: 'Mds Ar',
        };
    }

    /**
     * @param {number} n
     * @param {number} dec
     */
    function formatNum(n, dec) {
        const fixed = n.toFixed(dec);
        const parts = fixed.split('.');
        let intPart = parts[0];
        const frac = parts[1] || '';

        if (locale === 'en') {
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return dec > 0 && frac !== '' ? intPart + '.' + frac : intPart;
        }

        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        if (dec > 0 && frac !== '') {
            return intPart + ',' + frac;
        }

        return intPart;
    }

    function updatePreview() {
        const suffixes = currentSuffixes();
        const raw = String(amountEl.value || '').trim().replace(',', '.');
        if (raw === '' || Number.isNaN(Number(raw)) || Number(raw) <= 0) {
            previewEl.textContent = '—';

            return;
        }

        const amount = Number(raw);
        const dec = amount % 1 === 0 ? 0 : 2;
        const num = formatNum(amount, dec);
        const scale = scaleEl.value || 'million';
        let suffix = suffixes.million || 'M Ar';
        if (scale === 'ariary') {
            suffix = suffixes.ariary || 'Ar';
        } else if (scale === 'thousand') {
            suffix = suffixes.thousand || 'k Ar';
        } else if (scale === 'billion') {
            suffix = suffixes.billion || 'Mds Ar';
        }

        previewEl.textContent = num + ' ' + suffix;
    }

    if (localeSelect && localeSelect.tagName === 'SELECT') {
        localeSelect.addEventListener('change', () => {
            locale = localeSelect.value === 'en' ? 'en' : 'fr';
            updatePreview();
        });
    }

    amountEl.addEventListener('input', updatePreview);
    amountEl.addEventListener('change', updatePreview);
    scaleEl.addEventListener('change', updatePreview);
})();
