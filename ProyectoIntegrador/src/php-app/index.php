<?php
// src/php-app/index.php

require_once __DIR__ . '/config/database.php';

try {
    $sql = "SELECT id, modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_nuevos, direccion_ip, fecha_registro 
            FROM audit_logs 
            ORDER BY fecha_registro DESC";
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();

    // Procesar datos para las gráficas (Conteo por módulo)
    $modulosConteo = ['DotNet' => 0, 'PHP' => 0, 'NodeJS' => 0];
    // Conteo por tipo de acción
    $accionesConteo = [];

    foreach ($logs as $log) {
        if (strpos($log['modulo_origen'], 'DotNet') !== false) $modulosConteo['DotNet']++;
        elseif (strpos($log['modulo_origen'], 'PHP') !== false) $modulosConteo['PHP']++;
        elseif (strpos($log['modulo_origen'], 'NodeJS') !== false) $modulosConteo['NodeJS']++;

        $act = $log['accion'];
        $accionesConteo[$act] = ($accionesConteo[$act] ?? 0) + 1;
    }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="public/css/estilos.css" rel="stylesheet">
</head>
<body>

<div class="d-flex min-vh-100">
    
    <div class="sidebar bg-dark text-white p-3 d-flex flex-column" id="sidebar">
        <div class="d-flex align-items-center justify-content-between mb-4 mt-2 px-2">
            <h5 class="fw-bold mb-0 text-truncate label-sidebar"><i class="bi bi-shield-check text-success me-2"></i>Auditoría</h5>
            <button class="btn btn-sm btn-outline-light border-0" id="btnToggleSidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
        </div>
        
        <hr class="text-secondary">
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="#" class="nav-link active text-white py-2 px-3 menu-link" data-target="vista-bitacora">
                    <i class="bi bi-table me-3 fs-5"></i> <span class="label-sidebar">Bitácora Global</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link text-white py-2 px-3 menu-link" data-target="vista-graficas">
                    <i class="bi bi-pie-chart me-3 fs-5"></i> <span class="label-sidebar">Estadísticas y Gráficas</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link text-white py-2 px-3 menu-link" data-target="vista-linea-tiempo">
                    <i class="bi bi-clock-history me-3 fs-5"></i> <span class="label-sidebar">Línea de Tiempo</span>
                </a>
            </li>
        </ul>
        
        <hr class="text-secondary">
        <div class="px-2 text-muted label-sidebar text-center">
            <small>v1.2.0 - Docker Environment</small>
        </div>
    </div>

    <div class="flex-grow-1 bg-light d-flex flex-column transition-content">
        
        <div class="bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <h4 class="fw-bold text-dark mb-0">🔒 Control de Auditoría</h4>
                <small class="text-muted">Infraestructura del Módulo 2</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="form-check form-switch mb-0 fs-5">
                    <input class="form-check-input" type="checkbox" id="btnDarkModeToggle" style="cursor: pointer;">
                    <label class="form-check-label text-muted fs-6" for="btnDarkModeToggle" id="lblDarkMode">
                        <i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro
                    </label>
                </div>
                <span class="badge bg-success p-2"><i class="bi bi-hdd-network me-1"></i> PostgreSQL (Puerto 5432)</span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            
            <div id="vista-bitacora" class="dashboard-view">
                <div class="card shadow-sm border-0">
                    <div class="card-header card-header-custom py-3">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-list-task me-2"></i>Registros de Auditoría Recientes (Datos Semilla)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead>
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
                                        <tr><td colspan="7" class="text-center py-4 text-muted">No hay registros.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): 
                                            $badgeClass = 'bg-secondary';
                                            if (strpos($log['modulo_origen'], 'DotNet') !== false) $badgeClass = 'badge-dotnet';
                                            if (strpos($log['modulo_origen'], 'PHP') !== false) $badgeClass = 'badge-php';
                                            if (strpos($log['modulo_origen'], 'NodeJS') !== false) $badgeClass = 'badge-node';
                                        ?>
                                            <tr>
                                                <td><strong>#<?php echo $log['id']; ?></strong></td>
                                                <td><span class="badge <?php echo $badgeClass; ?> d-block p-2"><?php echo htmlspecialchars($log['modulo_origen']); ?></span></td>
                                                <td><code class="text-dark fw-bold"><?php echo htmlspecialchars($log['usuario']); ?></code></td>
                                                <td><span class="badge bg-light text-dark border action-badge"><?php echo htmlspecialchars($log['accion']); ?></span></td>
                                                <td>
                                                    <small class="d-block"><strong>Tabla:</strong> <?php echo htmlspecialchars($log['tabla_afectada'] ?? 'N/A'); ?></small>
                                                    <small class="text-muted"><strong>ID Ref:</strong> <?php echo htmlspecialchars($log['registro_id'] ?? 'N/A'); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($log['valores_nuevos']): ?>
                                                        <?php 
                                                            $jsonDecoded = json_decode($log['valores_nuevos'], true);
                                                            $jsonPretty = json_encode($jsonDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                                        ?>
                                                        <pre><code><?php echo htmlspecialchars($jsonPretty); ?></code></pre>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Sin datos adicionales</span>
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

            <div id="vista-graficas" class="dashboard-view d-none">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="mb-0 fw-semibold text-dark"><i class="bi bi-pie-chart-fill text-primary me-2"></i>Actividad por Módulo / Microservicio</h5>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center p-4">
                                <div style="width: 80%; max-height: 320px;">
                                    <canvas id="chartPastel"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h5 class="mb-0 fw-semibold text-dark"><i class="bi bi-bar-chart-steps text-success me-2"></i>Frecuencia de Tipos de Acciones</h5>
                            </div>
                            <div class="card-body p-4">
                                <canvas id="chartBarras"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="vista-linea-tiempo" class="dashboard-view d-none">
                <div class="card shadow-sm border-0 p-4">
                    <h5 class="fw-semibold text-dark mb-4 border-bottom pb-2"><i class="bi bi-clock-history me-2 text-warning"></i>Historial de Eventos Secuenciales</h5>
                    <div class="timeline mt-2">
                        <?php foreach ($logs as $log): 
                            $timelineIcon = 'bi-activity';
                            $timelineColor = 'bg-secondary';
                            if($log['accion'] === 'LOGIN') { $timelineIcon = 'bi-box-arrow-in-right'; $timelineColor = 'bg-primary'; }
                            elseif($log['accion'] === 'CREATE_DOCUMENT') { $timelineIcon = 'bi-file-earmark-plus'; $timelineColor = 'bg-success'; }
                            elseif($log['accion'] === 'INITIALIZATION') { $timelineIcon = 'bi-gear-fill'; $timelineColor = 'bg-info'; }
                        ?>
                            <div class="timeline-item mb-4 position-relative ps-4 border-start border-2 border-secondary-subtle">
                                <div class="timeline-badge <?php echo $timelineColor; ?> text-white d-flex align-items-center justify-content-center position-absolute rounded-circle" style="left: -13px; top: 0; width: 26px; height: 26px;">
                                    <i class="bi <?php echo $timelineIcon; ?> style-icon-timeline" style="font-size: 0.85rem;"></i>
                                </div>
                                <div class="timeline-content bg-light p-3 rounded shadow-sm border">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($log['modulo_origen']); ?> ➔ <span class="text-primary"><?php echo htmlspecialchars($log['accion']); ?></span></span>
                                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?php echo $log['fecha_registro']; ?></small>
                                    </div>
                                    <p class="mb-1 mt-2 text-secondary">El usuario <code><?php echo htmlspecialchars($log['usuario']); ?></code> afectó el elemento <strong><?php echo htmlspecialchars($log['tabla_afectada'] ?? 'N/A'); ?></strong> (Ref ID: <?php echo htmlspecialchars($log['registro_id'] ?? 'N/A'); ?>) desde la IP <?php echo htmlspecialchars($log['direccion_ip']); ?>.</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
    // 1. Alternador de despliegue del Sidebar (Toggle)
    
    const sidebar = document.getElementById('sidebar');
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    
    btnToggleSidebar.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });

    // 2. Control de navegación entre vistas (SPA simulation)

    const menuLinks = document.querySelectorAll('.menu-link');
    const views = document.querySelectorAll('.dashboard-view');

    menuLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Cambiar clase activa en menú lateral
            menuLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            // Alternar paneles de visualización
            const target = link.getAttribute('data-target');
            views.forEach(view => {
                if(view.id === target) {
                    view.classList.remove('d-none');
                } else {
                    view.classList.add('d-none');
                }
            });
        });
    });

