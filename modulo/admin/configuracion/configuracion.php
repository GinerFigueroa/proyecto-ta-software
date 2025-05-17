<?php
require_once '../../../php/database/conexion.php';

$mensaje = '';
$error = '';

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Recoger todos los parámetros enviados
        $parametros = $_POST['config'] ?? [];
        
        foreach ($parametros as $id => $valor) {
            // Obtener el tipo de configuración para validar
            $stmt = $db->prepare("SELECT tipo FROM configuracion WHERE id_config = ?");
            $stmt->execute([$id]);
            $tipo = $stmt->fetchColumn();
            
            // Validar según el tipo
            switch ($tipo) {
                case 'number':
                    $valor = filter_var($valor, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    break;
                case 'boolean':
                    $valor = $valor ? '1' : '0';
                    break;
                case 'json':
                    $valor = json_encode($valor);
                    break;
                default: // string
                    $valor = filter_var($valor, FILTER_SANITIZE_STRING);
            }
            
            // Actualizar en base de datos
            $stmt = $db->prepare("UPDATE configuracion SET valor = ? WHERE id_config = ?");
            $stmt->execute([$valor, $id]);
        }
        
        $db->commit();
        $mensaje = "Configuración actualizada correctamente";
    } catch (PDOException $e) {
        $db->rollBack();
        $error = "Error al actualizar la configuración: " . $e->getMessage();
        error_log("Error en configuración: " . $e->getMessage());
    }
}

// Obtener configuración actual
try {
    $stmt = $db->query("SELECT * FROM configuracion ORDER BY id_config");
    $configActual = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar la configuración";
    error_log("Error al cargar configuración: " . $e->getMessage());
    $configActual = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema | AlbumClinica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .config-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .config-header {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
            padding-bottom: 10px;
            color: #0d6efd;
        }
        .config-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #dee2e6;
        }
        .config-description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-cogs me-2"></i> Configuración del Sistema</h1>
            <a href="../../../bienvenido/pantallaBienvenida.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = '../../../bienvenido/pantallaBienvenida.php';
                }, 2000); // Redirige después de 2 segundos
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Sección Horarios -->
            <div class="config-section">
                <h3 class="config-header"><i class="fas fa-clock me-2"></i> Configuración de Horarios</h3>
                
                <?php foreach ($configActual as $item): ?>
                    <?php if (in_array($item['nombre'], ['horario_apertura', 'horario_cierre', 'duracion_cita_default'])): ?>
                        <div class="config-item">
                            <label for="config_<?= $item['id_config'] ?>" class="form-label">
                                <?= ucfirst(str_replace('_', ' ', $item['nombre'])) ?>
                            </label>
                            
                            <?php if ($item['tipo'] == 'string'): ?>
                                <input type="text" class="form-control" 
                                       id="config_<?= $item['id_config'] ?>" 
                                       name="config[<?= $item['id_config'] ?>]" 
                                       value="<?= htmlspecialchars($item['valor']) ?>">
                            <?php elseif ($item['tipo'] == 'number'): ?>
                                <input type="number" class="form-control" 
                                       id="config_<?= $item['id_config'] ?>" 
                                       name="config[<?= $item['id_config'] ?>]" 
                                       value="<?= htmlspecialchars($item['valor']) ?>">
                            <?php endif; ?>
                            
                            <?php if ($item['descripcion']): ?>
                                <div class="config-description"><?= htmlspecialchars($item['descripcion']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Sección Citas -->
            <div class="config-section">
                <h3 class="config-header"><i class="fas fa-calendar-alt me-2"></i> Configuración de Citas</h3>
                
                <?php foreach ($configActual as $item): ?>
                    <?php if (in_array($item['nombre'], ['dias_anticipacion_cancelacion'])): ?>
                        <div class="config-item">
                            <label for="config_<?= $item['id_config'] ?>" class="form-label">
                                <?= ucfirst(str_replace('_', ' ', $item['nombre'])) ?>
                            </label>
                            <input type="number" class="form-control" 
                                   id="config_<?= $item['id_config'] ?>" 
                                   name="config[<?= $item['id_config'] ?>]" 
                                   value="<?= htmlspecialchars($item['valor']) ?>">
                            <?php if ($item['descripcion']): ?>
                                <div class="config-description"><?= htmlspecialchars($item['descripcion']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Sección Notificaciones -->
            <div class="config-section">
                <h3 class="config-header"><i class="fas fa-envelope me-2"></i> Configuración de Notificaciones</h3>
                
                <?php foreach ($configActual as $item): ?>
                    <?php if (in_array($item['nombre'], ['email_notificaciones'])): ?>
                        <div class="config-item">
                            <label for="config_<?= $item['id_config'] ?>" class="form-label">
                                <?= ucfirst(str_replace('_', ' ', $item['nombre'])) ?>
                            </label>
                            <input type="email" class="form-control" 
                                   id="config_<?= $item['id_config'] ?>" 
                                   name="config[<?= $item['id_config'] ?>]" 
                                   value="<?= htmlspecialchars($item['valor']) ?>">
                            <?php if ($item['descripcion']): ?>
                                <div class="config-description"><?= htmlspecialchars($item['descripcion']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
