(() => {
    const cfgEl = document.getElementById('admin-media-dropzone-config');
    if (!cfgEl || typeof Dropzone === 'undefined') {
        return;
    }
    let cfg;
    try {
        cfg = JSON.parse(cfgEl.textContent || '{}');
    } catch {
        return;
    }
    const uploadUrl = cfg.uploadUrl;
    const csrfName = cfg.csrfName;
    if (!uploadUrl || !csrfName) {
        return;
    }

    Dropzone.autoDiscover = false;

    function csrfVal() {
        const el = document.querySelector(`#admin-media-csrf input[name="${csrfName}"]`);
        return el ? el.value : '';
    }

    function patchCsrfInput(hash) {
        if (!hash) {
            return;
        }
        const h = document.querySelector(`#admin-media-csrf input[name="${csrfName}"]`);
        if (h) {
            h.value = hash;
        }
    }

    new Dropzone('#media-dropzone', {
        url: uploadUrl,
        paramName: 'file',
        maxFilesize: 10,
        acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml,image/svg,.svg,application/pdf',
        parallelUploads: 1,
        dictDefaultMessage:
            'Glissez-déposez des fichiers ici ou cliquez pour choisir (images, SVG, PDF — max 10 Mo)',
        init() {
            this.on('sending', (file, xhr, formData) => {
                formData.append(csrfName, csrfVal());
            });
            this.on('success', (file, response) => {
                let data = response;
                if (typeof data === 'string') {
                    try {
                        data = JSON.parse(data);
                    } catch {
                        data = null;
                    }
                }
                if (data && data.csrfHash) {
                    patchCsrfInput(data.csrfHash);
                }
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });
            this.on('error', (file, message, xhr) => {
                let msg = 'Erreur d’upload';
                if (xhr && xhr.responseText) {
                    try {
                        const j = JSON.parse(xhr.responseText);
                        if (j.error) {
                            msg = j.error;
                        }
                        if (j.csrfHash) {
                            patchCsrfInput(j.csrfHash);
                        }
                    } catch {
                        /* ignore */
                    }
                } else if (typeof message === 'string') {
                    msg = message;
                }
                alert(msg);
            });
        },
    });
})();