// ==========================================================================
    // 3. Renderizado de Gráficas de Chart.js con los datos parseados de PHP
    // ==========================================================================
    
    // Declaramos las variables globales para que la función de modo oscuro pueda acceder a ellas
    let chartPastel;
    let chartBarras;

    // Gráfica de Pastel (Módulos)
    const ctxPastel = document.getElementById('chartPastel').getContext('2d');
    chartPastel = new Chart(ctxPastel, {
        type: 'pie',
        data: {
            labels: ['.NET (Módulo 1)', 'PHP (Módulo 2)', 'NodeJS (Módulo 3)'],
            datasets: [{
                data: [<?php echo $modulosConteo['DotNet']; ?>, <?php echo $modulosConteo['PHP']; ?>, <?php echo $modulosConteo['NodeJS']; ?>],
                backgroundColor: ['#512bd4', '#777bb4', '#339933'],
                borderWidth: 2
            }]
        },
        options: { 
            responsive: true, 
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: {
                        // Inicializa con el color correcto si entran directo en modo oscuro
                        color: localStorage.getItem('theme') === 'dark' ? '#f8fafc' : '#666666'
                    }
                } 
            } 
        }
    });

    // Gráfica de Barras (Acciones)
    const ctxBarras = document.getElementById('chartBarras').getContext('2d');
    chartBarras = new Chart(ctxBarras, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($accionesConteo)); ?>,
            datasets: [{
                label: 'Cantidad de Eventos',
                data: <?php echo json_encode(array_values($accionesConteo)); ?>,
                backgroundColor: ['#1e3a8a', '#0d9488', '#b45309', '#be123c'],
                borderRadius: 6
            }]
        },
        options: { 
            responsive: true, 
            scales: { 
                y: { 
                    beginAtZero: true, 
                    ticks: { 
                        stepSize: 1,
                        color: localStorage.getItem('theme') === 'dark' ? '#f8fafc' : '#666666'
                    } 
                },
                x: {
                    ticks: {
                        color: localStorage.getItem('theme') === 'dark' ? '#f8fafc' : '#666666'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: localStorage.getItem('theme') === 'dark' ? '#f8fafc' : '#666666'
                    }
                }
            }
        }
    });

    // ==========================================================================
    // ESCUCHADOR Y LÓGICA DE CONTROL PARA EL MODO OSCURO (AGREGAR ESTO)
    // ==========================================================================
    const toggleDarkMode = document.getElementById('btnDarkModeToggle');
    const lblDarkMode = document.getElementById('lblDarkMode');
    
    // 1. Validar el estado guardado previamente en el navegador al cargar la página
    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        if(toggleDarkMode) toggleDarkMode.checked = true;
        if(lblDarkMode) lblDarkMode.innerHTML = '<i class="bi bi-sun-fill text-warning me-1"></i> Modo Claro';
    }

    // 2. Escuchar activamente los clics del usuario en el interruptor (Switch)
    if(toggleDarkMode) {
        toggleDarkMode.addEventListener('change', (e) => {
            if (e.target.checked) {
                // Activar Modo Oscuro
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if(lblDarkMode) lblDarkMode.innerHTML = '<i class="bi bi-sun-fill text-warning me-1"></i> Modo Claro';
                actualizarGraficasParaModoOscuro(true);
            } else {
                // Activar Modo Claro
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                if(lblDarkMode) lblDarkMode.innerHTML = '<i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro';
                actualizarGraficasParaModoOscuro(false);
            }
        });
    }

    // 3. Función encargada de actualizar los textos de Chart.js en tiempo real
    function actualizarGraficasParaModoOscuro(isDark) {
        const colorTexto = isDark ? '#f8fafc' : '#666666';
        
        // Modificar las opciones de color de las instancias globales declaradas arriba
        [chartPastel, chartBarras].forEach(chartInstance => {
            if(chartInstance && chartInstance.options.plugins.legend) {
                chartInstance.options.plugins.legend.labels.color = colorTexto;
                
                // Si la gráfica tiene ejes (como la de barras), actualiza los ticks
                if(chartInstance.options.scales) {
                    if(chartInstance.options.scales.y && chartInstance.options.scales.y.ticks) {
                        chartInstance.options.scales.y.ticks.color = colorTexto;
                    }
                    if(chartInstance.options.scales.x && chartInstance.options.scales.x.ticks) {
                        chartInstance.options.scales.x.ticks.color = colorTexto;
                    }
                }
                // Redibuja la gráfica con los nuevos estilos de color
                chartInstance.update();
            }
        });
    }

</script>
</body>
</html>