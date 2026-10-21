#!/bin/bash
cd "$(dirname "$0")"

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

# Verificar extensiones requeridas por el backend PHP.
if ! php -m | grep -qi pdo_sqlite; then
    echo "❌ Error: La extensión pdo_sqlite no está habilitada en PHP"
    exit 1
fi

if ! php -m | grep -qi fileinfo; then
    echo "❌ Error: La extensión fileinfo no está habilitada en PHP"
    exit 1
fi

if [ ! -f "base de datos/megamaps.sqlite" ]; then
    echo "❌ Error: No se encontró base de datos/megamaps.sqlite"
    echo "   No se ha modificado ni recreado ninguna base de datos."
    exit 1
fi
if [ ! -f "base de datos/fotos_rutas_personalidas.sqlite" ]; then
    echo "❌ Error: No se encontró base de datos/fotos_rutas_personalidas.sqlite"
    echo "   No se ha modificado ni recreado ninguna base de datos."
    exit 1
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
echo "   • Principal: http://localhost:$PORT/frontend/index.html"
echo "   • Inicio rápido: http://localhost:$PORT/"
echo ""
echo "Presiona Ctrl+C para detener el servidor"
echo ""

# Iniciar servidor
php -S 127.0.0.1:$PORT -t .
