<?php
// API para obtener todas las fotos de salones
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function responder(int $estado, array $datos): void
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

try {
    $baseDatos = __DIR__ . DIRECTORY_SEPARATOR . 'megamaps.sqlite';

    if (!is_file($baseDatos)) {
        responder(404, ['error' => 'Base de datos no encontrada']);
    }

    $pdo = new PDO('sqlite:' . $baseDatos, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Crear tabla si no existe
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS fotos_salones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            salon_numero TEXT NOT NULL CHECK (length(salon_numero) = 3),
            ruta TEXT NOT NULL UNIQUE,
            creada_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $accion = $_GET['accion'] ?? 'listar';

    switch ($accion) {
        case 'listar':
            // Listar todas las fotos
            $stmt = $pdo->query(
                'SELECT id, salon_numero, ruta, creada_en
                 FROM fotos_salones
                 ORDER BY salon_numero, creada_en DESC'
            );
            $fotos = $stmt->fetchAll();

            // Agrupar por salón
            $porSalon = [];
            foreach ($fotos as $foto) {
                $salon = $foto['salon_numero'];
                if (!isset($porSalon[$salon])) {
                    $porSalon[$salon] = [];
                }
                $porSalon[$salon][] = $foto;
            }

            responder(200, [
                'total' => count($fotos),
                'fotos' => $fotos,
                'por_salon' => $porSalon,
            ]);
            break;

        case 'salon':
            // Obtener fotos de un salón específico
            $salon = $_GET['numero'] ?? '';
            if (!preg_match('/^\d{3}$/', $salon)) {
                responder(400, ['error' => 'Número de salón inválido']);
            }

            $stmt = $pdo->prepare(
                'SELECT id, salon_numero, ruta, creada_en
                 FROM fotos_salones
                 WHERE salon_numero = :salon
                 ORDER BY creada_en DESC'
            );
            $stmt->execute(['salon' => $salon]);
            $fotos = $stmt->fetchAll();

            responder(200, [
                'salon' => $salon,
                'total' => count($fotos),
                'fotos' => $fotos,
            ]);
            break;

        case 'estadisticas':
            // Obtener estadísticas
            $stmt = $pdo->query(
                'SELECT
                    COUNT(*) as total_fotos,
                    COUNT(DISTINCT salon_numero) as total_salones
                 FROM fotos_salones'
            );
            $stats = $stmt->fetch();

            $stmt = $pdo->query(
                'SELECT salon_numero, COUNT(*) as cantidad
                 FROM fotos_salones
                 GROUP BY salon_numero
                 ORDER BY cantidad DESC'
            );
            $porSalon = $stmt->fetchAll();

            responder(200, [
                'total_fotos' => $stats['total_fotos'],
                'total_salones' => $stats['total_salones'],
                'por_salon' => $porSalon,
            ]);
            break;

        default:
            responder(400, ['error' => 'Acción no válida']);
    }

} catch (PDOException $error) {
    error_log('Error en API de fotos: ' . $error->getMessage());
    responder(500, ['error' => 'Error de base de datos']);
} catch (Throwable $error) {
    error_log('Error inesperado en API de fotos: ' . $error->getMessage());
    responder(500, ['error' => 'Error del servidor']);
}
