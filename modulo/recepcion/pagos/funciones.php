<?php
/**
 * Archivo de funciones auxiliares para el sistema de gestión de pagos
 */

/**
 * Convierte una fecha de formato MySQL a formato legible
 * @param string $fecha Fecha en formato MySQL (Y-m-d H:i:s)
 * @param bool $incluirHora Si se debe incluir la hora en el formato
 * @return string Fecha formateada
 */
function formatearFecha($fecha, $incluirHora = true) {
    if (empty($fecha)) return 'N/A';
    
    $timestamp = strtotime($fecha);
    if ($timestamp === false) return 'N/A';
    
    $formato = $incluirHora ? 'd/m/Y H:i' : 'd/m/Y';
    return date($formato, $timestamp);
}

/**
 * Genera una contraseña aleatoria segura
 * @param int $longitud Longitud de la contraseña (por defecto 8)
 * @return string Contraseña generada
 */
function generarPasswordAleatorio($longitud = 8) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }
    
    return $password;
}

/**
 * Registra una acción en el log del sistema
 * @param PDO $pdo Conexión a la base de datos
 * @param int|null $id_usuario ID del usuario que realiza la acción
 * @param string $accion Descripción de la acción realizada
 * @param string $tabla_afectada Tabla afectada por la acción
 * @param int|null $id_registro ID del registro afectado
 * @param mixed $datos_anteriores Datos antes de la modificación (para updates)
 * @param mixed $datos_nuevos Datos después de la modificación (para updates)
 * @return bool True si se registró correctamente
 */
function registrarLog($pdo, $id_usuario, $accion, $tabla_afectada, $id_registro, $datos_anteriores = null, $datos_nuevos = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs 
                              (id_usuario, accion, tabla_afectada, id_registro_afectado, 
                               datos_anteriores, datos_nuevos) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        
        $datos_anteriores_json = $datos_anteriores ? json_encode($datos_anteriores) : null;
        $datos_nuevos_json = $datos_nuevos ? json_encode($datos_nuevos) : null;
        
        return $stmt->execute([
            $id_usuario,
            $accion,
            $tabla_afectada,
            $id_registro,
            $datos_anteriores_json,
            $datos_nuevos_json
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar log: " . $e->getMessage());
        return false;
    }
}

/**
 * Valida y sanitiza un número decimal (para montos de pago)
 * @param mixed $valor Valor a validar
 * @param float $min Valor mínimo permitido
 * @param float $max Valor máximo permitido
 * @return float|null Valor validado o null si no es válido
 */
function validarDecimal($valor, $min = 0, $max = 999999.99) {
    if (!is_numeric($valor)) return null;
    
    $valor = floatval($valor);
    $valor = round($valor, 2); // Redondear a 2 decimales
    
    if ($valor < $min || $valor > $max) return null;
    
    return $valor;
}

/**
 * Valida un método de pago
 * @param string $metodo Método a validar
 * @return string|null Método validado o null si no es válido
 */
function validarMetodoPago($metodo) {
    $metodos_validos = ['Efectivo', 'Tarjeta crédito', 'Tarjeta débito', 'Transferencia'];
    return in_array($metodo, $metodos_validos) ? $metodo : null;
}

/**
 * Valida un estado de pago
 * @param string $estado Estado a validar
 * @return string|null Estado validado o null si no es válido
 */
function validarEstadoPago($estado) {
    $estados_validos = ['Pendiente', 'Completado', 'Reembolsado', 'Cancelado'];
    return in_array($estado, $estados_validos) ? $estado : null;
}

/**
 * Envía un correo electrónico con las credenciales (para notificaciones)
 * @param string $email Correo electrónico del destinatario
 * @param string $nombre Nombre del destinatario
 * @param string $password Contraseña a enviar
 * @return bool True si se envió correctamente
 */
function enviarCredencialesPorEmail($email, $nombre, $password) {
    // En un entorno real, usaríamos PHPMailer o similar
    // Esta es una implementación simulada para desarrollo
    
    $asunto = "Bienvenido a nuestra clínica dental";
    $mensaje = "Hola $nombre,\n\n";
    $mensaje .= "Has sido registrado en nuestro sistema.\n";
    $mensaje .= "Tus credenciales de acceso son:\n";
    $mensaje .= "Email: $email\n";
    $mensaje .= "Contraseña temporal: $password\n\n";
    $mensaje .= "Por favor cambia tu contraseña después de iniciar sesión.\n\n";
    $mensaje .= "Atentamente,\nEl equipo de la clínica dental";
    
    // En producción usaríamos: mail($email, $asunto, $mensaje);
    // Para desarrollo solo lo registramos en el log
    error_log("Email simulado enviado a $email: $mensaje");
    
    return true;
}

/**
 * Obtiene el nombre completo del usuario
 * @param PDO $pdo Conexión a la base de datos
 * @param int $id_usuario ID del usuario
 * @return string Nombre completo o 'N/A' si no se encuentra
 */
function obtenerNombreUsuario($pdo, $id_usuario) {
    try {
        $stmt = $pdo->prepare("SELECT nombre, apellido FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $usuario ? trim($usuario['nombre'] . ' ' . $usuario['apellido']) : 'N/A';
    } catch (PDOException $e) {
        error_log("Error al obtener nombre de usuario: " . $e->getMessage());
        return 'N/A';
    }
}

/**
 * Verifica si un usuario tiene permiso para realizar una acción
 * @param PDO $pdo Conexión a la base de datos
 * @param int $id_usuario ID del usuario
 * @param string $permiso Permiso a verificar
 * @return bool True si tiene permiso
 */
function tienePermiso($pdo, $id_usuario, $permiso) {
    try {
        $stmt = $pdo->prepare("SELECT p.nombre 
                              FROM permisos p
                              JOIN rol_permiso rp ON p.id_permiso = rp.id_permiso
                              JOIN usuarios u ON rp.id_rol = u.id_rol
                              WHERE u.id_usuario = ? AND p.nombre = ?");
        $stmt->execute([$id_usuario, $permiso]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error al verificar permiso: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el ID del usuario actualmente autenticado
 * @return int|null ID del usuario o null si no está autenticado
 */
function obtenerUsuarioActual() {
    session_start();
    return $_SESSION['id_usuario'] ?? null;
}

/**
 * Redirige a una página con un mensaje flash
 * @param string $url URL a redirigir
 * @param string $tipo Tipo de mensaje (success, error, warning, info)
 * @param string $mensaje Mensaje a mostrar
 */
function redirigirConMensaje($url, $tipo, $mensaje) {
    $_SESSION['mensaje_flash'] = [
        'tipo' => $tipo,
        'mensaje' => $mensaje
    ];
    header("Location: $url");
    exit;
}

/**
 * Muestra un mensaje flash y lo elimina de la sesión
 */
function mostrarMensajeFlash() {
    session_start();
    if (empty($_SESSION['mensaje_flash'])) return '';
    
    $mensaje = $_SESSION['mensaje_flash'];
    unset($_SESSION['mensaje_flash']);
    
    $clases = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $clase = $clases[$mensaje['tipo']] ?? 'alert-info';
    
    return '<div class="alert ' . $clase . ' alert-dismissible fade show" role="alert">'
         . htmlspecialchars($mensaje['mensaje'])
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
         . '</div>';
}