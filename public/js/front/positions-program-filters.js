(function () {
  var root = document.querySelector('.js-positions-program-root');
  if (!root) {
    return;
  }

  var endpoint = root.getAttribute('data-filter-endpoint') || '';
  var csrfName = root.getAttribute('data-csrf-name') || '';
  var csrfHash = root.getAttribute('data-csrf-hash') || '';
  var csrfHeader = root.getAttribute('data-csrf-header') || 'X-CSRF-TOKEN';
  var ajaxErrorDefault = root.getAttribute('data-ajax-error') || '';
  var ajaxCsrfReload = root.getAttribute('data-ajax-csrf-reload') || ajaxErrorDefault;
  var metaEl = root.querySelector('.js-positions-grid-meta');
  var innerEl = root.querySelector('.js-positions-grid-inner');
  var errEl = root.querySelector('.js-positions-ajax-error');

  var typeState = [];
  var sectorState = [];
  var busy = false;

  function toggleInList(arr, value) {
    var i = arr.indexOf(value);
    if (i >= 0) {
      arr.splice(i, 1);
    } else {
      arr.push(value);
    }
  }

  function syncPillsFromServer(typeList, sectorList) {
    typeState = typeList.slice();
    sectorState = sectorList.slice();
    var pills = root.querySelectorAll('.js-positions-filter-pill');
    var pi;
    for (pi = 0; pi < pills.length; pi++) {
      var pill = pills[pi];
      var kind = pill.getAttribute('data-filter-kind');
      var val = pill.getAttribute('data-filter-value') || '';
      var active = false;
      if (kind === 'type') {
        active = typeState.indexOf(val) >= 0;
      } else if (kind === 'sector') {
        active = sectorState.indexOf(val) >= 0;
      }
      if (active) {
        pill.classList.add('positions-program-page__pill--active');
      } else {
        pill.classList.remove('positions-program-page__pill--active');
      }
    }
  }

  function setError(msg) {
    if (!errEl) {
      return;
    }
    if (msg) {
      errEl.textContent = msg;
      errEl.hidden = false;
    } else {
      errEl.textContent = '';
      errEl.hidden = true;
    }
  }

  function postFilters() {
    if (busy || !metaEl || !innerEl || endpoint === '' || csrfName === '') {
      return;
    }
    busy = true;
    setError('');

    var body = {
      type: typeState.slice(),
      sector: sectorState.slice(),
    };
    if (csrfName !== '' && csrfHash !== '') {
      body[csrfName] = csrfHash;
    }

    var headers = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    };
    if (csrfHash !== '') {
      headers[csrfHeader] = csrfHash;
    }

    fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: headers,
      body: JSON.stringify(body),
    })
      .then(function (res) {
        if (res.status === 403) {
          throw new Error('csrf');
        }
        if (!res.ok) {
          throw new Error('http');
        }
        return res.json();
      })
      .then(function (data) {
        if (!data || !data.ok) {
          throw new Error('payload');
        }
        if (typeof data.csrfHash === 'string' && data.csrfHash !== '') {
          csrfHash = data.csrfHash;
          root.setAttribute('data-csrf-hash', data.csrfHash);
        }
        if (typeof data.gridMetaHtml === 'string') {
          metaEl.innerHTML = data.gridMetaHtml;
        }
        if (typeof data.gridInnerHtml === 'string') {
          innerEl.innerHTML = data.gridInnerHtml;
        }
        var ty = Array.isArray(data.pillTypes) ? data.pillTypes : [];
        var se = Array.isArray(data.pillSectors) ? data.pillSectors : [];
        syncPillsFromServer(ty, se);
      })
      .catch(function (err) {
        if (err && err.message === 'csrf') {
          setError(ajaxCsrfReload);
        } else {
          setError(ajaxErrorDefault);
        }
      })
      .finally(function () {
        busy = false;
      });
  }

  root.addEventListener('click', function (e) {
    var t = e.target;
    if (!t || !t.closest) {
      return;
    }
    if (t.closest('.js-positions-reset-filters')) {
      typeState = [];
      sectorState = [];
      syncPillsFromServer([], []);
      postFilters();
      return;
    }
    var pill = t.closest('.js-positions-filter-pill');
    if (!pill) {
      return;
    }
    var kind = pill.getAttribute('data-filter-kind');
    var val = pill.getAttribute('data-filter-value') || '';
    if (val === '') {
      return;
    }
    if (kind === 'type') {
      toggleInList(typeState, val);
    } else if (kind === 'sector') {
      toggleInList(sectorState, val);
    } else {
      return;
    }
    syncPillsFromServer(typeState, sectorState);
    postFilters();
  });
})();
