document.addEventListener('DOMContentLoaded', function() {
    // Configurar fecha actual por defecto al abrir el modal
    const nuevoPagoModal = document.getElementById('nuevoPagoModal');
    if (nuevoPagoModal) {
        nuevoPagoModal.addEventListener('show.bs.modal', function() {
            document.getElementById('fecha_pago').value = new Date().toISOString().slice(0, 16);
            cargarCitasParaPago();
        });
    }

    // Evento para formulario de nuevo pago
    document.getElementById('formNuevoPago')?.addEventListener('submit', function(e) {
        e.preventDefault();
        registrarPago();
    });

    // Función para cargar citas disponibles para pago
    function cargarCitasParaPago() {
        fetch('../citas/buscar.php?para_pago=1')
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar citas');
                return response.json();
            })
            .then(data => {
                const select = document.getElementById('id_cita');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar cita...</option>';
                    data.forEach(cita => {
                        const option = document.createElement('option');
                        option.value = cita.id_cita;
                        option.textContent = `#${cita.id_cita} - ${cita.nombre_paciente} ${cita.apellido_paciente} (${formatFecha(cita.fecha_hora)})`;
                        option.setAttribute('data-monto', cita.monto_tratamiento || '0');
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar citas:', error);
                Swal.fire('Error', 'No se pudieron cargar las citas disponibles', 'error');
            });
    }

    // Función para registrar nuevo pago
    function registrarPago() {
        const form = document.getElementById('formNuevoPago');
        if (!form) return;

        // Validar campos requeridos
        const requiredFields = ['monto', 'metodo_pago', 'fecha_pago', 'estado'];
        const missingFields = requiredFields.filter(field => !form.elements[field].value);

        if (missingFields.length > 0) {
            Swal.fire('Error', 'Por favor complete todos los campos obligatorios', 'error');
            return;
        }

        const formData = new FormData(form);
        
        // Agregar el ID del usuario que crea el pago (ajustar según tu sistema)
        formData.append('creado_por', 1); // Ejemplo: ID del usuario logueado

        fetch('registrar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Éxito',
                    text: data.message,
                    icon: 'success',
                    willClose: () => {
                        form.reset();
                        const modal = bootstrap.Modal.getInstance(nuevoPagoModal);
                        modal?.hide();
                        cargarPagos();
                        cargarResumen();
                    }
                });
            } else {
                throw new Error(data.message || 'Error desconocido al registrar el pago');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', error.message || 'Ocurrió un error al registrar el pago', 'error');
        });
    }

    // Función auxiliar para formatear fechas
    function formatFecha(fechaString) {
        if (!fechaString) return 'N/A';
        const fecha = new Date(fechaString);
        return fecha.toLocaleDateString('es-PE', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Otras funciones necesarias (cargarPagos, cargarResumen, etc.)
    // ... (implementar según sea necesario)
});