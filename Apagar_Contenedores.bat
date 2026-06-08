@echo off
TITLE Detener Ecosistema Docker - Proyecto Integrador Contaduria
echo =========================================================
echo       DETENIENDO ARQUITECTURA DE 6 CONTENEDORES
echo =========================================================
echo.

:: Viajar de manera relativa entrando primero a ProyectoIntegrador y luego a docker
cd "%~dp0ProyectoIntegrador\docker"

echo [PASO 1] Deteniendo contenedores y liberando memoria RAM...
docker-compose down
echo.

echo =========================================================
echo   Ecosistema detenido correctamente y recursos liberados.
echo =========================================================
echo.
pause