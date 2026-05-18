(function () {
    var root = document.querySelector('[data-project-share]');
    if (!root) {
        return;
    }

    var urlEl = root.querySelector('[data-project-share-url]');
    var copyBtn = root.querySelector('[data-project-share-copy]');
    var socialLinks = root.querySelectorAll('[data-project-share-social]');
    var copiedLabel = root.getAttribute('data-share-copied') || 'Copied';
    var copyDefaultLabel = copyBtn ? (copyBtn.getAttribute('aria-label') || 'Copy') : 'Copy';
    var shareUrl = (root.getAttribute('data-share-url') || '').trim();

    if (shareUrl === '' && urlEl) {
        shareUrl = String(urlEl.textContent || '').trim();
    }

    function isMobileDevice() {
        return /Android|iPhone|iPad|iPod|Mobile|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function isAndroidDevice() {
        return /Android/i.test(navigator.userAgent);
    }

    function usesWebSharerWithUrl(network) {
        return network === 'facebook' || network === 'linkedin' || network === 'x';
    }

    function selectUrlText() {
        if (!urlEl || !window.getSelection) {
            return;
        }
        var range = document.createRange();
        range.selectNodeContents(urlEl);
        var selection = window.getSelection();
        if (!selection) {
            return;
        }
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function copyUrl() {
        if (shareUrl === '') {
            return;
        }

        selectUrlText();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shareUrl).then(showCopyFeedback).catch(fallbackCopyUrl);
            return;
        }

        fallbackCopyUrl();
    }

    function fallbackCopyUrl() {
        if (!urlEl) {
            return;
        }
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {
            ok = false;
        }
        if (ok) {
            showCopyFeedback();
        }
    }

    function showCopyFeedback() {
        if (!copyBtn) {
            return;
        }
        copyBtn.classList.add('is-copied');
        copyBtn.setAttribute('aria-label', copiedLabel);
        copyBtn.setAttribute('title', copiedLabel);
        window.setTimeout(function () {
            copyBtn.classList.remove('is-copied');
            copyBtn.setAttribute('aria-label', copyDefaultLabel);
            copyBtn.setAttribute('title', copyDefaultLabel);
        }, 1600);
    }

    function resolveWhatsappHref(link) {
        if (isAndroidDevice()) {
            var androidHref = (link.getAttribute('data-share-href-android') || '').trim();
            if (androidHref !== '') {
                return androidHref;
            }
        }

        return (link.getAttribute('data-share-href-app') || link.getAttribute('href') || '').trim();
    }

    function resolveWebSharerHref(link) {
        if (isMobileDevice()) {
            var mobileHref = (link.getAttribute('data-share-href-mobile') || '').trim();
            if (mobileHref !== '') {
                return mobileHref;
            }
        }

        return (link.getAttribute('href') || '').trim();
    }

    function openShareLink(link) {
        var network = link.getAttribute('data-project-share-social') || '';
        var href;

        if (network === 'email') {
            window.location.href = (link.getAttribute('href') || '').trim();
            return;
        }

        if (network === 'whatsapp') {
            href = resolveWhatsappHref(link);
            window.location.href = href;
            return;
        }

        if (usesWebSharerWithUrl(network)) {
            href = resolveWebSharerHref(link);
            if (href === '') {
                return;
            }
            if (isMobileDevice()) {
                window.location.href = href;
            } else {
                window.open(href, '_blank', 'noopener,noreferrer');
            }
            return;
        }

        href = (link.getAttribute('href') || '').trim();
        if (href !== '') {
            window.location.href = href;
        }
    }

    function handleSocialClick(ev) {
        var link = ev.currentTarget;
        if (!link) {
            return;
        }

        ev.preventDefault();
        openShareLink(link);
    }

    socialLinks.forEach(function (link) {
        link.removeAttribute('target');
        link.addEventListener('click', handleSocialClick);
    });

    if (urlEl) {
        urlEl.addEventListener('click', selectUrlText);
        urlEl.addEventListener('focus', selectUrlText);
    }

    if (copyBtn) {
        copyBtn.addEventListener('click', copyUrl);
    }
})();
