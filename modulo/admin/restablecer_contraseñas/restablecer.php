<?php
require_once '../../../php/database/conexion.php';

session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: ../../index.php');
    exit;
}

$error = '';
$success = '';
$password_temp = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    try {
        // Verificar si el email existe
        $stmt = $db->prepare("SELECT id_usuario, email FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
       if ($usuario) {
    // Generar contraseña temporal (8 caracteres alfanuméricos)
    $password_temp = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 8);
    
    // ACTUALIZAR CONTRASEÑA EN TEXTO PLANO (sin hashear)
    $stmt = $db->prepare("UPDATE usuarios SET usuario_clave = ? WHERE id_usuario = ?");
    $stmt->execute([$password_temp, $usuario['id_usuario']]); // Almacena directamente
    
    $success = "Contraseña temporal generada  cámbiala después de iniciar sesión.:";
            
      
        } else {
            $error = "No existe una cuenta con ese email.";
        }
    } catch (PDOException $e) {
        $error = "Error al procesar la solicitud. Inténtalo más tarde.";
        error_log("Error en recuperación: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | AlbumClinica</title>
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
        .password-display {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 5px;
            font-size: 1.2rem;
            text-align: center;
            margin: 15px 0;
            font-family: monospace;
        }
        .copy-btn {
            cursor: pointer;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-key me-2"></i> Recuperar Contraseña</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <div class="password-display">
                        <span id="temp-password"><?= htmlspecialchars($password_temp) ?></span>
                        <i class="fas fa-copy copy-btn ms-2" onclick="copyToClipboard()" title="Copiar contraseña"></i>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Por seguridad, cambia esta contraseña después de iniciar sesión.
                    </div>
                    <div class="text-center">
                        <a href="../../../bienvenido/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Ir a Inicio de Sesión
                        </a>
                    </div>
                <?php else: ?>
                    <p>Ingresa tu dirección de email para generar una contraseña temporal.</p>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-2"></i> Generar Contraseña Temporal
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="mt-3 text-center">
                    <a href="../../../bienvenido/login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard() {
            const password = document.getElementById('temp-password').textContent;
            navigator.clipboard.writeText(password).then(() => {
                alert('Contraseña copiada al portapapeles');
            });
        }
    </script>
</body>
</html>
