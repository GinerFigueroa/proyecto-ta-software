<?php
// api.php - Endpoint para manejar todas las solicitudes AJAX

header('Content-Type: application/json');
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

session_start();
require_once '../php/database/conexion.php';


// Obtener acción solicitada
$action = $_GET['action'] ?? '';

// Obtener ID del paciente
$id_usuario = $_SESSION['id_usuario'];
$paciente = $db->query("SELECT id_paciente FROM pacientes WHERE id_usuario = $id_usuario")->fetch_assoc();
$id_paciente = $paciente['id_paciente'] ?? null;

if (!$id_paciente) {
    echo json_encode(['success' => false, 'message' => 'No se encontró información del paciente']);
    exit();
}

// Procesar acciones
switch ($action) {
    case 'getTratamientos':
        $tratamientos = $db->query("SELECT * FROM tratamientos WHERE activo = 1");
        $resultados = [];
        while ($row = $tratamientos->fetch_assoc()) {
            $resultados[] = [
                'id_tratamiento' => $row['id_tratamiento'],
                'nombre' => $row['nombre'],
                'costo' => $row['costo'],
                'duracion_estimada' => $row['duracion_estimada']
            ];
        }
        echo json_encode($resultados);
        break;
        
    case 'getCitas':
        $citas = $db->query("
            SELECT c.*, t.nombre as tratamiento 
            FROM citas c 
            JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento 
            WHERE c.id_paciente = $id_paciente 
            ORDER BY c.fecha_hora DESC
        ");
        
        $resultados = [];
        while ($row = $citas->fetch_assoc()) {
            $resultados[] = [
                'id_cita' => $row['id_cita'],
                'fecha_hora' => $row['fecha_hora'],
                'tratamiento' => $row['tratamiento'],
                'estado' => $row['estado'],
                'notas' => $row['notas']
            ];
        }
        echo json_encode($resultados);
        break;
        
    case 'agendarCita':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id_tratamiento = $db->real_escape_string($data['tratamiento']);
        $fecha_hora = $db->real_escape_string($data['fecha'] . ' ' . $data['hora']);
        $notas = $db->real_escape_string($data['notas'] ?? '');
        
        // Verificar disponibilidad (simplificado - en un sistema real sería más complejo)
        $existe = $db->query("
            SELECT id_cita FROM citas 
            WHERE fecha_hora = '$fecha_hora' 
            AND estado IN ('Pendiente', 'Confirmada')
        ")->num_rows;
        
        if ($existe > 0) {
            echo json_encode(['success' => false, 'message' => 'La hora seleccionada no está disponible']);
            break;
        }
        
        $sql = "INSERT INTO citas (id_paciente, id_tratamiento, fecha_hora, duracion, estado, notas, creado_por) 
                VALUES ($id_paciente, $id_tratamiento, '$fecha_hora', 30, 'Pendiente', '$notas', $id_usuario)";
        
        if ($db->query($sql)) {
            echo json_encode(['success' => true, 'message' => '¡Cita agendada correctamente!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agendar la cita: ' . $db->error]);
        }
        break;
        
    case 'cancelarCita':
        $data = json_decode(file_get_contents('php://input'), true);
        $id_cita = $db->real_escape_string($data['id_cita']);
        
        // Verificar que la cita pertenece al paciente
        $verificacion = $db->query("
            SELECT id_cita FROM citas 
            WHERE id_cita = $id_cita AND id_paciente = $id_paciente
        ")->num_rows;
        
        if ($verificacion === 0) {
            echo json_encode(['success' => false, 'message' => 'Cita no encontrada o no autorizada']);
            break;
        }
        
        $sql = "UPDATE citas SET estado = 'Cancelada' WHERE id_cita = $id_cita";
        
        if ($db->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Cita cancelada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar la cita: ' . $db->error]);
        }
        break;
        
    case 'getUserData':
        $usuario = $db->query("
            SELECT nombre_apellido FROM usuarios WHERE id_usuario = $id_usuario
        ")->fetch_assoc();
        
        echo json_encode($usuario ?: ['nombre_apellido' => 'Usuario']);
        break;


     // Agregar estos nuevos casos al switch statement en api.php
case 'getPagosCita':
    $id_cita = intval($_GET['id_cita']);
    $pagos = $db->query("SELECT * FROM pagos WHERE id_cita = $id_cita");
    $resultados = [];
    while ($row = $pagos->fetch_assoc()) {
        $resultados[] = $row;
    }
    echo json_encode($resultados);
    break;

case 'registrarPago':
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id_cita = $db->real_escape_string($data['id_cita']);
    $monto = $db->real_escape_string($data['monto']);
    $metodo_pago = $db->real_escape_string($data['metodo_pago']);
    $referencia = $db->real_escape_string($data['referencia'] ?? '');
    $notas = $db->real_escape_string($data['notas'] ?? '');
    
    $sql = "INSERT INTO pagos (id_cita, monto, metodo_pago, estado, referencia, fecha_pago, notas) 
            VALUES ($id_cita, $monto, '$metodo_pago', 'Completado', '$referencia', NOW(), '$notas')";
    
    if ($db->query($sql)) {
        // Actualizar estado de la cita si es necesario
        $db->query("UPDATE citas SET estado = 'Completada' WHERE id_cita = $id_cita");
        echo json_encode(['success' => true, 'message' => 'Pago registrado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el pago: ' . $db->error]);
    }
    break;



        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

$db->close();
?>