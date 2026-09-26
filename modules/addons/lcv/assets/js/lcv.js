document.addEventListener('DOMContentLoaded', function () {
    function setChecks(selector, value) {
        document.querySelectorAll(selector).forEach(function (input) {
            input.checked = value;
        });
    }

    const select = document.querySelector('[data-lcv-select]');
    const clear = document.querySelector('[data-lcv-clear]');

    if (select) {
        select.addEventListener('click', function () {
            setChecks('.lcv-check', true);
        });
    }

    if (clear) {
        clear.addEventListener('click', function () {
            setChecks('.lcv-check', false);
        });
    }

    document.querySelectorAll('[data-lcv-group-select]').forEach(function (button) {
        button.addEventListener('click', function () {
            const group = button.getAttribute('data-lcv-group-select');
            const container = document.querySelector('[data-lcv-group="' + group + '"]');
            if (container) {
                container.querySelectorAll('.lcv-check').forEach(function (input) {
                    input.checked = true;
                });
            }
        });
    });

    document.querySelectorAll('[data-lcv-filter]').forEach(function (input) {
        input.addEventListener('input', function () {
            const query = input.value.toLowerCase().trim();
            document.querySelectorAll('[data-lcv-filter-row]').forEach(function (row) {
                row.style.display = !query || row.textContent.toLowerCase().indexOf(query) !== -1 ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('[data-lcv-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            const target = document.getElementById(button.getAttribute('data-lcv-toggle-password'));
            if (!target) return;
            const isPassword = target.type === 'password';
            target.type = isPassword ? 'text' : 'password';
            button.textContent = isPassword ? 'Hide' : 'Show';
        });
    });
});
