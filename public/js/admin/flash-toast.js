(() => {
    const el = document.getElementById('adminToastOk');
    if (el && typeof bootstrap !== 'undefined') {
        bootstrap.Toast.getOrCreateInstance(el, { autohide: true, delay: 5000 }).show();
    }
})();
