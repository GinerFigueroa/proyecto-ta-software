<?php
function verificarAutenticacion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: login.php");
        exit();
    }
}

function obtenerRolUsuario() {
    if (isset($_SESSION['rol'])) {
        return $_SESSION['rol'];
    }
    return 'Invitado';
}

function esAdministrador() {
    return ($_SESSION['id_rol'] ?? 0) === 1;
}

function esDentista() {
    return ($_SESSION['id_rol'] ?? 0) === 2;
}

function esRecepcionista() {
    return ($_SESSION['id_rol'] ?? 0) === 3;
}

function esPaciente() {
    return ($_SESSION['id_rol'] ?? 0) === 4;
}

// Función para verificar permisos
function tienePermiso($rolRequerido) {
    $rolActual = $_SESSION['id_rol'] ?? 0;
    
    // Administrador tiene acceso a todo
    if ($rolActual === 1) return true;
    
    // Convertir rol requerido a ID si es string
    if (is_string($rolRequerido)) {
        $roles = [
            'Administrador' => 1,
            'Dentista' => 2,
            'Recepcionista' => 3,
            'Paciente' => 4
        ];
        $rolRequerido = $roles[$rolRequerido] ?? 0;
    }
    
    return $rolActual === $rolRequerido;
}
?>