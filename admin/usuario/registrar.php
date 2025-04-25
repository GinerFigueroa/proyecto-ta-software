<?php
require_once '../../php/database/conexion.php';

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanitizar los datos del formulario
    $nombre_apellido = htmlspecialchars(trim($_POST['nombre_apellido']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $usuario = htmlspecialchars(trim($_POST['usuario_usuario']));
    $password = trim($_POST['usuario_clave']);
    $telefono = htmlspecialchars(trim($_POST['telefono'] ?? ''));
    $id_rol = intval($_POST['id_rol']);
    
    // Validaciones básicas
    $errores = [];
    
    if (empty($nombre_apellido)) {
        $errores[] = "El nombre y apellido son obligatorios";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (empty($usuario)) {
        $errores[] = "El nombre de usuario es obligatorio";
    }
    
    if (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    try {
        // Verificar si el usuario o email ya existen
        $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = ? OR usuario_usuario = ?");
        $stmt->execute([$email, $usuario]);
        
        if ($stmt->rowCount() > 0) {
            $errores[] = "El correo electrónico o nombre de usuario ya están registrados";
        }
        
        // Si no hay errores, proceder con el registro
        if (empty($errores)) {
            // Insertar el nuevo usuario CON CONTRASEÑA SIN ENCRIPTAR (NO RECOMENDADO)
            $stmt = $db->prepare("INSERT INTO usuarios 
                                 (id_rol, email, usuario_clave, usuario_usuario, nombre_apellido, telefono, activo) 
                                 VALUES (?, ?, ?, ?, ?, ?, 1)");
            
            $stmt->execute([$id_rol, $email, $password, $usuario, $nombre_apellido, $telefono]);
            
            // Redirigir al login con mensaje de éxito
            header("Location: ../../bienvenido/login.php?registro=exitoso");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error en el registro: " . $e->getMessage());
        $errores[] = "Ocurrió un error al procesar tu registro. Por favor intenta nuevamente.";
    }
    
    // Si hay errores, mostrarlos en el formulario de registro
    if (!empty($errores)) {
        session_start();
        $_SESSION['errores_registro'] = $errores;
        $_SESSION['datos_registro'] = $_POST;
        header("Location: ../../bienvenido/registro.php");
        exit();
    }
} else {
    // Si no es POST, redirigir al login
    header("Location: ../../bienvenido/login.php");
    exit();
}