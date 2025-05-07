<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'correo/vendor/autoload.php';

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos requeridos
    $required = ['nombre', 'telefono', 'email', 'tratamiento', 'fecha', 'doctor'];
    $missing = array_diff($required, array_keys($_POST));
    
    if (!empty($missing)) {
        header('Location: index.php?error=campos_faltantes');
        exit();
    }

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '2113110108@untels.edu.pe'; // Reemplazar con variable de entorno
        $mail->Password = 'qxtokeygitczcnhu'; // Reemplazar con variable de entorno
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Configuración del correo
        $mail->setFrom('2113110108@untels.edu.pe', 'Clínica ZAZDENT');
        $mail->addAddress('2113110108@untels.edu.pe');
        $mail->addReplyTo($_POST['email'], $_POST['nombre']);

        // Asunto y cuerpo del mensaje
        $mail->Subject = 'Nueva solicitud de cita - ' . htmlspecialchars($_POST['nombre']);
        
        $cuerpoHTML = "<h2>Nueva solicitud de cita</h2>
            <p><strong>Nombre:</strong> " . htmlspecialchars($_POST['nombre']) . "</p>
            <p><strong>Teléfono:</strong> " . htmlspecialchars($_POST['telefono']) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($_POST['email']) . "</p>
            <p><strong>Tratamiento:</strong> " . htmlspecialchars($_POST['tratamiento']) . "</p>
            <p><strong>Fecha solicitada:</strong> " . htmlspecialchars($_POST['fecha']) . "</p>
            <p><strong>Doctor preferido:</strong> " . htmlspecialchars($_POST['doctor']) . "</p>
            <p><strong>Mensaje:</strong><br>" . nl2br(htmlspecialchars($_POST['mensaje'])) . "</p>
            <hr>
            <p><small>Solicitud recibida el " . date('d/m/Y H:i') . "</small></p>";
        
        $mail->isHTML(true);
        $mail->Body = $cuerpoHTML;
        $mail->AltBody = strip_tags(str_replace("<br>", "\n", $cuerpoHTML));

        // Envío del correo
        $mail->send();
        
        // Redirección con mensaje de éxito
        header('Location: index.html?success=cita_enviada');
        exit();
        
    } catch (Exception $e) {
        // Registrar error y redirigir
        error_log('Error al enviar correo: ' . $e->getMessage());
        header('Location: index.html?error=envio_fallido&message=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si alguien accede directamente al script sin enviar formulario
    header('Location: index.html');
    exit();
}
?>


<?php
// Al inicio del archivo
$mensaje_exito = '';
$mensaje_error = '';

if (isset($_GET['success']) && $_GET['success'] === 'cita_enviada') {
    $mensaje_exito = '¡Tu cita ha sido solicitada con éxito! Nos pondremos en contacto contigo pronto.';
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'campos_faltantes':
            $mensaje_error = 'Por favor completa todos los campos requeridos.';
            break;
        case 'envio_fallido':
            $mensaje_error = 'Hubo un error al enviar tu solicitud. Por favor inténtalo de nuevo.';
            if (isset($_GET['message'])) {
                error_log('Error detallado: ' . urldecode($_GET['message']));
            }
            break;
        default:
            $mensaje_error = 'Ocurrió un error inesperado.';
    }
}
?>

<!-- En tu sección de formulario, muestra los mensajes -->
<?php if ($mensaje_exito): ?>
<div class="alert alert-success">
    <?php echo $mensaje_exito; ?>
</div>
<?php endif; ?>

<?php if ($mensaje_error): ?>
<div class="alert alert-danger">
    <?php echo $mensaje_error; ?>
</div>
<?php endif; ?>

<!-- Tu formulario aquí -->