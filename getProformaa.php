<?php
require_once '../../dompdf/autoload.inc.php'; // Ruta a Dompdf
use Dompdf\Dompdf;

// Crear instancia de Dompdf
$dompdf = new Dompdf();



    // Cargar HTML en Dompdf
    $dompdf->loadHtml($html);

    // Configurar tamaño de papel
    $dompdf->setPaper('A4', 'portrait');

    // Renderizar PDF
    $dompdf->render();

    // Mostrar el PDF en el navegador
    $dompdf->stream("factura-Report.pdf", ["Attachment" => false]);
} else {
    echo ".";
}
?>
