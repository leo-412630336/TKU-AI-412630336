// public/js/script.js

document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.getElementById('password');
    const strengthMeter = document.getElementById('strength-meter');

    if (passwordInput && strengthMeter) {
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            let strength = 0;

            if (val.length >= 8) strength++;
            if (val.match(/[A-Z]/) && val.match(/[0-9]/)) strength++;
            if (val.match(/[^a-zA-Z0-9]/)) strength++;

            strengthMeter.className = 'strength-meter';
            if (val.length > 0) {
                if (strength === 0) strengthMeter.classList.add('strength-weak');
                else if (strength === 1) strengthMeter.classList.add('strength-weak');
                else if (strength === 2) strengthMeter.classList.add('strength-medium');
                else if (strength >= 3) strengthMeter.classList.add('strength-strong');
            }
        });
    }

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            // Basic Client-side Validation
            const inputs = form.querySelectorAll('input[required]');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
});
