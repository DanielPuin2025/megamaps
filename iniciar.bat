@echo off
title MegaMaps - Megacolegio El Progreso

echo ======================================
echo   MegaMaps - Megacolegio El Progreso
echo ======================================
echo.

REM Verificar PHP
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP no esta instalado
    echo         Instala PHP desde https://www.php.net/downloads
    pause
    exit /b 1
)

echo [OK] PHP detectado
php -v | findstr /C:"PHP"
echo.

REM Crear carpetas
echo [INFO] Creando carpetas necesarias...
if not exist "fotos\salones" mkdir "fotos\salones"
echo       - fotos\salones creada
echo.

REM Verificar base de datos
if not exist "megamaps.sqlite" (
    echo [ADVERTENCIA] Base de datos no encontrada
    echo              Ejecuta: python crear_base_datos.py
    echo.
)

REM Determinar puerto
set PORT=8000
netstat -ano | findstr ":8000" >nul
if %ERRORLEVEL% EQU 0 (
    set PORT=8001
    echo [INFO] Puerto 8000 ocupado, usando puerto 8001
)

echo ======================================
echo   Iniciando servidor en puerto %PORT%
echo ======================================
echo.
echo [WEB] Accede a la aplicacion:
echo       - Principal: http://localhost:%PORT%/index.html
echo       - Administrador: http://localhost:%PORT%/admin.html
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

REM Iniciar servidor
php -S localhost:%PORT%
