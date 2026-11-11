document.addEventListener('DOMContentLoaded', function () {
    var alerts = document.querySelectorAll('.alert');

    alerts.forEach(function (alertBox) {
        if (alertBox.classList.contains('alert-js-error')) {
            return;
        }

        var durasi = alertBox.classList.contains('alert-success') ? 4000 : 7000;
        alertBox.style.transition = 'opacity 0.5s ease, transform 0.5s ease, margin 0.5s ease';

        setTimeout(function () {
            alertBox.style.opacity = '0';
            alertBox.style.transform = 'translateY(-10px)';

            setTimeout(function () {
                if (alertBox.parentNode) {
                    alertBox.parentNode.removeChild(alertBox);
                }
            }, 500);
        }, durasi);
    });

    var forms = document.querySelectorAll('form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) {
                return;
            }

            var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

            if (submitButton && !submitButton.disabled) {
                setTimeout(function () {
                    submitButton.disabled = true;
                    submitButton.style.opacity = '0.7';
                    submitButton.style.cursor = 'not-allowed';

                    if (submitButton.tagName === 'INPUT') {
                        submitButton.value = 'Memproses...';
                    } else {
                        submitButton.textContent = 'Memproses...';
                    }
                }, 10);
            }
        });
    });
});