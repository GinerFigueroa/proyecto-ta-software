document.addEventListener('DOMContentLoaded', function () {
    // Validación del formulario de login
    const loginForm = document.querySelector('form');
    if (loginForm) {
        const usernameInput = document.querySelector('input[name="username"]');
        const passwordInput = document.querySelector('input[name="password"]');

        loginForm.addEventListener('submit', function (event) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value.trim();
            let errors = [];

            if (username === '') {
                errors.push('El usuario/email es obligatorio');
            }

            if (password === '') {
                errors.push('La contraseña es obligatoria');
            } else if (password.length < 8) {
                errors.push('La contraseña debe tener al menos 8 caracteres');
            } else if (!/[A-Z]/.test(password)) {
                errors.push('La contraseña debe contener al menos una mayúscula');
            } else if (!/[0-9]/.test(password)) {
                errors.push('La contraseña debe contener al menos un número');
            } else if (!/[^A-Za-z0-9]/.test(password)) {
                errors.push('La contraseña debe contener al menos un carácter especial');
            }

            if (errors.length > 0) {
                event.preventDefault();
                alert(errors.join('\n'));
            }
        });
    }

    // Efectos para las tarjetas del dashboard
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const link = this.querySelector('a');
            if (link) {
                window.location.href = link.href;
            }
        });
    });
});