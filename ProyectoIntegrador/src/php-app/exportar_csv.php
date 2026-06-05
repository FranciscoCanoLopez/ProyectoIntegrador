<?php
// src/php-app/exportar_csv.php

require_once __DIR__ . '/config/database.php';

try {
    // 1. Consultar todos los registros ordenados por fecha
    $sql = "SELECT id, modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_nuevos, direccion_ip, fecha_registro 
            FROM audit_logs 
            ORDER BY fecha_registro DESC";
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();

    // 2. Establecer las cabeceras HTTP para forzar la descarga del archivo CSV
    $filename = "Reporte_Auditoria_" . date("Y-m-d_H-i-s") . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '";');

    // 3. Abrir la salida estándar de PHP para escribir los datos
    $output = fopen('php://output', 'w');

    // 4. Inyectar el BOM UTF-8 para que Excel reconozca los acentos y caracteres especiales automáticamente
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // 5. Definir los títulos de las columnas (Cabecera del reporte)
    fputcsv($output, [
        'ID REGISTRO', 
        'MICROSERVICIO/MÓDULO ORIGEN', 
        'OPERADOR/USUARIO', 
        'ACCIÓN EJECUTADA', 
        'TABLA AFECTADA', 
        'ID DE REFERENCIA', 
        'VALORES DEL EVENTO (JSON RAW)', 
        'DIRECCIÓN IP', 
        'FECHA Y HORA DEL REGISTRO'
    ]);

    // 6. Recorrer la información de PostgreSQL e inyectarla en las filas del documento
    foreach ($logs as $log) {
        // Limpiar saltos de línea en el JSON para que no rompa la estructura de celdas en Excel
        $jsonRaw = $log['valores_nuevos'] ? str_replace(["\r", "\n", "\t"], ' ', $log['valores_nuevos']) : 'N/A';

        fputcsv($output, [
            $log['id'],
            $log['modulo_origen'],
            $log['usuario'],
            $log['accion'],
            $log['tabla_afectada'] ?? 'N/A',
            $log['registro_id'] ?? 'N/A',
            $jsonRaw,
            $log['direccion_ip'],
            $log['fecha_registro']
        ]);
    }

    // 7. Cerrar el flujo de datos
    fclose($output);
    exit();

} catch (Exception $e) {
    die("Error crítico al generar el reporte de exportación: " . $e->getMessage());
}