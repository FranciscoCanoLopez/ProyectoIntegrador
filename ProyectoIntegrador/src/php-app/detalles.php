<?php
// src/php-app/detalles.php

require_once __DIR__ . '/config/database.php';

// 1. Validar que se reciba un ID válido por la URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Error: ID de registro no válido o no especificado.");
}

try {
    // 2. Consultar el registro específico en la tabla audit_logs
    $sql = "SELECT id, modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_nuevos, direccion_ip, fecha_registro 
            FROM audit_logs 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $log = $stmt->fetch();

    if (!$log) {
        die("Error: El registro de auditoría con ID #$id no existe.");
    }

    // Procesar el JSONB para mostrarlo bonito
    $jsonPretty = "";
    if ($log['valores_nuevos']) {
        $jsonDecoded = json_decode($log['valores_nuevos'], true);
        $jsonPretty = json_encode($jsonDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    die("Error al consultar el detalle del log: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Registro #<?php echo $log['id']; ?> - Auditoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/public/css/estilos.css" rel="stylesheet">
</head>
<body>

<div class="d-flex min-vh-100">
    
    <div class="flex-grow-1 bg-light d-flex flex-column transition-content">
        
        <div class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h4 class="fw-bold text-dark mb-0">🔍 Inspector de Auditoría</h4>
                <small class="text-muted">Visualizando Registro de Bitácora #<?php echo $log['id']; ?></small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0 fs-5 no-print">
                    <input class="form-check-input" type="checkbox" id="btnDarkModeToggle" style="cursor: pointer;">
                    <label class="form-check-label text-muted fs-6" for="btnDarkModeToggle" id="lblDarkMode">
                        <i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro
                    </label>
                </div>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print">
                    <i class="bi bi-printer me-1"></i> Imprimir Reporte / PDF
                </button>
                <a href="index.php" class="btn btn-sm btn-outline-primary no-print"><i class="bi bi-arrow-left me-1"></i> Volver a la Bitácora</a>
            </div>
        </div>

        <div class="container py-5 flex-grow-1">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Metadatos del Evento</h5>
                            <span class="badge bg-secondary p-2">ID Interno: #<?php echo $log['id']; ?></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6 border-bottom pb-2">
                                    <span class="text-muted d-block small fw-bold text-uppercase">Origen del Módulo</span>
                                    <span class="fs-5 fw-semibold text-dark"><?php echo htmlspecialchars($log['modulo_origen']); ?></span>
                                </div>
                                <div class="col-md-6 border-bottom pb-2">
                                    <span class="text-muted d-block small fw-bold text-uppercase">Usuario Operador</span>
                                    <code class="fs-5 fw-bold text-primary"><?php echo htmlspecialchars($log['usuario']); ?></code>
                                </div>
                                <div class="col-md-6 border-bottom pb-2">
                                    <span class="text-muted d-block small fw-bold text-uppercase">Acción Ejecutada</span>
                                    <span class="badge bg-light text-dark border action-badge px-3 py-2 fs-6 mt-1"><?php echo htmlspecialchars($log['accion']); ?></span>
                                </div>
                                <div class="col-md-6 border-bottom pb-2">
                                    <span class="text-muted d-block small fw-bold text-uppercase">Fecha y Hora del Registro</span>
                                    <span class="text-secondary fw-semibold d-block mt-1"><i class="bi bi-calendar3 me-2"></i><?php echo $log['fecha_registro']; ?></span>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small fw-bold text-uppercase">Tabla / Entidad Afectada</span>
                                    <strong class="text-dark"><?php echo htmlspecialchars($log['tabla_afectada'] ?? 'N/A'); ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <span class="text-muted d-block small fw-bold text-uppercase">ID de Referencia del Registro</span>
                                    <span class="text-secondary">#<?php echo htmlspecialchars($log['registro_id'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-semibold text-dark"><i class="bi bi-code-slash text-danger me-2"></i>Valores Nuevos / Payload (JSONB)</h5>
                        </div>
                        <div class="card-body bg-dark-subtle rounded-bottom p-0">
                            <?php if ($jsonPretty): ?>
                                <pre class="m-0 p-4" style="border-radius: 0 0 8px 8px; max-height: 500px; overflow-y: auto;"><code class="text-dark"><?php echo htmlspecialchars($jsonPretty); ?></code></pre>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted fst-italic">
                                    <i class="bi bi-slash-circle fs-3 d-block mb-2"></i> Sin datos adicionales insertados en este evento.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <footer class="footer py-3 text-center text-muted bg-white border-top">
            <small>© 2026 - Proyecto Integrador de Contaduría y TI</small>
        </footer>
    </div>
</div>

<script>
    // LÓGICA DE CONTROL PARA EL MODO OSCURO (Sincronizado con index)
    const toggleDarkMode = document.getElementById('btnDarkModeToggle');
    const lblDarkMode = document.getElementById('lblDarkMode');
    
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        if(toggleDarkMode) toggleDarkMode.checked = true;
        if(lblDarkMode) lblDarkMode.innerHTML = '<i class="bi bi-sun-fill text-warning me-1"></i> Modo Claro';
    }

    if(toggleDarkMode) {
        toggleDarkMode.addEventListener('change', (e) => {
            if (e.target.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if(lblDarkMode) lblDarkMode.innerHTML = '<i class="bi bi-sun-fill text-warning me-1"></i> Modo Claro';
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                if(lblDarkMode) lblDarkMode.innerHTML = '<i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro';
            }
        });
    }
</script>
</body>
</html>