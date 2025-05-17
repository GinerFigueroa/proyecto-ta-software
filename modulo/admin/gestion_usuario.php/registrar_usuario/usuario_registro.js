$(document).ready(function() {
    // Cargar roles al iniciar
    function loadRoles() {
        $.ajax({
            url: 'obtener_roles.php',
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#id_rol').html('<option value="">Cargando roles...</option>');
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    const $select = $('#id_rol').empty();
                    $select.append('<option value="">Seleccione un rol...</option>');
                    
                    $.each(response.data, function(i, rol) {
                        $select.append($('<option>', {
                            value: rol.id_rol,
                            text: rol.nombre
                        }));
                    });
                } else {
                    $('#id_rol').html('<option value="">No hay roles disponibles</option>');
                    console.error('Error en datos de roles:', response.error);
                }
            },
            error: function(xhr) {
                $('#id_rol').html('<option value="">Error al cargar roles</option>');
                console.error('Error al cargar roles:', xhr.responseText);
            }
        });
    }

    loadRoles();

    // Validación de formulario
    $('#formRegistroUsuario').submit(function(e) {
        e.preventDefault();
        
        // Validar contraseña
        const pass = $('#usuario_clave').val();
        const confirmPass = $('#confirmar_clave').val();
        
        if (pass.length < 8) {
            alert('La contraseña debe tener al menos 8 caracteres');
            return;
        }
        
        if (pass !== confirmPass) {
            alert('Las contraseñas no coinciden');
            return;
        }
        
        // Validar rol seleccionado
        const idRol = $('#id_rol').val();
        if (!idRol) {
            alert('Debe seleccionar un rol válido');
            return;
        }

        // Enviar datos
        const formData = {
            nombre_apellido: $('#nombre_apellido').val().trim(),
            usuario_usuario: $('#usuario_usuario').val().trim(),
            email: $('#email').val().trim(),
            telefono: $('#telefono').val().trim(),
            id_rol: idRol,
            activo: $('#activo').val(),
            usuario_clave: pass
        };

        $.ajax({
            url: 'registrar_usuario.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Usuario registrado correctamente');
                    window.location.href = '../usuario.html';
                } else {
                    alert(response.error || 'Error al registrar usuario');
                }
            },
            error: function(xhr) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.error || 'Error en el servidor');
                } catch {
                    alert('Error al procesar la respuesta del servidor');
                }
            }
        });
    });
});