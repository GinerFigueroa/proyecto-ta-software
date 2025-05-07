<?php
require_once '../../../php/database/conexion.php';
require_once 'funciones.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : null;
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : null;
    $metodo_pago = isset($_POST['metodo_pago']) ? htmlspecialchars($_POST['metodo_pago']) : null;
    $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : null;
    $referencia = isset($_POST['referencia']) ? htmlspecialchars($_POST['referencia']) : null;
    $estado = isset($_POST['estado']) ? htmlspecialchars($_POST['estado']) : null;
    $notas = isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : null;
    $creado_por = isset($_POST['creado_por']) ? intval($_POST['creado_por']) : null;

    // Validaciones
    $errors = [];

if (empty($monto)) $errors[] = 'El monto es requerido';
if (empty($metodo_pago)) $errors[] = 'El método de pago es requerido';
if (empty($fecha_pago)) $errors[] = 'La fecha de pago es requerida';
if (empty($estado)) $errors[] = 'El estado es requerido';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
    exit;
}

    try {
        $pdo->beginTransaction();

        // Insertar nuevo pago
        $stmt = $pdo->prepare("INSERT INTO pagos 
                              (id_cita, monto, metodo_pago, estado, referencia, fecha_pago, notas, creado_por) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $id_cita ?: null,
            $monto,
            $metodo_pago,
            $estado,
            $referencia ?: null,
            $fecha_pago,
            $notas ?: null,
            $creado_por ?: null
        ]);

        $id_pago = $pdo->lastInsertId();

        // Registrar en logs
        registrarLog($pdo, $creado_por, 'Registro de pago', 'pagos', $id_pago, null, [
            'monto' => $monto,
            'metodo_pago' => $metodo_pago,
            'estado' => $estado
        ]);

        // Si está asociado a una cita y el pago es completado, actualizar estado de la cita
        if ($id_cita && $estado === 'Completado') {
            $stmt = $pdo->prepare("UPDATE citas SET estado = 'Completada' WHERE id_cita = ?");
            $stmt->execute([$id_cita]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'id_pago' => $id_pago
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar pago: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>