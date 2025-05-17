<?php


session_start();
require_once '../php/database/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validación básica
    if (empty($username) || empty($password)) {
        $error = "Usuario y contraseña son requeridos";
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        $sql = "SELECT u.id_usuario, u.nombre_apellido, u.usuario_clave, 
                       u.usuario_usuario, u.email, u.activo,
                       r.nombre as nombre_rol, r.id_rol
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE (u.email = :username OR u.usuario_usuario = :username)
                AND u.activo = 1
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() === 1) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Comparación directa (sin password_verify)
                if ($password === $usuario['usuario_clave']) {
                    // Configurar sesión
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre'] = $usuario['nombre_apellido'];
                    $_SESSION['rol'] = $usuario['nombre_rol'];
                    $_SESSION['id_rol'] = $usuario['id_rol'];
                    $_SESSION['username'] = $usuario['usuario_usuario'];
                    
                    // Actualizar último login
                    $updateSql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = :id";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bindParam(':id', $usuario['id_usuario'], PDO::PARAM_INT);
                    $updateStmt->execute();

                    // Redirigir según rol
                    header("Location: pantallaBienvenida.php");
                    exit();
                } else {
                    $error = "Credenciales incorrectas.";
                }
            } else {
                $error = "Usuario no encontrado o cuenta inactiva.";
            }
        } else {
            $error = "Error en la consulta a la base de datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Clínica Dental</title>
    <link rel="stylesheet" href="bienvenido.css">
</head>
<body>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Clínica Dental</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        }
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 450px;
            width: 100%;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 120px;
        }
        .btn-login {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            width: 100%;
            padding: 10px;
            font-weight: 600;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }
        .divider {
            position: relative;
            text-align: center;
            margin: 20px 0;
        }
        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #ddd;
            z-index: 1;
        }
        .divider-text {
            position: relative;
            display: inline-block;
            padding: 0 10px;
            background-color: white;
            z-index: 2;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="logo">
                        <i class="fas fa-tooth fa-3x mb-3" style="color: #2575fc;"></i>
                        <h3 class="mb-4">Clínica Dental</h3>
                    </div>

                    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ¡Registro exitoso! Por favor inicia sesión.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario o Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Ingresa tu usuario o email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                            <a href="../modulo/admin/restablecer_contraseñas/restablecer.php" class="float-end text-decoration-none">¿Olvidaste tu contraseña?</a>
                          
                        </div>
                        <button type="submit" class="btn btn-primary btn-login mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión
                        </button>
                        
                        <div class="text-center mt-3">
                            <p>¿No tienes una cuenta? 
                                <a href="../admin/usuario/usuario.html" class="text-decoration-none fw-bold">Regístrate</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="bienvenido.js"></script>
</body>
</html>

    
    
</body>
 

</html>

 