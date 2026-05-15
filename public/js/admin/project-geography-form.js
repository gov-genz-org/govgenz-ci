(() => {
    var root = document.getElementById('pp-geo-picker');
    if (!root) {
        return;
    }

    var apiBase = (root.dataset.apiBase || '').replace(/\/$/, '');
    var catalogReady = root.dataset.catalogReady === '1';
    var nationalCb = document.getElementById('pp-geo-national');
    var cascade = document.getElementById('pp-geo-cascade');
    var selRegions = document.getElementById('pp-geo-regions');
    var selDistricts = document.getElementById('pp-geo-districts');
    var selCommunes = document.getElementById('pp-geo-communes');
    var selFokontany = document.getElementById('pp-geo-fokontany');
    var preview = document.getElementById('pp-geo-preview');
    var statusEl = document.getElementById('pp-geo-status');
    var initialEl = document.getElementById('pp-geo-initial');
    var labelsEl = document.getElementById('pp-geo-labels');

    if (!nationalCb || !selRegions || !selDistricts || !selCommunes || !selFokontany || !preview) {
        return;
    }

    var geoLabels = {
        regions: '{0} régions',
        districts: '{0} districts',
        communes: '{0} communes',
        fokontany: '{0} fokontany',
    };
    if (labelsEl && labelsEl.textContent) {
        try {
            geoLabels = JSON.parse(labelsEl.textContent);
        } catch (e) {
            geoLabels = geoLabels;
        }
    }

    var initial = {
        national: false,
        region_ids: [],
        district_ids: [],
        commune_ids: [],
        fokontany_ids: [],
    };
    if (initialEl && initialEl.textContent) {
        try {
            initial = JSON.parse(initialEl.textContent);
        } catch (e) {
            initial = initial;
        }
    }

    var restoring = false;

    function setStatus(msg, isError) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = msg || '';
        statusEl.classList.toggle('text-danger', !!isError);
    }

    function selectedValues(selectEl) {
        var out = [];
        if (!selectEl) {
            return out;
        }
        for (var i = 0; i < selectEl.options.length; i++) {
            var opt = selectEl.options[i];
            if (opt.selected && opt.value) {
                out.push(String(opt.value));
            }
        }
        return out;
    }

    function fillSelect(selectEl, items, selectedIds) {
        if (!selectEl) {
            return;
        }
        var keep = {};
        selectedIds.forEach(function (id) {
            keep[String(id)] = true;
        });

        selectEl.innerHTML = '';
        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = String(item.id);
            opt.textContent = String(item.name || '');
            if (keep[String(item.id)]) {
                opt.selected = true;
            }
            selectEl.appendChild(opt);
        });

        selectEl.disabled = items.length === 0;
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).then(function (res) {
            var ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                throw new Error('Réponse invalide (session expirée ?). Reconnectez-vous à l’admin.');
            }
            return res.json().then(function (data) {
                if (!res.ok && data && data.error) {
                    throw new Error(data.error);
                }
                return data;
            });
        });
    }

    function queryUrl(path, param, ids) {
        if (!ids.length) {
            return Promise.resolve({ ok: true, items: [] });
        }
        var q = ids.map(function (id) {
            return encodeURIComponent(param) + '[]=' + encodeURIComponent(id);
        }).join('&');
        return fetchJson(apiBase + '/' + path + '?' + q);
    }

    function regionsAlreadyInDom() {
        return selRegions.options.length > 0;
    }

    function loadRegions() {
        if (regionsAlreadyInDom()) {
            return Promise.resolve({ ok: true, items: [] });
        }
        if (!catalogReady || !apiBase) {
            return Promise.reject(new Error('Référentiel géo non importé (mdg:import-geo).'));
        }
        return fetchJson(apiBase + '/regions').then(function (regData) {
            if (!regData.ok || !Array.isArray(regData.items)) {
                throw new Error(regData.error || 'Impossible de charger les régions.');
            }
            fillSelect(selRegions, regData.items, []);
            return regData;
        });
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escAttr(s) {
        return escHtml(s);
    }

    function countSummary(level, n) {
        var tpl = geoLabels[level] || geoLabels.regions || '{0}';
        return tpl.replace('{0}', String(n));
    }

    function renderPreview(parts, level) {
        if (parts.length === 0) {
            preview.textContent = '—';
            return;
        }
        if (parts.length <= 2) {
            preview.textContent = parts.join(' & ');
            return;
        }
        var summary = countSummary(level, parts.length);
        var full = parts.join(' & ');
        preview.innerHTML =
            '<span class="project-geo project-geo--has-tip">' +
            '<span class="project-geo__summary">' +
            escHtml(summary) +
            '</span>' +
            '<span class="project-geo__info" tabindex="0" role="button" aria-label="' +
            escAttr(full) +
            '">i</span>' +
            '<span class="project-geo__tip" role="tooltip">' +
            escHtml(full) +
            '</span>' +
            '</span>';
    }

    function updatePreview() {
        if (nationalCb.checked) {
            preview.textContent = 'National';
            return;
        }
        var parts = [];
        var level = 'regions';
        var fok = selectedValues(selFokontany);
        var com = selectedValues(selCommunes);
        var dis = selectedValues(selDistricts);
        var reg = selectedValues(selRegions);

        if (fok.length) {
            level = 'fokontany';
            fok.forEach(function (id) {
                var opt = selFokontany.querySelector('option[value="' + id + '"]');
                if (opt) {
                    parts.push(opt.textContent);
                }
            });
        } else if (com.length) {
            level = 'communes';
            com.forEach(function (id) {
                var opt = selCommunes.querySelector('option[value="' + id + '"]');
                if (opt) {
                    parts.push(opt.textContent);
                }
            });
        } else if (dis.length) {
            level = 'districts';
            dis.forEach(function (id) {
                var opt = selDistricts.querySelector('option[value="' + id + '"]');
                if (opt) {
                    parts.push(opt.textContent);
                }
            });
        } else if (reg.length) {
            level = 'regions';
            reg.forEach(function (id) {
                var opt = selRegions.querySelector('option[value="' + id + '"]');
                if (opt) {
                    parts.push(opt.textContent);
                }
            });
        }

        renderPreview(parts, level);
    }

    function toggleNational() {
        var on = nationalCb.checked;
        if (cascade) {
            cascade.classList.toggle('d-none', on);
        }
        if (on) {
            selRegions.selectedIndex = -1;
            selDistricts.innerHTML = '';
            selCommunes.innerHTML = '';
            selFokontany.innerHTML = '';
            selRegions.disabled = true;
            selDistricts.disabled = true;
            selCommunes.disabled = true;
            selFokontany.disabled = true;
            updatePreview();
            return;
        }

        selRegions.disabled = !catalogReady;
        if (!regionsAlreadyInDom()) {
            setStatus('Chargement des régions…');
            loadRegions()
                .then(function () {
                    setStatus('');
                    restoreChildLevels();
                })
                .catch(function (err) {
                    setStatus(err.message || String(err), true);
                });
        } else {
            restoreChildLevels();
        }
        updatePreview();
    }

    function onRegionsChange() {
        if (restoring || nationalCb.checked) {
            return;
        }
        var regionIds = selectedValues(selRegions);
        setStatus('Chargement des districts…');
        queryUrl('districts', 'region_ids', regionIds)
            .then(function (data) {
                if (!data.ok) {
                    throw new Error(data.error || 'Erreur districts');
                }
                var keepDistricts = selectedValues(selDistricts).filter(function (id) {
                    return data.items.some(function (it) {
                        return String(it.id) === String(id);
                    });
                });
                fillSelect(selDistricts, data.items, keepDistricts);
                return onDistrictsChange(true);
            })
            .then(function () {
                setStatus('');
                updatePreview();
            })
            .catch(function (err) {
                setStatus(err.message || String(err), true);
            });
    }

    function onDistrictsChange(fromCascade) {
        if (restoring || nationalCb.checked) {
            return Promise.resolve();
        }
        var districtIds = selectedValues(selDistricts);
        if (!fromCascade) {
            setStatus('Chargement des communes…');
        }
        return queryUrl('communes', 'district_ids', districtIds)
            .then(function (data) {
                if (!data.ok) {
                    throw new Error(data.error || 'Erreur communes');
                }
                var keepCommunes = selectedValues(selCommunes).filter(function (id) {
                    return data.items.some(function (it) {
                        return String(it.id) === String(id);
                    });
                });
                fillSelect(selCommunes, data.items, keepCommunes);
                return onCommunesChange(true);
            })
            .then(function () {
                if (!fromCascade) {
                    setStatus('');
                    updatePreview();
                }
            })
            .catch(function (err) {
                setStatus(err.message || String(err), true);
            });
    }

    function onCommunesChange(fromCascade) {
        if (restoring || nationalCb.checked) {
            return Promise.resolve();
        }
        var communeIds = selectedValues(selCommunes);
        if (!fromCascade) {
            setStatus('Chargement des fokontany…');
        }
        return queryUrl('fokontany', 'commune_ids', communeIds)
            .then(function (data) {
                if (!data.ok) {
                    throw new Error(data.error || 'Erreur fokontany');
                }
                var keepFok = selectedValues(selFokontany).filter(function (id) {
                    return data.items.some(function (it) {
                        return String(it.id) === String(id);
                    });
                });
                fillSelect(selFokontany, data.items, keepFok);
                if (!fromCascade) {
                    setStatus('');
                    updatePreview();
                }
            })
            .catch(function (err) {
                setStatus(err.message || String(err), true);
            });
    }

    function restoreChildLevels() {
        if (nationalCb.checked) {
            return;
        }
        restoring = true;
        setStatus('Chargement des niveaux…');
        queryUrl('districts', 'region_ids', initial.region_ids || [])
            .then(function (disData) {
                if (!disData.ok) {
                    throw new Error(disData.error || 'Erreur');
                }
                fillSelect(selDistricts, disData.items, initial.district_ids || []);
                return queryUrl('communes', 'district_ids', initial.district_ids || []);
            })
            .then(function (comData) {
                if (!comData.ok) {
                    throw new Error(comData.error || 'Erreur');
                }
                fillSelect(selCommunes, comData.items, initial.commune_ids || []);
                return queryUrl('fokontany', 'commune_ids', initial.commune_ids || []);
            })
            .then(function (fokData) {
                if (fokData && fokData.ok) {
                    fillSelect(selFokontany, fokData.items, initial.fokontany_ids || []);
                }
                restoring = false;
                setStatus('');
                updatePreview();
            })
            .catch(function (err) {
                restoring = false;
                setStatus(err.message || String(err), true);
            });
    }

    function restoreCascade() {
        if (initial.national) {
            nationalCb.checked = true;
            toggleNational();
            return;
        }

        if (!catalogReady) {
            setStatus('Import géographique requis (php spark mdg:import-geo).', true);
            return;
        }

        selRegions.disabled = false;

        if (regionsAlreadyInDom()) {
            restoreChildLevels();
            return;
        }

        setStatus('Chargement des régions…');
        loadRegions()
            .then(function () {
                syncSelectSelection(selRegions, initial.region_ids || []);
                restoreChildLevels();
            })
            .catch(function (err) {
                setStatus(err.message || String(err), true);
            });
    }

    function syncSelectSelection(selectEl, ids) {
        if (!selectEl || !ids || !ids.length) {
            return;
        }
        var wanted = {};
        ids.forEach(function (id) {
            wanted[String(id)] = true;
        });
        for (var i = 0; i < selectEl.options.length; i++) {
            var opt = selectEl.options[i];
            opt.selected = !!wanted[String(opt.value)];
        }
    }

    function enableGeoFieldsForSubmit() {
        selRegions.disabled = false;
        selDistricts.disabled = false;
        selCommunes.disabled = false;
        selFokontany.disabled = false;
    }

    nationalCb.addEventListener('change', toggleNational);
    selRegions.addEventListener('change', onRegionsChange);
    selDistricts.addEventListener('change', function () {
        onDistrictsChange(false);
    });
    selCommunes.addEventListener('change', function () {
        onCommunesChange(false);
    });
    selFokontany.addEventListener('change', updatePreview);
    [selRegions, selDistricts, selCommunes, selFokontany].forEach(function (sel) {
        sel.addEventListener('mouseup', function () {
            window.setTimeout(updatePreview, 0);
        });
    });

    var form = root.closest('form');
    if (form) {
        form.addEventListener('submit', enableGeoFieldsForSubmit);
    }

    restoreCascade();

    if (regionsAlreadyInDom() && !initial.national) {
        syncSelectSelection(selRegions, initial.region_ids || []);
        updatePreview();
    }
})();
