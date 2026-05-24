(function () {
  var root = document.querySelector('.js-projects-program-root');
  if (!root) {
    return;
  }

  var endpoint = root.getAttribute('data-filter-endpoint') || '';
  var csrfName = root.getAttribute('data-csrf-name') || '';
  var csrfHash = root.getAttribute('data-csrf-hash') || '';
  var csrfHeader = root.getAttribute('data-csrf-header') || 'X-CSRF-TOKEN';
  var ajaxErrorDefault = root.getAttribute('data-ajax-error') || '';
  var ajaxCsrfReload = root.getAttribute('data-ajax-csrf-reload') || ajaxErrorDefault;
  var metaEl = root.querySelector('.js-projects-grid-meta');
  var innerEl = root.querySelector('.js-projects-grid-inner');
  var errEl = root.querySelector('.js-projects-ajax-error');

  var statusState = [];
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

  function syncPillsFromServer(statusList, sectorList) {
    statusState = statusList.slice();
    sectorState = sectorList.slice();
    var pills = root.querySelectorAll('.js-projects-filter-pill');
    var pi;
    for (pi = 0; pi < pills.length; pi++) {
      var pill = pills[pi];
      var kind = pill.getAttribute('data-filter-kind');
      var val = pill.getAttribute('data-filter-value') || '';
      var active = false;
      if (kind === 'status') {
        active = statusState.indexOf(val) >= 0;
      } else if (kind === 'sector') {
        active = sectorState.indexOf(val) >= 0;
      }
      if (active) {
        pill.classList.add('projects-program-page__pill--active');
      } else {
        pill.classList.remove('projects-program-page__pill--active');
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
      status: statusState.slice(),
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
        var st = Array.isArray(data.pillStatus) ? data.pillStatus : [];
        var se = Array.isArray(data.pillSectors) ? data.pillSectors : [];
        syncPillsFromServer(st, se);
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
    if (t.closest('.js-projects-reset-filters')) {
      statusState = [];
      sectorState = [];
      syncPillsFromServer([], []);
      postFilters();
      return;
    }
    var pill = t.closest('.js-projects-filter-pill');
    if (!pill) {
      return;
    }
    var kind = pill.getAttribute('data-filter-kind');
    var val = pill.getAttribute('data-filter-value') || '';
    if (val === '') {
      return;
    }
    if (kind === 'status') {
      toggleInList(statusState, val);
    } else if (kind === 'sector') {
      toggleInList(sectorState, val);
    } else {
      return;
    }
    syncPillsFromServer(statusState, sectorState);
    postFilters();
  });
})();
