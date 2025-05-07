$(document).ready(function() {
    let tablaCitas;

    function inicializarTabla() {
        tablaCitas = $('#tablaCitas').DataTable({
            ajax: {
                url: 'buscar.php',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error("Error al cargar citas:", xhr.responseText);
                    $('#tablaCitas tbody').html(
                        `<tr><td colspan="8" class="text-center text-danger">Error al cargar los datos</td></tr>`
                    );
                }
            },
            columns: [
                { data: 'id_cita' },
                { data: 'paciente' },
                { data: 'tratamiento' },
                { data: 'dentista' },
                {
                    data: 'fecha_hora',
                    render: function(data) {
                        return moment(data).format('DD/MM/YYYY HH:mm');
                    }
                },
                {
                    data: 'duracion',
                    render: function(data) {
                        return data + ' min';
                    }
                },
                {
                    data: 'estado',
                    render: function(data) {
                        const clases = {
                            'Pendiente': 'bg-warning',
                            'Confirmada': 'bg-primary',
                            'Completada': 'bg-success',
                            'Cancelada': 'bg-secondary',
                            'No asistió': 'bg-danger'
                        };
                        return `<span class="badge ${clases[data]}">${data}</span>`;
                    }
                },
                {
                    data: 'id_cita',
                    render: function(id_cita) {
                        return `
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-primary btn-editar" data-id="${id_cita}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${id_cita}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            pageLength: 10
        });
    }

    // Eliminar cita
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: '¿Eliminar Cita?',
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
                    data: { id_cita: id },
                    success: function(response) {
                        tablaCitas.ajax.reload();
                        Swal.fire('¡Eliminado!', 'La cita fue eliminada', 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudo eliminar la cita', 'error');
                    }
                });
            }
        });
    });

    // --- Zona corregida --- 
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        // Validación robusta del ID
        if (typeof id === 'undefined' || id === null || isNaN(id) || id <= 0) {
            Swal.fire('Error', 'ID de cita inválido', 'error');
            return;
        }
        
        // Redirección al HTML (no al PHP)
        window.location.href = `editar_cita/cita_editar.html?id_cita=${id}`; 
    });

    // Inicializar tabla
    inicializarTabla();
});