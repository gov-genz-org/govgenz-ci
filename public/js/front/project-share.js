(function () {
    var root = document.querySelector('[data-project-share]');
    if (!root) {
        return;
    }

    var urlEl = root.querySelector('[data-project-share-url]');
    var copyBtn = root.querySelector('[data-project-share-copy]');
    var socialLinks = root.querySelectorAll('[data-project-share-social]');
    var copiedLabel = root.getAttribute('data-share-copied') || 'Copied';
    var tiktokDoneLabel = root.getAttribute('data-share-tiktok-done') || copiedLabel;
    var copyDefaultLabel = copyBtn ? (copyBtn.getAttribute('aria-label') || 'Copy') : 'Copy';
    var shareUrl = (root.getAttribute('data-share-url') || '').trim();
    var shareTitle = (root.getAttribute('data-share-title') || '').trim();
    var shareText = (root.getAttribute('data-share-text') || '').trim();

    if (shareUrl === '' && urlEl) {
        shareUrl = String(urlEl.textContent || '').trim();
    }

    if (shareText === '' && shareTitle !== '' && shareUrl !== '') {
        shareText = shareTitle + ' — ' + shareUrl;
    }

    function isMobileDevice() {
        return /Android|iPhone|iPad|iPod|Mobile|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function isAndroidDevice() {
        return /Android/i.test(navigator.userAgent);
    }

    function isIosDevice() {
        return /iPhone|iPad|iPod/i.test(navigator.userAgent);
    }

    function usesWebSharerWithUrl(network) {
        return network === 'facebook' || network === 'linkedin' || network === 'x';
    }

    function prefersNativeShare(network) {
        return network === 'facebook' || network === 'linkedin' || network === 'x' || network === 'tiktok';
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

    function copyText(text, onSuccess) {
        if (text === '') {
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(onSuccess).catch(function () {
                fallbackCopyText(text, onSuccess);
            });
            return;
        }

        fallbackCopyText(text, onSuccess);
    }

    function fallbackCopyText(text, onSuccess) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {
            ok = false;
        }
        document.body.removeChild(ta);
        if (ok && onSuccess) {
            onSuccess();
        }
    }

    function copyUrl() {
        if (shareUrl === '') {
            return;
        }

        selectUrlText();
        copyText(shareUrl, showCopyFeedback);
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

    function showTiktokFeedback() {
        if (!copyBtn) {
            return;
        }
        copyBtn.classList.add('is-copied');
        copyBtn.setAttribute('aria-label', tiktokDoneLabel);
        copyBtn.setAttribute('title', tiktokDoneLabel);
        window.setTimeout(function () {
            copyBtn.classList.remove('is-copied');
            copyBtn.setAttribute('aria-label', copyDefaultLabel);
            copyBtn.setAttribute('title', copyDefaultLabel);
        }, 2200);
    }

    function tryNativeShare() {
        if (!navigator.share || shareUrl === '') {
            return Promise.resolve(false);
        }

        var payload = { url: shareUrl };
        if (shareTitle !== '') {
            payload.title = shareTitle;
        }
        if (shareText !== '') {
            payload.text = shareText;
        }

        return navigator.share(payload).then(function () {
            return true;
        }).catch(function (err) {
            if (err && err.name === 'AbortError') {
                return true;
            }
            return false;
        });
    }

    function openInBrowserTab(href) {
        if (href === '') {
            return;
        }
        var opened = window.open(href, '_blank', 'noopener,noreferrer');
        if (!opened && isMobileDevice()) {
            window.location.assign(href);
        }
    }

    function tryAppDeepLink(appHref, webHref) {
        if (appHref === '') {
            return;
        }

        var fallbackTimer = window.setTimeout(function () {
            if (webHref !== '') {
                openInBrowserTab(webHref);
            }
        }, 900);

        var clearFallback = function () {
            window.clearTimeout(fallbackTimer);
        };

        window.addEventListener('pagehide', clearFallback, { once: true });
        window.addEventListener('blur', clearFallback, { once: true });

        if (isAndroidDevice() && appHref.indexOf('intent://') === 0) {
            window.location.href = appHref;
            return;
        }

        window.location.href = appHref;
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
        return (link.getAttribute('href') || '').trim();
    }

    function resolveAppHref(link) {
        if (isAndroidDevice()) {
            var androidHref = (link.getAttribute('data-share-href-android') || '').trim();
            if (androidHref !== '') {
                return androidHref;
            }
        }

        return (link.getAttribute('data-share-href-app') || '').trim();
    }

    function shareTiktok() {
        var text = shareText !== '' ? shareText : shareUrl;
        if (text === '') {
            return;
        }

        tryNativeShare().then(function (shared) {
            if (shared) {
                return;
            }

            copyText(text, function () {
                showTiktokFeedback();
                var appHref = 'snssdk1180://';
                if (isIosDevice()) {
                    appHref = 'tiktok://';
                }
                window.setTimeout(function () {
                    window.location.href = appHref;
                }, 350);
            });
        });
    }

    function openShareLink(link) {
        var network = link.getAttribute('data-project-share-social') || '';

        if (network === 'email') {
            window.location.href = (link.getAttribute('href') || '').trim();
            return;
        }

        if (network === 'whatsapp') {
            window.location.href = resolveWhatsappHref(link);
            return;
        }

        if (network === 'tiktok') {
            shareTiktok();
            return;
        }

        if (usesWebSharerWithUrl(network)) {
            var webHref = resolveWebSharerHref(link);
            var appHref = resolveAppHref(link);

            if (isMobileDevice() && prefersNativeShare(network)) {
                tryNativeShare().then(function (shared) {
                    if (shared) {
                        return;
                    }
                    if (appHref !== '' && (isIosDevice() || isAndroidDevice())) {
                        tryAppDeepLink(appHref, webHref);
                        return;
                    }
                    openInBrowserTab(webHref);
                });
                return;
            }

            if (webHref !== '') {
                openInBrowserTab(webHref);
            }
            return;
        }

        var href = (link.getAttribute('href') || '').trim();
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
