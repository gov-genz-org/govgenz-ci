(function () {
    var pad2 = function (n) {
        return String(n).padStart(2, '0');
    };

    var formatLocal = function (d) {
        return pad2(d.getDate()) + '-' + pad2(d.getMonth() + 1) + '-' + d.getFullYear()
            + ' ' + pad2(d.getHours()) + ':' + pad2(d.getMinutes()) + ':' + pad2(d.getSeconds());
    };

    var formatDatetimeLocalValue = function (d) {
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate())
            + 'T' + pad2(d.getHours()) + ':' + pad2(d.getMinutes());
    };

    var rememberClientTimezone = function () {
        var tz = '';
        try {
            tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        } catch (e) {
            tz = '';
        }
        if (!tz) {
            return;
        }
        document.cookie = 'admin_client_tz=' + encodeURIComponent(tz)
            + '; path=/; max-age=31536000; SameSite=Lax';
    };

    var applyDisplayTimes = function () {
        document.querySelectorAll('time.js-admin-datetime').forEach(function (el) {
            var iso = el.getAttribute('datetime');
            if (!iso) {
                return;
            }
            var d = new Date(iso);
            if (Number.isNaN(d.getTime())) {
                return;
            }
            el.textContent = formatLocal(d);
        });
    };

    var applyDatetimeInputs = function () {
        document.querySelectorAll('input[data-admin-datetime-utc]').forEach(function (input) {
            if (input.value) {
                return;
            }
            var iso = input.getAttribute('data-admin-datetime-utc');
            if (!iso) {
                return;
            }
            var d = new Date(iso);
            if (Number.isNaN(d.getTime())) {
                return;
            }
            input.value = formatDatetimeLocalValue(d);
        });
    };

    rememberClientTimezone();
    applyDisplayTimes();
    applyDatetimeInputs();
})();
