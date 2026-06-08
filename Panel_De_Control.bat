@echo off
:menu
cls
color 0B
title PANEL DE CONTROL - PROYECTO INTEGRADOR
echo ==========================================================
echo        SISTEMA INTEGRAL DE GESTION DOCUMENTAL
echo               PANEL DE ACCESO RAPIDO
echo ==========================================================
echo.
echo  [1] Abrir Modulo 1 (.NET Core - Gestion Central)
echo  [2] Abrir Modulo 2 (PHP - Consulta y Reportes)
echo  [3] Abrir Modulo 3 (Node.js - Buscador NoSQL)
echo  [4] Abrir TODO EL ECOSISTEMA (Los 3 modulos a la vez)
echo  [5] Salir
echo.
echo ==========================================================
set /p opcion="Selecciona una opcion (1-5) y presiona Enter: "

if "%opcion%"=="1" (
    echo Abriendo Modulo 1...
    :: Modifica el puerto 5000 si tu .NET corre en otro puerto
    start http://localhost:5000
    goto menu
)
if "%opcion%"=="2" (
    echo Abriendo Modulo 2...
    start http://localhost:8080
    goto menu
)
if "%opcion%"=="3" (
    echo Abriendo Modulo 3...
    start http://localhost:3000
    goto menu
)
if "%opcion%"=="4" (
    echo Levantando interfaces del ecosistema completo...
    start http://localhost:5000
    start http://localhost:8080
    start http://localhost:3000
    goto menu
)
if "%opcion%"=="5" (
    echo Cerrando panel...
    timeout /t 1 >nul
    exit
)

:: Si el usuario escribe cualquier otra cosa
echo Opcion no valida, intenta de nuevo.
timeout /t 2 >nul
goto menu