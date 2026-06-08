@echo off
TITLE Ecosistema Docker - Proyecto Integrador Contaduria
echo =========================================================
echo       INICIANDO ARQUITECTURA DE 6 CONTENEDORES
echo =========================================================
echo.

:: Viajar de manera relativa entrando primero a ProyectoIntegrador y luego a docker
cd "%~dp0ProyectoIntegrador\docker"

echo [PASO 1] Descargando imagenes y compilando Dockerfiles...
docker-compose up -d --build
echo.

echo [PASO 2] Validando estado actual de los servicios...
docker-compose ps
echo.

echo =========================================================
echo Ecosistema iniciado de manera correcta.
echo  - Portal .NET Core (Mod 1): http://localhost:5000
echo  - Portal PHP 8     (Mod 2): http://localhost:8080
echo  - Portal Node.js   (Mod 3): http://localhost:3000
echo =========================================================
echo.
pause