document.getElementById('registroForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Limpiar mensajes de error anteriores
    clearErrors();
    
    // Validar campos
    let isValid = true;
    
    // Validar contraseñas coincidentes
    const password = document.getElementById('usuario_clave').value;
    const confirmPassword = document.getElementById('confirmar_clave').value;
    
    if (password !== confirmPassword) {
        showError('confirmar_clave', 'Las contraseñas no coinciden');
        isValid = false;
    }
    
    // Validar fortaleza de contraseña (opcional)
    if (password.length < 8) {
        showError('usuario_clave', 'La contraseña debe tener al menos 8 caracteres');
        isValid = false;
    }
    
    // Validar email
    const email = document.getElementById('email').value;
    if (!validateEmail(email)) {
        showError('email', 'Ingrese un correo electrónico válido');
        isValid = false;
    }
    
    // Si todo es válido, enviar el formulario
    if (isValid) {
        this.submit();
    }
});

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.style.borderColor = '#e74c3c';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function clearErrors() {
    const errors = document.querySelectorAll('.error');
    errors.forEach(error => error.remove());
    
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => input.style.borderColor = '#ddd');
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}