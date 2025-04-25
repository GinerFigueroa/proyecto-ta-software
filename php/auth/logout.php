<?php
header('Content-Type: application/json');
session_start();

// Para autenticación por sesiones
session_unset();
session_destroy();

// Para JWT (el cliente debe eliminar el token)
echo json_encode(['success' => true]);
?>