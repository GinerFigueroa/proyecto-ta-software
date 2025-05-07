$(document).ready(function() {
    let tablaPacientes;

    function cargarTabla(searchTerm = '') {
        if ($.fn.DataTable.isDataTable('#tablaPacientes')) {
            tablaPacientes.destroy();
        }

        tablaPacientes = $('#tablaPacientes').DataTable({
            ajax: {
                url: 'buscar.php',
                data: { search: searchTerm },
                dataSrc: 'data',
                error: function(xhr) {
                    console.error("Error:", xhr.responseText);
                }
            },
            columns: [
                { data: 'id_paciente' },
                { data: 'nombre' },
                { data: 'fecha_nacimiento' },
                { 
                    data: 'edad',
                    render: function(data) {
                        return data ? data + ' años' : 'N/A';
                    }
                },
                { data: 'genero' },
                { data: 'alergias' },
                { data: 'enfermedades_cronicas' },
                { data: 'medicamentos' },
                { data: 'seguro_medico' },
                { data: 'numero_seguro' },
                {
                    data: 'id_paciente',
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-primary btn-editar" data-id="${data}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data}">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: spanishLanguageConfig(),
            pageLength: 10
        });
    }

    // Configuración de idioma
    function spanishLanguageConfig() {
        return {
            "search": "Buscar:",
            "lengthMenu": "Mostrar _MENU_ registros",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        };
    }

    // Búsqueda
    $('#btnBuscar').click(function() {
        const searchTerm = $('#inputBuscar').val();
        cargarTabla(searchTerm);
    });

    // Eliminar paciente
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar paciente?',
            text: "¡Esta acción no se puede deshacer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar.php',
                    method: 'POST',
                    data: { id_paciente: id },
                    success: function(response) {
                        tablaPacientes.ajax.reload();
                        Swal.fire('¡Eliminado!', response.message, 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.error, 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        // Validación adicional del ID
        if (typeof id === 'undefined' || id === null || isNaN(id)) {
            Swal.fire('Error', 'ID de paciente inválido', 'error');
            return;
        }
        
        // Convertir a número entero
        const idPaciente = parseInt(id, 10);
        
        // Redirección con parámetro correcto
        window.location.href = `editar_paciente/paciente.html?id_paciente=${idPaciente}`;
    });
    // Cargar tabla inicial
    cargarTabla();
});
