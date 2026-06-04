<?php
// src/php-app/index.php

// 1. Incluimos la conexión a la base de datos de PostgreSQL
require_once __DIR__ . '/config/database.php';

try {
    // 2. Consultamos los logs ordenados por la fecha más reciente
    $sql = "SELECT id, modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_nuevos, direccion_ip, fecha_registro 
            FROM audit_logs 
            ORDER BY fecha_registro DESC";
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error al consultar la bitácora: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo 2 - Sistema de Auditoría y Bitácora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-header-custom { background-color: #1e3a8a; color: white; }
        pre { background-color: #f1f5f9; padding: 8px; border-radius: 4px; font-size: 0.85rem; max-height: 150px; overflow-y: auto; }
        .badge-dotnet { background-color: #512bd4; }
        .badge-php { background-color: #777bb4; }
        .badge-node { background-color: #339933; }
    </style>
</head>
<body>

<div class="container-fluid py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold text-dark">📋 Bitácora del Sistema</h1>
                <p class="text-muted">Módulo 2: Infraestructura de Auditoría Distribuida (PHP + PostgreSQL)</p>
            </div>
            <span class="badge bg-success p-2">Conectado a PostgreSQL (Puerto 5433)</span>
        </div>

        <div class="card shadow-sm">
            <div class="card-header card-header-custom py-3">
                <h5 class="mb-0 fw-semibold">Registros de Auditoría Recientes (Datos Semilla)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 15%">Origen</th>
                                <th style="width: 12%">Usuario</th>
                                <th style="width: 12%">Acción</th>
                                <th style="width: 15%">Tabla / Registro</th>
                                <th style="width: 25%">Detalle (JSONB)</th>
                                <th style="width: 16%">Fecha / IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No se encontraron registros en la bitácora.</td>
                                endforeach; ?>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    // Asignar color de badge según el módulo de origen
                                    $badgeClass = 'bg-secondary';
                                    if (strpos($log['modulo_origen'], 'DotNet') !== false) $badgeClass = 'badge-dotnet';
                                    if (strpos($log['modulo_origen'], 'PHP') !== false) $badgeClass = 'badge-php';
                                    if (strpos($log['modulo_origen'], 'NodeJS') !== false) $badgeClass = 'badge-node';
                                ?>
                                    <tr>
                                        <td><strong>#<?php echo $log['id']; ?></strong></td>
                                        <td><span class="badge <?php echo $badgeClass; ?> d-block p-2"><?php echo htmlspecialchars($log['modulo_origen']); ?></span></td>
                                        <td><code class="text-dark fw-bold"><?php echo htmlspecialchars($log['usuario']); ?></code></td>
                                        <td><span class="badge bg-light text-dark border fw-semibold"><?php echo htmlspecialchars($log['accion']); ?></span></td>
                                        <td>
                                            <small class="d-block"><strong>Tabla:</strong> <?php echo htmlspecialchars($log['tabla_afectada'] ?? 'N/A'); ?></small>
                                            <small class="text-muted"><strong>ID Ref:</strong> <?php echo htmlspecialchars($log['registro_id'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($log['valores_nuevos']): ?>
                                                <?php 
                                                    // Decodificamos el JSON de Postgres para formatearlo bonito en pantalla
                                                    $jsonDecoded = json_decode($log['valores_nuevos'], true);
                                                    $jsonPretty = json_encode($jsonDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                                ?>
                                                <pre><code><?php echo htmlspecialchars($jsonPretty); ?></code></pre>
                                            <?php else: ?>
                                                <span class="text-muted italic">Sin datos adicionales</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="d-block fw-semibold text-secondary"><?php echo $log['fecha_registro']; ?></small>
                                            <small class="badge bg-dark-subtle text-dark-emphasis mt-1">🌐 IP: <?php echo htmlspecialchars($log['direccion_ip']); ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<footer class="footer mt-auto py-3 bg-white border-top text-center text-muted">
    <div class="container">
        <small>© 2026 - Proyecto Integrador de Contaduría y TI</small>
    </div>
</footer>

</body>
</html>