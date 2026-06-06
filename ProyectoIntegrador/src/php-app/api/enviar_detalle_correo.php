<?php
// src/php-app/api/enviar_detalle_correo.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Cargar base de datos
    require_once __DIR__ . '/../config/database.php';
    
    // 2. Cargar autoload de vendor
    $rutaAutoload = __DIR__ . '/../vendor/autoload.php';

    if (!file_exists($rutaAutoload)) {
        http_response_code(500);
        echo json_encode([
            "status" => "error", 
            "message" => "Error de infraestructura: No se localizó 'vendor/autoload.php'"
        ]);
        exit;
    }

    require_once $rutaAutoload; 

    // --- CREDENCIALES INYECTADAS POR DOCKER ---
    // Al mapear el env_file en docker-compose, $_ENV se llena automáticamente
    $smtp_user = !empty($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : '';
    $smtp_pass = !empty($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : '';
    $smtp_host = !empty($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'smtp.gmail.com';
    $smtp_port = !empty($_ENV['SMTP_PORT']) ? (int)$_ENV['SMTP_PORT'] : 587;

    // Validar de forma preventiva que Docker haya inyectado las credenciales
    if (empty($smtp_user) || empty($smtp_pass)) {
        throw new Exception("Docker no ha inyectado las variables de entorno. Verifica la propiedad env_file en tu docker-compose.yml.");
    }

    // Capturar parámetros del Frontend
    $input = json_decode(file_get_contents('php://input'), true);
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    $email = filter_var($input['email'] ?? null, FILTER_VALIDATE_EMAIL);

    if (!$id || !$email) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Datos de entrada o correo electrónico no válidos."]);
        exit;
    }

    // Consultar el registro de auditoría en PostgreSQL
    $sql = "SELECT id, modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_nuevos, direccion_ip, fecha_registro 
            FROM audit_logs WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $log = $stmt->fetch();

    if (!$log) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "El registro de auditoría #$id no existe."]);
        exit;
    }

    $jsonPretty = "Sin datos adicionales insertados en este evento.";
    if ($log['valores_nuevos']) {
        $jsonDecoded = json_decode($log['valores_nuevos'], true);
        $jsonPretty = json_encode($jsonDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // Diseñar el cuerpo del correo
    $cuerpoHtml = "
    <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 30px; color: #334155;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;'>
            <div style='background-color: #1e293b; padding: 20px; text-align: center; color: #ffffff;'>
                <h2 style='margin: 0; font-size: 20px; font-weight: 600;'>🔍 Reporte de Inspección de Auditoría</h2>
                <p style='margin: 5px 0 0 0; font-size: 13px; color: #94a3b8;'>Registro Interno #{$log['id']}</p>
            </div>
            <div style='padding: 25px;'>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;'>Origen del Módulo</td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 15px; color: #0f172a; text-align: right; font-weight: 600;'>".htmlspecialchars($log['modulo_origen'])."</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;'>Usuario Operador</td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 15px; color: #2563eb; text-align: right; font-family: monospace; font-weight: bold;'>".htmlspecialchars($log['usuario'])."</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;'>Acción Ejecutada</td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; text-align: right;'><span style='background-color: #f1f5f9; color: #334155; padding: 4px 8px; border-radius: 4px; font-size: 13px; font-weight: bold; border: 1px solid #cbd5e1;'>".htmlspecialchars($log['accion'])."</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;'>Fecha y Hora</td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #475569; text-align: right;'>{$log['fecha_registro']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b;'>Dirección IP</td>
                        <td style='padding: 10px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #475569; text-align: right; font-family: monospace;'>{$log['direccion_ip']}</td>
                    </tr>
                </table>
                <div style='margin-top: 25px;'>
                    <h4 style='margin: 0 0 10px 0; font-size: 14px; color: #0f172a;'>📦 Valores Nuevos / Payload (JSONB):</h4>
                    <pre style='background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; color: #334155; overflow-x: auto; white-space: pre-wrap; word-wrap: break-word;'>".htmlspecialchars($jsonPretty)."</pre>
                </div>
            </div>
            <div style='background-color: #f1f5f9; padding: 15px; text-align: center; border-top: 1px solid #e2e8f0;'>
                <small style='color: #64748b;'>© 2026 - Proyecto Integrador de Contaduría y TI</small>
            </div>
        </div>
    </div>";

    // Configuración del servicio PHPMailer
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->isSMTP();
    $mail->Host       = $smtp_host; 
    $mail->SMTPAuth   = true;                                 
    $mail->Username   = $smtp_user;        
    $mail->Password   = $smtp_pass;        
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       
    $mail->Port       = $smtp_port;                                  

    $mail->setFrom($smtp_user, 'Inspector de Bitácora TI');
    $mail->addAddress($email);                                

    $mail->isHTML(true);
    $mail->Subject = "Reporte de Auditoría: Registro #{$log['id']} - {$log['modulo_origen']}";
    $mail->Body    = $cuerpoHtml;
    $mail->AltBody = "Reporte del Registro #{$log['id']}\nMódulo: {$log['modulo_origen']}";

    $mail->send();

    echo json_encode(["status" => "success", "message" => "El reporte ha sido enviado correctamente por correo electrónico."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error en la Base de Datos de Auditoría: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Inconveniente interno en el servidor: " . $e->getMessage()]);
}