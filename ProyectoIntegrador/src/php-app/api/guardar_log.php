<?php
// api/guardar_log.php
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

// Validar que la petición sea estrictamente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido. Use POST."]);
    exit;
}

// Leer el JSON crudo que envía el cliente (.NET, Node.js o una herramienta de pruebas)
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "JSON inválido o malformado."]);
    exit;
}

try {
    // Preparar la consulta SQL (Protección contra SQL Injection)
    $sql = "INSERT INTO audit_logs 
            (modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_anteriores, valores_nuevos, direccion_ip) 
            VALUES (:modulo, :usuario, :accion, :tabla, :registro_id, :anterior, :nuevo, :ip)";
            
    $stmt = $pdo->prepare($sql);
    
    // Ejecutar pasando los valores mapeados. Los campos JSON se guardan codificados en texto.
    $stmt->execute([
        ':modulo'      => $data['modulo_origen'] ?? 'Desconocido',
        ':usuario'     => $data['usuario'] ?? 'Anonimo',
        ':accion'      => $data['accion'] ?? 'ACCION_DESCONOCIDA',
        ':tabla'       => $data['tabla_afectada'] ?? null,
        ':registro_id' => $data['registro_id'] ?? null,
        ':anterior'    => isset($data['valores_anteriores']) ? json_encode($data['valores_anteriores']) : null,
        ':nuevo'       => isset($data['valores_nuevos']) ? json_encode($data['valores_nuevos']) : null,
        ':ip'          => $_SERVER['REMOTE_ADDR'] // Captura automáticamente la IP del contenedor solicitante
    ]);

    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Log de auditoría guardado exitosamente."]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno del servidor: " . $e->getMessage()]);
}