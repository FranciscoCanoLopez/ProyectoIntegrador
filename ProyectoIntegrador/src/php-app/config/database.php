<?php
// config/database.php

define('DB_HOST', getenv('DB_HOST') ?: 'postgres-db');
define('DB_PORT', getenv('DB_PORT') ?: '5433');
define('DB_NAME', getenv('DB_NAME') ?: 'sistema_gestion');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'password123');

try {
    // Forzamos manualmente el puerto 5432 que es el que usa la red interna de Docker
    $dsn = "pgsql:host=" . DB_HOST . ";port=5432;dbname=" . DB_NAME;
    
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la infraestructura de auditoría: " . $e->getMessage());
}