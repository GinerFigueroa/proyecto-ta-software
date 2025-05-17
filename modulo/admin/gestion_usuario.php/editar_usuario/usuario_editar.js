$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('id');
    
    if (!userId) {
        alert('ID de usuario no proporcionado');
        window.location.href = '../usuario.html';
        return;
    }

    // Cargar datos del usuario y roles
    $.ajax({
        url: `usuario_editar.php?id=${userId}`,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const { usuario, roles } = response;
                
                // Llenar formulario
                $('#id_usuario').val(usuario.id_usuario);
                $('#nombre_apellido').val(usuario.nombre_apellido || '');
                $('#usuario_usuario').val(usuario.usuario_usuario);
                $('#email').val(usuario.email);
                $('#telefono').val(usuario.telefono || '');
                $('#activo').val(usuario.activo.toString());
                
                // Llenar select de roles
                const rolSelect = $('#id_rol');
                rolSelect.empty().append('<option value="">Seleccionar rol...</option>');
                roles.forEach(rol => {
                    rolSelect.append(
                        `<option value="${rol.id_rol}" ${rol.id_rol == usuario.id_rol ? 'selected' : ''}>
                            ${rol.nombre}
                        </option>`
                    );
                });
                
                // Mostrar formulario
                $('#loading').hide();
                $('#formContainer').fadeIn();
            } else {
                alert(response.error);
                window.location.href = '../usuario.html';
                usuario.html
            }
        },
        error: function(xhr) {
            alert('Error al cargar datos del usuario');
            window.location.href = '../usuario.html';
        }
    });

    // Validación de contraseñas coincidentes
    $('#confirmar_clave').on('input', function() {
        const password = $('#usuario_clave').val();
        const confirmPassword = $(this).val();
        
        if (password && confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $('#usuario_clave').addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            $('#usuario_clave').removeClass('is-invalid');
        }
    });

    // Envío del formulario
    $('#formEditarUsuario').submit(function(e) {
        e.preventDefault();
        
        const password = $('#usuario_clave').val();
        const confirmPassword = $('#confirmar_clave').val();
        
        if (password && password !== confirmPassword) {
            alert('Las contraseñas no coinciden');
            return;
        }
        
        const formData = {
            id_usuario: $('#id_usuario').val(),
            id_rol: $('#id_rol').val(),
            nombre_apellido: $('#nombre_apellido').val(),
            telefono: $('#telefono').val(),
            activo: $('#activo').val()
        };
        
        if (password) {
            formData.usuario_clave = password;
        }
        
        $.ajax({
            url: 'actualizar_usuario.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Usuario actualizado correctamente');
                    window.location.href = '../usuario.html';
                } else {
                    alert(response.error);
                }
            },
            error: function(xhr) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.error || 'Error al actualizar usuario');
                } catch {
                    alert('Error al procesar la respuesta del servidor');
                }
            }
        });
    });

    // Botón cancelar
    $('#btnCancelar').click(function() {
        if (confirm('¿Estás seguro de que deseas cancelar los cambios?')) {
            window.location.href = '../usuario.html';
        }
    });
});
