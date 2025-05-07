$(document).ready(function() {
    $('#tablaCitas').DataTable({
        ajax: {
            url: 'buscar.php',
            dataSrc: 'data',
            error: function(xhr) {
                console.error("Error al cargar datos:", xhr.responseText);
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
            { data: 'duracion' },
            { 
                data: 'estado',
                render: function(data) {
                    return `<span class="badge bg-${getEstadoClass(data)}">${data}</span>`;
                }
            },
            {
                data: 'id_cita',
                render: function(data) {
                    return `<button class="btn-editar" data-id="${data}">Editar</button>`;
                }
            }
        ],
        language: spanishLanguageConfig(),
        order: [[4, 'desc']] // Ordenar por fecha descendente
    });

    function getEstadoClass(estado) {
        const clases = {
            'Pendiente': 'warning',
            'Confirmada': 'primary',
            'Completada': 'success',
            'Cancelada': 'secondary',
            'No asistió': 'danger'
        };
        return clases[estado];
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        events: {
            url: 'agenda.php',
            method: 'GET',
            failure: function(error) {
                console.error('Error loading events:', error);
                alert('Error al cargar eventos. Ver consola para detalles.');
            }
        },
        // ... otras configuraciones
    });

    calendar.render();
});
