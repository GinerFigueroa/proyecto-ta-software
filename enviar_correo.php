<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function


if($_POST){
    $asunto=$_POST['asunto'];
    $descripcion=$_POST['descripcion'];
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader (created by composer, not included with PHPMailer)
require 'correo/vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings--correo
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = '2113110108@untels.edu.pe';                     //SMTP username
    $mail->Password   = 'vuzynpjcnzifchsl';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('remitente@gmail.com', 'Jorge Luis');
    $mail->addAddress('2113110108@untels.edu.pe', 'Giner');     //Add a recipient
    
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $asunto;
    $mail->Body    = $descripcion;
    
    $mail->send();
    header("location:index.html");
    echo "Correo enviado exitosamente";
} catch (Exception $e) {
    echo "Error en el envio: {$mail->ErrorInfo}";
}