(() => {
    const form = document.querySelector('form.admin-editor-form');
    if (!form) {
        return;
    }
    let dirty = false;

    function mark() {
        dirty = true;
    }

    form.addEventListener('input', mark);
    form.addEventListener('change', mark);
    form.addEventListener('submit', () => {
        dirty = false;
    });

    window.addEventListener('beforeunload', (e) => {
        if (!dirty) {
            return;
        }
        e.preventDefault();
        e.returnValue = '';
    });

    function bindTinyMce(editor) {
        if (!editor || editor.id !== 'body_html') {
            return;
        }
        editor.on('Change Undo Redo', mark);
    }

    if (typeof tinymce !== 'undefined') {
        const existing = tinymce.get('body_html');
        if (existing) {
            bindTinyMce(existing);
        }
        tinymce.on('AddEditor', (ev) => {
            bindTinyMce(ev.editor);
        });
    }
})();
