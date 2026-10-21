@echo off
title MegaMaps - Megacolegio El Progreso
cd /d "%~dp0"

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

set "PHP_PATH="
for /f "delims=" %%i in ('where php') do if not defined PHP_PATH set "PHP_PATH=%%i"
for %%i in ("%PHP_PATH%") do set "PHP_DIR=%%~dpi"

echo [OK] PHP detectado
php -v | findstr /C:"PHP"
echo.

REM Verificar que PHP tenga SQLite y soporte para validar imagenes.
php -c php.ini -d "extension_dir=%PHP_DIR%ext" -d extension=pdo_sqlite -d extension=fileinfo -m | findstr /I /C:"pdo_sqlite" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] La extension pdo_sqlite no esta habilitada en PHP
    pause
    exit /b 1
)

php -c php.ini -d "extension_dir=%PHP_DIR%ext" -d extension=pdo_sqlite -d extension=fileinfo -m | findstr /I /C:"fileinfo" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] La extension fileinfo no esta habilitada en PHP
    pause
    exit /b 1
)

if not exist "base de datos\megamaps.sqlite" (
    echo [ERROR] No se encontro base de datos\megamaps.sqlite
    echo         No se ha modificado ni recreado ninguna base de datos.
    echo.
    pause
    exit /b 1
)

if not exist "base de datos\fotos_rutas_personalidas.sqlite" (
    echo [ERROR] No se encontro base de datos\fotos_rutas_personalidas.sqlite
    echo         No se ha modificado ni recreado ninguna base de datos.
    echo.
    pause
    exit /b 1
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
echo       - Principal: http://localhost:%PORT%/frontend/index.html
echo       - Inicio rapido: http://localhost:%PORT%/
echo.
echo Presiona Ctrl+C para detener el servidor
echo.

REM Iniciar servidor
php -c php.ini -d "extension_dir=%PHP_DIR%ext" -d extension=pdo_sqlite -d extension=fileinfo -S 127.0.0.1:%PORT% -t .
