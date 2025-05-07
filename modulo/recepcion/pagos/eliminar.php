<?php
require_once '../../../php/database/conexion.php';
require_once 'funciones.php';

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pago = filter_input(INPUT_POST, 'id_pago', FILTER_VALIDATE_INT);
    $id_cita = filter_input(INPUT_POST, 'id_cita', FILTER_VALIDATE_INT);
    $monto = filter_input(INPUT_POST, 'monto', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
    $fecha_pago = filter_input(INPUT_POST, 'fecha_pago', FILTER_SANITIZE_STRING);
    $referencia = filter_input(INPUT_POST, 'referencia', FILTER_SANITIZE_STRING);
    $estado = filter_input(INPUT_POST, 'estado', FILTER_SANITIZE_STRING);
    $notas = filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_STRING);
    $actualizado_por = $_SESSION['id_usuario'] ?? null;

    // Validaciones básicas
    if (!$id_pago) {
        echo json_encode(['success' => false, 'message' => 'ID de pago inválido']);
        exit;
    }

    if (empty($monto) || empty($metodo_pago) || empty($fecha_pago) || empty($estado)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
        exit;
    }

    if (!is_numeric($monto) || $monto <= 0) {
        echo json_encode(['success' => false, 'message' => 'El monto debe ser un número positivo']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Obtener datos actuales del pago para el log
        $stmt = $pdo->prepare("SELECT * FROM pagos WHERE id_pago = ?");
        $stmt->execute([$id_pago]);
        $pago_actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pago_actual) {
            throw new Exception("Pago no encontrado");
        }

        // Actualizar pago
        $stmt = $pdo->prepare("UPDATE pagos SET 
                              id_cita = ?, monto = ?, metodo_pago = ?, estado = ?, 
                              referencia = ?, fecha_pago = ?, notas = ?
                              WHERE id_pago = ?");
        $stmt->execute([
            $id_cita ?: null,
            $monto,
            $metodo_pago,
            $estado,
            $referencia ?: null,
            $fecha_pago,
            $notas ?: null,
            $id_pago
        ]);

        // Registrar en logs
        $datos_nuevos = [
            'monto' => $monto,
            'metodo_pago' => $metodo_pago,
            'estado' => $estado,
            'fecha_pago' => $fecha_pago
        ];
        
        registrarLog($pdo, $actualizado_por, 'Actualización de pago', 'pagos', $id_pago, $pago_actual, $datos_nuevos);

        // Si está asociado a una cita y el estado cambió a Completado, actualizar cita
        if ($id_cita && $estado === 'Completado' && $pago_actual['estado'] !== 'Completado') {
            $stmt = $pdo->prepare("UPDATE citas SET estado = 'Completada' WHERE id_cita = ?");
            $stmt->execute([$id_cita]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pago actualizado correctamente'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar pago: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>