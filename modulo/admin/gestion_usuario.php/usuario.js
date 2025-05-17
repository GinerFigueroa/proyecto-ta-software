$(document).ready(function() {
    // Inicializar DataTable
    const tablaUsuarios = $('#tablaUsuarios').DataTable({
        ajax: {
            url: 'buscar_usuario.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id_usuario' },
            { data: 'nombre_apellido' },
            { data: 'usuario_usuario' },
            { data: 'email' },
            { data: 'rol_nombre' },
            { data: 'telefono' },
            { 
                data: 'activo',
                render: function(data) {
                    return data ? '<span class="badge bg-success">Activo</span>' : 
                                 '<span class="badge bg-danger">Inactivo</span>';
                }
            },
            { 
                data: 'ultimo_login',
                render: function(data) {
                    return data ? new Date(data).toLocaleString() : 'Nunca';
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning btn-editar" data-id="${data.id_usuario}">
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data.id_usuario}">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </div>
                    `;
                },
                orderable: false
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });

    // Redirección para registrar nuevo usuario
    $('#btnNuevoUsuario').click(function() {
        window.location.href = 'registrar_usuario/usuario_registro.html';
        
    });

    // Redirección para editar usuario
    $('#tablaUsuarios').on('click', '.btn-editar', function() {
        const idUsuario = $(this).data('id');
        window.location.href = `editar_usuario/usuario_editar.html?id=${idUsuario}`;
        
    });

    // Manejar eliminación de usuario
    const confirmarEliminarModal = new bootstrap.Modal('#confirmarEliminarModal');
    
    $('#tablaUsuarios').on('click', '.btn-eliminar', function() {
        const idUsuario = $(this).data('id');
        $('#usuarioIdEliminar').val(idUsuario);
        confirmarEliminarModal.show();
    });

    $('#btnConfirmarEliminar').click(function() {
        const idUsuario = $('#usuarioIdEliminar').val();
        
        $.ajax({
            url: 'eliminar.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: idUsuario }),
            success: function(response) {
                confirmarEliminarModal.hide();
                tablaUsuarios.ajax.reload();
                alert('Usuario eliminado correctamente');
            },
            error: function(xhr) {
                alert('Error al eliminar usuario: ' + xhr.responseText);
            }
        });
    });
});
