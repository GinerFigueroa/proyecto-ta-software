<?php
require_once '../../../php/database/conexion.php';
session_start();

// Verificar autenticación y rol (4 = Paciente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 4) {
    header("Location: ../../../bienvenido/login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$id_usuario = $_SESSION['id_usuario'];

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id_usuario = :id_usuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar actualización
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_apellido = trim($_POST['nombre_apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validaciones básicas
    if (empty($nombre_apellido)) {
        $error = 'El nombre completo es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        try {
            // Construir la consulta SQL
            $sql = "UPDATE usuarios SET 
                    nombre_apellido = :nombre_apellido,
                    email = :email,
                    telefono = :telefono";
            
            // Si hay nueva contraseña, la actualizamos
            $params = [
                ':nombre_apellido' => $nombre_apellido,
                ':email' => $email,
                ':telefono' => $telefono,
                ':id_usuario' => $id_usuario
            ];
            
            if (!empty($password)) {
                $sql .= ", usuario_clave = :password";
                $params[':password'] = $password; // En un sistema real, deberías hashear la contraseña
            }
            
            $sql .= " WHERE id_usuario = :id_usuario";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $mensaje = 'Perfil actualizado correctamente';
            
            // Actualizar datos en sesión
            $_SESSION['nombre'] = $nombre_apellido;
            
        } catch (PDOException $e) {
            $error = 'Error al actualizar el perfil: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Clínica Dental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .form-control:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="profile-header text-center">
            <div class="d-flex justify-content-center mb-3">
                <div class="position-relative">
                    <img src="../../../assets/img/default-profile.png" alt="Foto de perfil" class="profile-picture">
                    <button class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
            </div>
            <h2><?= htmlspecialchars($usuario['nombre_apellido']) ?></h2>
            <p class="mb-0">Paciente</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">
                <h4 class="card-title mb-4"><i class="fas fa-user-edit me-2"></i>Editar Perfil</h4>
                
                <form method="POST" action="perfil.php">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_apellido" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre_apellido" name="nombre_apellido" 
                                   value="<?= htmlspecialchars($usuario['nombre_apellido']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($usuario['email']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="usuario" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" 
                                   value="<?= htmlspecialchars($usuario['usuario_usuario']) ?>" disabled>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Dejar en blanco para no cambiar</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card shadow mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4"><i class="fas fa-info-circle me-2"></i>Información Adicional</h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de Registro</label>
                        <input type="text" class="form-control" 
                               value="<?= date('d/m/Y', strtotime($usuario['creado_en'])) ?>" disabled>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Último Acceso</label>
                        <input type="text" class="form-control" 
                               value="<?= $usuario['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca' ?>" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de contraseñas coincidentes
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    </script>
</body>
</html>