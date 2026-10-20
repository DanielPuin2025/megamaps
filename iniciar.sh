#!/bin/bash

echo "======================================"
echo "🎓 MegaMaps - Megacolegio El Progreso"
echo "======================================"
echo ""

# Verificar que PHP esté instalado
if ! command -v php &> /dev/null; then
    echo "❌ Error: PHP no está instalado"
    echo "   Instala PHP desde https://www.php.net/downloads"
    exit 1
fi

echo "✅ PHP detectado: $(php -v | head -n 1)"
echo ""

# Verificar SQLite
if ! php -m | grep -q sqlite3; then
    echo "⚠️  Advertencia: SQLite3 no está habilitado en PHP"
    echo "   El sistema podría no funcionar correctamente"
    echo ""
fi

# Crear carpetas necesarias
echo "📁 Creando carpetas necesarias..."
mkdir -p fotos/salones
chmod 777 fotos/salones 2>/dev/null || true
echo "   ✓ fotos/salones"

# Verificar base de datos
if [ ! -f "megamaps.sqlite" ]; then
    echo ""
    echo "⚠️  Base de datos no encontrada. Ejecuta:"
    echo "   python crear_base_datos.py"
    echo ""
fi

# Determinar puerto
PORT=8000
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; then
    PORT=8001
    echo "⚠️  Puerto 8000 ocupado, usando puerto $PORT"
fi

echo ""
echo "======================================"
echo "🚀 Iniciando servidor en puerto $PORT"
echo "======================================"
echo ""
echo "📱 Accede a la aplicación:"
echo "   • Principal: http://localhost:$PORT/index.html"
echo "   • Administrador: http://localhost:$PORT/admin.html"
echo ""
echo "Presiona Ctrl+C para detener el servidor"
echo ""

# Iniciar servidor
php -S localhost:$PORT
