<?php
require_once '../../../php/database/conexion.php';

header('Content-Type: application/json');

try {
    // Nombre del archivo de backup
    $fecha = date('Y-m-d_His');
    $nombreArchivo = "backup_albumclinica_$fecha.sql";
    $rutaBackup = "../../backups/$nombreArchivo";
    
    // Crear directorio si no existe
    if (!file_exists('../../backups')) {
        mkdir('../../backups', 0755, true);
    }
    
    // Comando para generar backup (MySQL)
    $command = "mysqldump --user={$db->user} --password={$db->pass} --host={$db->host} {$db->db} > $rutaBackup";
    system($command, $output);
    
    if ($output === 0) {
        echo json_encode([
            'success' => true,
            'file' => $nombreArchivo,
            'timestamp' => date('Y-m-d H:i:s') // 2025-05-10 01:51:58
        ]);
    } else {
        throw new Exception("Error al ejecutar mysqldump");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
