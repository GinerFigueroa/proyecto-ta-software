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


    <div class="login-container">
    <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
    <div class="alert alert-success">
        ¡Registro exitoso! Por favor inicia sesión.
    </div>
<?php endif; ?>
        <h2>Login</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label>Usuario o Email:</label><br>
            <input type="text" name="username" required><br><br>

            <label>Contraseña:</label><br>
            <input type="password" name="password" required><br><br>

            <input type="submit" value="Iniciar sesión">
            <div class="tex">
    <a href="../admin/usuario/usuario.html" style="color: blue; text-decoration: none;">
    
        ¿No tienes una cuenta? Regístrate
    </a>
</div>
            
        
        </form>
    </div>
    <script src="bienvenido.js"></script>

    
    
</body>
 

</html>

 