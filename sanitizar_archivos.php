<?php
// sanitizar_archivos.php - Script para limpiar nombres de archivos

$baseDir = __DIR__ . '/assets/fotos/galeria';
$archivos = glob($baseDir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

$renombrados = [];
$errores = [];

foreach ($archivos as $rutaCompleta) {
    $nombreOriginal = basename($rutaCompleta);

    // Sanitizar nombre
    $nombreLimpio = sanitizarNombre($nombreOriginal);

    // Si el nombre cambió, renombrar
    if ($nombreOriginal !== $nombreLimpio) {
        $rutaNueva = dirname($rutaCompleta) . '/' . $nombreLimpio;

        // Verificar si ya existe
        if (file_exists($rutaNueva)) {
            $errores[] = "❌ Ya existe: $nombreLimpio (original: $nombreOriginal)";
            continue;
        }

        // Renombrar
        if (rename($rutaCompleta, $rutaNueva)) {
            $renombrados[] = [
                'original' => $nombreOriginal,
                'nuevo' => $nombreLimpio
            ];
            echo "✅ Renombrado: $nombreOriginal → $nombreLimpio\n";
        } else {
            $errores[] = "❌ Error al renombrar: $nombreOriginal";
        }
    }
}

echo "\n📊 RESUMEN:\n";
echo "✅ Archivos renombrados: " . count($renombrados) . "\n";
echo "❌ Errores: " . count($errores) . "\n";

if (!empty($errores)) {
    echo "\n⚠️ ERRORES:\n";
    foreach ($errores as $error) {
        echo $error . "\n";
    }
}

// Guardar mapeo para actualizar referencias
if (!empty($renombrados)) {
    file_put_contents('mapeo_renombrado.json', json_encode($renombrados, JSON_PRETTY_PRINT));
    echo "\n💾 Mapeo guardado en: mapeo_renombrado.json\n";
}

function sanitizarNombre($nombre) {
    // Separar nombre y extensión
    $info = pathinfo($nombre);
    $ext = $info['extension'] ?? 'jpg';
    $nombreSinExt = $info['filename'];

    // Normalizar unicode (quitar acentos)
    $nombreSinExt = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombreSinExt);

    // Convertir a minúsculas
    $nombreSinExt = strtolower($nombreSinExt);

    // Reemplazar espacios y caracteres especiales con guiones
    $nombreSinExt = preg_replace('/[^a-z0-9]+/', '-', $nombreSinExt);

    // Eliminar guiones al inicio y final
    $nombreSinExt = trim($nombreSinExt, '-');

    // Colapsar múltiples guiones
    $nombreSinExt = preg_replace('/-+/', '-', $nombreSinExt);

    // Si quedó vacío, usar 'imagen'
    $nombreSinExt = $nombreSinExt ?: 'imagen';

    return $nombreSinExt . '.' . strtolower($ext);
}
