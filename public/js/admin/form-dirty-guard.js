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
        if (!editor) {
            return;
        }
        const el = editor.getElement();
        if (!el || !form.contains(el)) {
            return;
        }
        editor.on('Change Undo Redo', mark);
    }

    if (typeof tinymce !== 'undefined') {
        Object.keys(tinymce.editors).forEach((id) => {
            bindTinyMce(tinymce.editors[id]);
        });
        tinymce.on('AddEditor', (ev) => {
            bindTinyMce(ev.editor);
        });
    }
})();
