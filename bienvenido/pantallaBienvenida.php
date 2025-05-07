<?php
require_once 'auth.php';
verificarAutenticacion();

$rol = obtenerRolUsuario();
$nombre = $_SESSION['nombre'] ?? 'Usuario'; // Valor por defecto si no existe
$id_usuario = $_SESSION['id_usuario'] ?? null; // ID de usuario para personalización
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Clínica Dental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bienvenido.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?= strtolower($rol) ?>">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tooth me-2"></i>Clínica Dental
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><?= htmlspecialchars($nombre) ?></span>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2 class="text-center">
                    <i class="fas fa-user-circle me-2"></i>Panel de <?= htmlspecialchars($rol) ?>
                </h2>
                <p class="text-center text-muted">Bienvenido al sistema de gestión dental</p>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php 
            switch($rol): 
                case 'Administrador': ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-users-cog fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title">Gestión de Usuarios</h5>
                                <p class="card-text">Administra todos los usuarios del sistema</p>
                                <a href="modules/admin/usuarios.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-key fa-3x mb-3 text-warning"></i>
                                <h5 class="card-title">Restablecer Contraseñas</h5>
                                <p class="card-text">Ayuda a usuarios con acceso</p>
                                <a href="modules/admin/restablecer.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-cogs fa-3x mb-3 text-info"></i>
                                <h5 class="card-title">Configuración</h5>
                                <p class="card-text">Ajustes del sistema</p>
                                <a href="modules/admin/configuracion.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-3x mb-3 text-success"></i>
                                <h5 class="card-title">Reportes</h5>
                                <p class="card-text">Genera reportes del sistema</p>
                                <a href="modules/admin/reportes.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php break; ?>
                    
                <?php case 'Dentista': ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title">Mi Agenda</h5>
                                <p class="card-text">Consulta tu agenda de citas</p>
                                <a href="modules/dentista/agenda.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-injured fa-3x mb-3 text-info"></i>
                                <h5 class="card-title">Mis Pacientes</h5>
                                <p class="card-text">Gestiona tus pacientes</p>
                                <a href="../modulo/dentista/pacientes/pacientes.html" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-teeth-open fa-3x mb-3 text-warning"></i>
                                <h5 class="card-title">Tratamientos</h5>
                                <p class="card-text">Catálogo de tratamientos</p>
                                <a href="../modulo/dentista/tratamientos/tratamientos.html" class="btn btn-primary stretched-link">
                        
                                
                                Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-file-medical fa-3x mb-3 text-success"></i>
                                <h5 class="card-title">Historiales</h5>
                                <p class="card-text">Registros clínicos</p>
                                <a href="../modulo/dentista/historiales/historiales.html" class="btn btn-primary stretched-link">
                                
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php break; ?>
                    
                <?php case 'Recepcionista': ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-plus fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title">Registrar Cita</h5>
                                <p class="card-text">Agenda nuevas citas</p>
                                <a href="../modulo/recepcion/citas/crear_cita/cita_regitros.html" class="btn btn-primary stretched-link">
                               
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-3x mb-3 text-info"></i>
                                <h5 class="card-title">Registrar Paciente</h5>
                                <p class="card-text">Nuevos pacientes</p>
                                <a href="../modulo/recepcion/pacientes/pacientes.html" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                  
                                  
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-3x mb-3 text-warning"></i>
                                <h5 class="card-title">Ver Agenda</h5>
                                <p class="card-text">Consulta la agenda completa</p>
                                <a href="../modulo/recepcion/agenda/agenda.html" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-money-bill-wave fa-3x mb-3 text-success"></i>
                                <h5 class="card-title">Registrar Pagos</h5>
                                <p class="card-text">Gestión de pagos</p>
                                <a href="../modulo/recepcion/pagos/pagos.html" class="btn btn-primary stretched-link">
                               
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php break; ?>
                    
                <?php case 'Paciente': ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title">Mis Citas</h5>
                                <p class="card-text">Consulta tus citas programadas</p>
                                <a href="../modulo/paciente/misCitas/MortrarCitas.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-file-medical-alt fa-3x mb-3 text-info"></i>
                                <h5 class="card-title">Mi Historial</h5>
                                <p class="card-text">Consulta tu historial médico</p>
                                <a href="../modulo/paciente/miHistorial/historial.php" class="btn btn-primary stretched-link">
                                  
                                Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-receipt fa-3x mb-3 text-warning"></i>
                                <h5 class="card-title">Mis Pagos</h5>
                                <p class="card-text">Consulta tus pagos</p>
                                <a href="../modulo/paciente/miPagos/pagos.php" class="btn btn-primary stretched-link">
                                
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-edit fa-3x mb-3 text-success"></i>
                                <h5 class="card-title">Mi Perfil</h5>
                                <p class="card-text">Actualiza tu información</p>
                                <a href="../modulo/paciente/miPerfil/perfil.php" class="btn btn-primary stretched-link">
                                    Acceder <i class="fas fa-arrow-right ms-1"></i>

                                   
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php break; ?>
                    
                <?php default: ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No tienes opciones disponibles para tu rol. Contacta al administrador.
                        </div>
                    </div>
            <?php endswitch; ?>
        </div>
    </div>

    <footer class="bg-dark text-white py-3 mt-5">
        <div class="container text-center">
            <p class="mb-0">Clínica Dental &copy; <?= date('Y') ?></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/bienvenido.js"></script>
</body>
</html>