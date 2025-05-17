<?php
require_once '../../../php/database/conexion.php';




// Verificar token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header('Location: restablecer.php');
    exit;
}

try {
    // Verificar token válido
    $stmt = $db->prepare("
        SELECT r.id, r.id_usuario, r.expiracion, u.email 
        FROM recuperacion_contraseña r
        JOIN usuarios u ON r.id_usuario = u.id_usuario
        WHERE r.token = ? AND r.usado = 0 AND r.expiracion > NOW()
    ");
    $stmt->execute([$token]);
    $solicitud = $stmt->fetch();

    if (!$solicitud) {
        $error = "El enlace de recuperación no es válido o ha expirado.";
    }

    // Procesar cambio de contraseña
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $solicitud) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($password) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } else {
            // Actualizar contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $db->beginTransaction();
            
            try {
                // Actualizar contraseña del usuario
                $stmt = $db->prepare("UPDATE usuarios SET usuario_clave = ? WHERE id_usuario = ?");
                $stmt->execute([$hashed_password, $solicitud['id_usuario']]);
                
                // Marcar token como usado
                $stmt = $db->prepare("UPDATE recuperacion_contraseña SET usado = 1 WHERE id = ?");
                $stmt->execute([$solicitud['id']]);
                
                $db->commit();
                $success = "Tu contraseña ha sido actualizada correctamente. Ahora puedes iniciar sesión.";
            } catch (Exception $e) {
                $db->rollBack();
                $error = "Error al actualizar la contraseña. Inténtalo de nuevo.";
                error_log("Error al actualizar contraseña: " . $e->getMessage());
            }
        }
    }
} catch (PDOException $e) {
    $error = "Error al procesar la solicitud. Inténtalo más tarde.";
    error_log("Error en confirmación de recuperación: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | AlbumClinica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            max-width: 500px;
            margin: 50px auto;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-key me-2"></i> Restablecer Contraseña</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <div class="text-center mt-3">
                        <a href="../../login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                        </a>
                    </div>
                <?php elseif ($solicitud): ?>
                    <p>Hola, estás restableciendo la contraseña para <strong><?= htmlspecialchars($solicitud['email']) ?></strong>. Ingresa una nueva contraseña segura.</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            <div class="form-text">Mínimo 8 caracteres</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Guardar Nueva Contraseña
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">El enlace de recuperación no es válido o ha expirado.</div>
                    <div class="text-center mt-3">
                        <a href="restablecer.php" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i> Solicitar Nuevo Enlace
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
