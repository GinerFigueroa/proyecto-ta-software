<script>
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');

    if (!id) {
        alert('ID no proporcionado');
        window.location.href = '../tratamientos.html';
    }

    // Cargar datos del tratamiento
    fetch(`obtener_tratamiento.php?id=${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Error HTTP: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);
            
            document.getElementById('id_tratamiento').value = data.id_tratamiento;
            document.getElementById('nombre').value = data.nombre;
            document.getElementById('descripcion').value = data.descripcion;
            document.getElementById('duracion_estimada').value = data.duracion_estimada;
            document.getElementById('costo').value = data.costo;
            document.getElementById('requisitos').value = data.requisitos;
            document.getElementById('activo').value = data.activo;
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Error al cargar el tratamiento');
            window.location.href = '../tratamientos.html';
        });

    // Enviar formulario
    document.getElementById('formEditar').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        try {
            const response = await fetch('actualizar_editar.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Error desconocido');

            alert(result.message);
            window.location.href = '../tratamientos.html';

        } catch (error) {
            console.error('Error:', error);
            alert(error.message || 'Error al actualizar');
        }
    });
</script>