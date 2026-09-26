document.addEventListener('DOMContentLoaded', function () {
    const select = document.querySelector('[data-lcv-select]');
    const clear = document.querySelector('[data-lcv-clear]');

    function setChecks(value) {
        document.querySelectorAll('.lcv-check').forEach(function (input) {
            input.checked = value;
        });
    }

    if (select) {
        select.addEventListener('click', function () {
            setChecks(true);
        });
    }

    if (clear) {
        clear.addEventListener('click', function () {
            setChecks(false);
        });
    }
});
