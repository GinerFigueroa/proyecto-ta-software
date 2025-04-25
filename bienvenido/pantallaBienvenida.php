<?php
require_once 'auth.php';
verificarAutenticacion();

$rol = obtenerRolUsuario();
$nombre = $_SESSION['nombre']; // Corregido aquí
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido - Clínica Dental</title>
    <link rel="stylesheet" href="bienvenido.css">
</head>
<body class="<?= strtolower($rol) ?>">
    <header>
        <h1>Bienvenido, <?= htmlspecialchars($nombre) ?></h1>
        <p>Rol: <?= htmlspecialchars($rol) ?></p>
    </header>
    
    <div class="dashboard">
        <?php 
        switch($rol): 
            case 'Administrador': ?>
                <div class="card">
                    <a href="modules/admin/usuarios.php">Gestionar Usuarios</a>
                </div>
                <div class="card">
                    <a href="modules/admin/restablecer.php">Restablecer Contraseñas</a>
                </div>
                <div class="card">
                    <a href="modules/admin/configuracion.php">Configuración del Sistema</a>
                </div>
                <div class="card">
                    <a href="modules/admin/reportes.php">Reportes</a>
                </div>
                <?php break; ?>
                
            <?php case 'Dentista': ?>
                <div class="card">
                    <a href="modules/dentista/agenda.php">Mi Agenda</a>
                </div>
                <div class="card">
                    <a href="modules/dentista/pacientes.php">Mis Pacientes</a>
                </div>
                <div class="card">
                    <a href="modules/dentista/tratamientos.php">Tratamientos</a>
                </div>
                <div class="card">
                    <a href="modules/dentista/historiales.php">Historiales Clínicos</a>
                </div>
                <?php break; ?>
                
            <?php case 'Recepcionista': ?>
                <div class="card">
                    <a href="modules/recepcion/citas.php">Registrar Cita</a>
                </div>
                <div class="card">
                    <a href="modules/recepcion/pacientes.php">Registrar Paciente</a>
                </div>
                <div class="card">
                    <a href="modules/recepcion/agenda.php">Ver Agenda</a>
                </div>
                <div class="card">
                    <a href="modules/recepcion/pagos.php">Registrar Pagos</a>
                </div>
                <?php break; ?>
                
            <?php case 'Paciente': ?>
                <div class="card">
                    <a href="../admin/citas/login.php">Mis Citas</a>
                    
                </div>
                <div class="card">
                    <a href="modules/paciente/historial.php">Mi Historial</a>
                </div>
                <div class="card">
                    <a href="modules/paciente/pagos.php">Mis Pagos</a>
                </div>
                <div class="card">
                    <a href="modules/paciente/perfil.php">Mi Perfil</a>
                </div>
                <?php break; ?>
                
            <?php default: ?>
                <div class="card">
                    <p>No tienes opciones disponibles para tu rol.</p>
                </div>
        <?php endswitch; ?>
    </div>
    
    <a href="logout.php" class="logout">Cerrar Sesión</a>
    
    <script src="bienvenido.js"></script>
</body>
</html>