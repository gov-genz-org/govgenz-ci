(() => {
    const cfgEl = document.getElementById('admin-tinymce-config');
    if (!cfgEl) {
        return;
    }
    let cfg;
    try {
        cfg = JSON.parse(cfgEl.textContent || '{}');
    } catch {
        return;
    }
    const uploadUrl = cfg.uploadUrl;
    const mediaJsonUrl = cfg.mediaJsonUrl;
    const csrfName = cfg.csrfName;
    const pageUrlContact = cfg.pageUrlContact || '';
    const pageUrlPress = cfg.pageUrlPress || '';
    if (!uploadUrl || !mediaJsonUrl || !csrfName || typeof tinymce === 'undefined') {
        return;
    }

    function htmlAttr(val) {
        return escapeHtmlAttr(String(val));
    }

    function tplEtudeSection(contactHref, pressHref) {
        return (
            '<section class="cms-section" aria-labelledby="etude-heading">' +
            '<header>' +
            '<p class="cms-kicker">Étude jeunesse 2026</p>' +
            '<h2 id="etude-heading" class="section__title">Les chiffres qui nous portent</h2>' +
            '<p class="cms-lead muted">Une base chiffrée pour comprendre le poids démographique de la jeunesse malgache et les leviers d’action à activer.</p>' +
            '</header>' +
            '<dl class="cms-metrics">' +
            '<div><dt>72,6 %</dt><dd>de la population a 0–30 ans</dd></div>' +
            '<div><dt>75,2 %</dt><dd>de pauvreté nationale en 2022</dd></div>' +
            '<div><dt>47 %</dt><dd>des 5–17 ans concernés par le travail des enfants</dd></div>' +
            '<div><dt>13 %</dt><dd>de fréquentation au secondaire second cycle</dd></div>' +
            '</dl>' +
            '<p class="muted">L’étude complète couvre les 24 régions, l’éducation, la santé, l’emploi, la diaspora et les leviers d’action — à adapter selon vos publications officielles.</p>' +
            '<p class="cms-actions">' +
            '<a class="cms-btn cms-btn--secondary" href="' +
            htmlAttr(contactHref) +
            '">Demander l’étude complète</a> ' +
            '<a class="cms-btn cms-btn--primary" href="' +
            htmlAttr(pressHref) +
            '">Voir les communiqués</a>' +
            '</p>' +
            '</section>'
        );
    }

    function tplSectionSimple() {
        return (
            '<section class="cms-section">' +
            '<header>' +
            '<p class="cms-kicker">Sur-titre de section</p>' +
            '<h2 class="section__title">Titre principal</h2>' +
            '<p class="cms-lead muted">Chapô : une ou deux phrases pour introduire le bloc.</p>' +
            '</header>' +
            '<p>Corps du bloc : paragraphes, listes, images…</p>' +
            '</section>'
        );
    }

    function tplSectionHeaderGovgenz() {
        return (
            '<div class="section__header">' +
            '<div class="section__overline">SUR-TITRE</div>' +
            '<h2 class="section__title">Titre principal</h2>' +
            '<p class="section__lead">Chapô : une ou deux phrases pour introduire la rubrique.</p>' +
            '</div>'
        );
    }

    function csrfVal() {
        const el = document.querySelector(`form input[name="${csrfName}"]`);
        return el ? el.value : '';
    }

    function applyCsrf(hash) {
        if (!hash) {
            return;
        }
        const h = document.querySelector(`input[name="${csrfName}"]`);
        if (h) {
            h.value = hash;
        }
    }

    function escapeHtmlAttr(val) {
        return String(val)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;');
    }

    const modalEl = document.getElementById('tinymce-media-modal');
    let modalInst = null;

    function mediaModal() {
        if (!modalInst && modalEl && window.bootstrap) {
            modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        return modalInst;
    }

    function jsonUrlForPage(page) {
        const u = new URL(mediaJsonUrl, window.location.origin);
        u.searchParams.set('page', String(page));
        return u.toString();
    }

    function bindGridPick(editor) {
        const grid = document.getElementById('tinymce-media-grid');
        if (!grid) {
            return;
        }
        grid.onclick = (ev) => {
            const btn = ev.target.closest('.tinymce-pick-media');
            if (!btn) {
                return;
            }
            const url = btn.getAttribute('data-url') || '';
            if (!url) {
                return;
            }
            editor.insertContent(
                `<p><img src="${escapeHtmlAttr(url)}" alt="À compléter : courte description de l’image (accessibilité)" /></p>`,
            );
            mediaModal().hide();
        };
    }

    function renderMediaPickerPage(editor, payload) {
        const grid = document.getElementById('tinymce-media-grid');
        const pagerEl = document.getElementById('tinymce-media-pager');
        const emptyMsg = document.getElementById('tinymce-media-empty');
        if (!grid || !modalEl) {
            return;
        }

        const rawItems = Array.isArray(payload.items) ? payload.items : [];
        const images = rawItems.filter((i) => i.mime && String(i.mime).indexOf('image/') === 0);
        const page = Math.max(1, Number(payload.page) || 1);
        const totalPages = Math.max(1, Number(payload.totalPages) || 1);
        const total = Number(payload.total) || 0;

        grid.innerHTML = '';

        if (images.length === 0) {
            emptyMsg.textContent =
                total === 0
                    ? 'Aucune image dans la médiathèque. Envoyez des fichiers depuis « Médias » puis rechargez cette fenêtre.'
                    : 'Aucune image sur cette page.';
            emptyMsg.classList.remove('d-none');
        } else {
            emptyMsg.classList.add('d-none');
            images.forEach((it) => {
                const url = String(it.url || '');
                const col = document.createElement('div');
                col.className = 'col-6 col-md-4';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-light border w-100 p-2 tinymce-pick-media';
                btn.setAttribute('data-url', url);
                const img = document.createElement('img');
                img.src = url;
                img.className = 'img-fluid rounded w-100';
                img.alt = String(it.name || '');
                img.loading = 'lazy';
                btn.appendChild(img);
                col.appendChild(btn);
                grid.appendChild(col);
            });
        }

        bindGridPick(editor);

        if (pagerEl) {
            pagerEl.innerHTML = '';
            if (total === 0) {
                pagerEl.classList.add('d-none');
                pagerEl.classList.remove('d-flex');
            } else {
                pagerEl.classList.remove('d-none');
                pagerEl.classList.add('d-flex');
                const meta = document.createElement('span');
                meta.className = 'text-muted';
                meta.textContent =
                    totalPages > 1
                        ? `Page ${page} / ${totalPages} · ${total} fichier(s)`
                        : `${total} fichier(s)`;
                pagerEl.appendChild(meta);

                if (totalPages > 1) {
                    const btns = document.createElement('span');
                    btns.className = 'd-inline-flex gap-2';
                    const prev = document.createElement('button');
                    prev.type = 'button';
                    prev.className = 'btn btn-sm btn-outline-secondary tinymce-media-prev';
                    prev.textContent = 'Précédent';
                    prev.disabled = page <= 1;
                    prev.addEventListener('click', () => loadMediaPickerPage(editor, page - 1));
                    const next = document.createElement('button');
                    next.type = 'button';
                    next.className = 'btn btn-sm btn-outline-secondary tinymce-media-next';
                    next.textContent = 'Suivant';
                    next.disabled = page >= totalPages;
                    next.addEventListener('click', () => loadMediaPickerPage(editor, page + 1));
                    btns.appendChild(prev);
                    btns.appendChild(next);
                    pagerEl.appendChild(btns);
                }
            }
        }
    }

    function loadMediaPickerPage(editor, pageNum) {
        const grid = document.getElementById('tinymce-media-grid');
        const loading = document.getElementById('tinymce-media-loading');
        const emptyMsg = document.getElementById('tinymce-media-empty');
        if (!grid || !modalEl || !loading || !emptyMsg) {
            return;
        }
        grid.innerHTML = '';
        loading.classList.remove('d-none');
        emptyMsg.classList.add('d-none');

        fetch(jsonUrlForPage(pageNum), { credentials: 'same-origin' })
            .then((r) => r.json())
            .then((data) => {
                loading.classList.add('d-none');
                if (!data || !Array.isArray(data.items)) {
                    emptyMsg.textContent = 'Réponse invalide du serveur.';
                    emptyMsg.classList.remove('d-none');
                    return;
                }
                renderMediaPickerPage(editor, data);
            })
            .catch(() => {
                loading.classList.add('d-none');
                emptyMsg.textContent = 'Impossible de charger la liste des médias.';
                emptyMsg.classList.remove('d-none');
            });
    }

    function openMediaPicker(editor) {
        const grid = document.getElementById('tinymce-media-grid');
        const loading = document.getElementById('tinymce-media-loading');
        const emptyMsg = document.getElementById('tinymce-media-empty');
        const pagerEl = document.getElementById('tinymce-media-pager');
        if (!grid || !modalEl || !loading || !emptyMsg) {
            return;
        }
        grid.innerHTML = '';
        if (pagerEl) {
            pagerEl.innerHTML = '';
            pagerEl.classList.add('d-none');
            pagerEl.classList.remove('d-flex');
        }
        loading.classList.remove('d-none');
        emptyMsg.classList.add('d-none');
        mediaModal().show();
        loadMediaPickerPage(editor, 1);
    }

    function htmlBeautifyOptions() {
        return {
            indent_size: 2,
            indent_char: ' ',
            max_preserve_newlines: 1,
            wrap_line_length: 100,
            end_with_newline: false,
            preserve_newlines: true,
            indent_inner_html: true,
            indent_scripts: 'normal',
            wrap_attributes: 'auto',
        };
    }

    /**
     * TinyMCE détruit les espacements dans le DOM : indenter via setContent ne change rien d’visible.
     * Le plugin « Code » affiche getContent({ source_view: true }) dans une textarea : on la reformate à l’ouverture.
     */
    function prettifyToxCodeTextarea(editor, deadlineMs) {
        var htmlB = typeof window.html_beautify === 'function' ? window.html_beautify : null;
        if (!htmlB) {
            if (editor.notificationManager) {
                editor.notificationManager.open({
                    text: 'Le script de formatage HTML n’a pas été chargé. Videz le cache du navigateur et rechargez la page.',
                    type: 'error',
                    timeout: 8000,
                });
            }
            return;
        }
        var opts = htmlBeautifyOptions();
        var endAt = Date.now() + (typeof deadlineMs === 'number' ? deadlineMs : 1200);

        function tryOnce() {
            var wrap = document.querySelector('.tox-dialog-wrap:not(.tox-dialog-wrap--hidden)');
            var dlg = wrap ? wrap.querySelector('.tox-dialog') : document.querySelector('.tox-dialog');
            var ta = dlg
                ? dlg.querySelector('textarea.tox-textarea') || dlg.querySelector('textarea')
                : null;
            if (ta) {
                try {
                    var cur = ta.value;
                    var pretty = htmlB(cur, opts);
                    if (pretty !== cur) {
                        ta.value = pretty;
                        ta.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                } catch (err) {
                    if (editor.notificationManager) {
                        editor.notificationManager.open({
                            text: 'Impossible de reformater ce HTML (fragment invalide ou contenu sensible).',
                            type: 'warning',
                            timeout: 7000,
                        });
                    }
                }
                return;
            }
            if (Date.now() < endAt) {
                window.requestAnimationFrame(tryOnce);
            }
        }

        window.requestAnimationFrame(tryOnce);
    }

    tinymce.init({
        selector: '#body_html',
        height: 520,
        menubar: false,
        plugins: 'lists link image code table autoresize wordcount',
        image_advtab: true,
        image_title: true,
        block_formats: 'Paragraphe=p; Titre 2=h2; Titre 3=h3; Titre 4=h4; Division=div',
        toolbar:
            'undo redo | blocks styles | bold italic underline forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image medialibrary table | ggzblocks | code | removeformat',
        relative_urls: false,
        convert_urls: true,
        branding: false,
        promotion: false,
        custom_colors: true,
        formats: {
            cms_kicker: { block: 'p', classes: 'cms-kicker' },
            cms_lead: { block: 'p', classes: 'cms-lead' },
            ggz_section_overline: { block: 'div', classes: 'section__overline' },
            ggz_section_title_h1: { block: 'h1', classes: 'section__title' },
            ggz_section_title_h2: { block: 'h2', classes: 'section__title' },
            ggz_section_lead: { block: 'p', classes: 'section__lead' },
        },
        style_formats: [
            {
                title: 'Bloc CMS (sections simples)',
                items: [
                    { title: 'Sur-titre · cms-kicker', format: 'cms_kicker' },
                    { title: 'Chapô · cms-lead', format: 'cms_lead' },
                ],
            },
            {
                title: 'Rubrique site_govgenz (.section__*)',
                items: [
                    { title: 'Sur-titre pill · section__overline', format: 'ggz_section_overline' },
                    { title: 'Titre · H2 + section__title', format: 'ggz_section_title_h2' },
                    { title: 'Titre page · H1 + section__title', format: 'ggz_section_title_h1' },
                    { title: 'Chapô · section__lead', format: 'ggz_section_lead' },
                ],
            },
        ],
        style_formats_merge: true,
        content_style:
            'body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; font-size: 17px; line-height: 1.55; color: #212529; }' +
            ' p { margin-top: 0; margin-bottom: 0.75em; }' +
            ' .cms-kicker { margin: 0 0 0.35rem; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #c2410c; }' +
            ' .cms-lead { margin: 0; font-size: 1.02rem; line-height: 1.55; }' +
            ' .section__header { text-align: center; max-width: 800px; margin: 0 auto 1.5rem; }' +
            ' .section__overline { display: inline-block; margin: 0 0 0.75rem; padding: 0.35rem 0.9rem; border-radius: 100px; border: 1px solid rgba(220,38,38,0.35); font-family: ui-monospace, monospace; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.28em; color: #b91c1c; background: rgba(220,38,38,0.06); }' +
            ' .section__title { font-family: "Bebas Neue", "Arial Narrow", sans-serif; font-size: clamp(1.75rem, 4vw, 2.6rem); line-height: 1.05; letter-spacing: -0.02em; font-weight: 400; margin: 0 0 0.65rem; color: #111827; }' +
            ' .section__lead { font-style: italic; font-size: 1rem; line-height: 1.65; color: #374151; max-width: 700px; margin: 0 auto; }',
        setup(editor) {
            editor.ui.registry.addMenuButton('ggzblocks', {
                text: 'Blocs',
                fetch(callback) {
                    callback([
                        {
                            type: 'menuitem',
                            text: 'Section « Étude » (4 indicateurs + boutons)',
                            onAction: () => editor.insertContent(tplEtudeSection(pageUrlContact, pageUrlPress)),
                        },
                        {
                            type: 'menuitem',
                            text: 'Section simple (sur-titre + titre + chapô)',
                            onAction: () => editor.insertContent(tplSectionSimple()),
                        },
                        {
                            type: 'menuitem',
                            text: 'En-tête rubrique site_govgenz (.section__header)',
                            onAction: () => editor.insertContent(tplSectionHeaderGovgenz()),
                        },
                    ]);
                },
            });

            editor.ui.registry.addButton('medialibrary', {
                text: 'Médias',
                tooltip: 'Insérer une image depuis la médiathèque',
                onAction() {
                    openMediaPicker(editor);
                },
            });

            editor.on('ExecCommand', (e) => {
                if (e.command === 'mceCodeEditor') {
                    prettifyToxCodeTextarea(editor, 1600);
                }
            });

            editor.on('ExecCommand', (e) => {
                if (e.command !== 'mceInsertContent') {
                    return;
                }
                const val = e.value;
                if (typeof val !== 'string' || val.indexOf('<img') === -1) {
                    return;
                }
                const am = val.match(/alt\s*=\s*["']([^"']*)["']/i);
                if (am && am[1].trim() !== '') {
                    return;
                }
                if (editor.notificationManager) {
                    editor.notificationManager.open({
                        text: 'Ajoutez ou complétez le texte alternatif (alt) de l’image pour l’accessibilité.',
                        type: 'warning',
                        timeout: 8000,
                    });
                }
            });
        },
        images_upload_handler(blobInfo, progress) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', uploadUrl);
                xhr.onload = () => {
                    let json;
                    try {
                        json = JSON.parse(xhr.responseText);
                    } catch {
                        reject('Réponse invalide du serveur.');
                        return;
                    }
                    if (json.csrfHash) {
                        applyCsrf(json.csrfHash);
                    }
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject(json.error || `HTTP ${xhr.status}`);
                        return;
                    }
                    if (!json.location) {
                        reject(json.error || 'Échec de l’upload');
                        return;
                    }
                    resolve(json.location);
                };
                xhr.onerror = () => {
                    reject('Erreur réseau');
                };
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable && e.total > 0) {
                        progress((e.loaded / e.total) * 100);
                    }
                };
                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append(csrfName, csrfVal());
                xhr.send(formData);
            });
        },
    });
})();
