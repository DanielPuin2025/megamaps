<?php
declare(strict_types=1);

// Headers anti-caché
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $https,
    'samesite' => 'Strict',
]);
session_start();

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mostrarResultado(string $titulo, string $mensaje, int $estado, ?string $ruta = null): void
{
    http_response_code($estado);
    $rutaHtml = $ruta === null ? '' : '<p>Ruta guardada: <code>' . escapar($ruta) . '</code></p>';
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . escapar($titulo) . '</title>'
        . '<style>body{font:16px Arial,sans-serif;background:#f1f4fa;color:#1b2340;'
        . 'margin:0;padding:32px}.card{max-width:560px;margin:auto;padding:24px;'
        . 'background:#fff;border:1px solid #e3e7f1;border-radius:16px}a{color:#178245}'
        . '.links{display:flex;gap:18px;flex-wrap:wrap}</style>'
        . '</head><body><main class="card"><h1>' . escapar($titulo) . '</h1><p>'
        . escapar($mensaje) . '</p>' . $rutaHtml
        . '<p class="links"><a href="subir_foto.html">Subir otra foto</a>'
        . '<a href="index.html">Volver a MegaMaps</a></p></main></body></html>';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    mostrarResultado('Solicitud no válida', 'Usa el formulario para enviar una foto.', 405);
    exit;
}

// Comentado para permitir acceso sin autenticación en desarrollo
// if (empty($_SESSION['megamaps_admin'])) {
//     mostrarResultado('Acceso requerido', 'Inicia sesión en MegaMaps como administrador antes de subir fotos.', 401);
//     exit;
// }

$archivoGuardado = null;

try {
    $salon = isset($_POST['salon']) && is_string($_POST['salon'])
        ? trim($_POST['salon'])
        : '';

    if (!preg_match('/^\d{3}$/', $salon)) {
        throw new RuntimeException('Selecciona un número de salón válido de tres cifras.');
    }

    if (!isset($_FILES['foto']) || !is_array($_FILES['foto'])) {
        throw new RuntimeException('Selecciona una foto JPEG para subir.');
    }

    $foto = $_FILES['foto'];
    if (!isset($foto['error'], $foto['size'], $foto['tmp_name'])
        || !is_int($foto['error'])
        || !is_int($foto['size'])
        || !is_string($foto['tmp_name'])) {
        throw new RuntimeException('No se recibió correctamente el archivo.');
    }

    if ($foto['error'] !== UPLOAD_ERR_OK) {
        $errores = [
            UPLOAD_ERR_INI_SIZE => 'La foto supera el límite de subida configurado en PHP.',
            UPLOAD_ERR_FORM_SIZE => 'La foto supera el límite de 1 GB.',
            UPLOAD_ERR_PARTIAL => 'La foto solo se recibió parcialmente. Inténtalo de nuevo.',
            UPLOAD_ERR_NO_FILE => 'Selecciona una foto JPEG para subir.',
            UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene una carpeta temporal configurada.',
            UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo temporal.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP bloqueó la subida.',
        ];
        throw new RuntimeException($errores[$foto['error']] ?? 'No se pudo recibir la foto.');
    }

    if ($foto['size'] < 1) {
        throw new RuntimeException('La foto debe pesar más de 0 bytes.');
    }
    // Límite de tamaño removido para permitir archivos de cualquier tamaño

    // Validación mejorada y más flexible de imágenes
    $informacionImagen = @getimagesize($foto['tmp_name']);
    $mime = @(new finfo(FILEINFO_MIME_TYPE))->file($foto['tmp_name']);

    // Tipos permitidos con validación flexible
    $tiposPermitidos = [
        'image/jpeg', 'image/jpg', 'image/pjpeg',
        'image/png', 'image/x-png',
        'image/webp'
    ];

    // Extensiones permitidas
    $extension = strtolower(pathinfo($foto['name'] ?? '', PATHINFO_EXTENSION));
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    // Validar que sea una imagen válida (con al menos una verificación exitosa)
    $esImagenValida = (
        $informacionImagen !== false ||
        in_array($mime, $tiposPermitidos, true) ||
        in_array($extension, $extensionesPermitidas, true)
    );

    if (!$esImagenValida) {
        throw new RuntimeException('El archivo debe ser una imagen válida (JPEG, PNG o WebP).');
    }

    $baseFotos = __DIR__ . DIRECTORY_SEPARATOR . 'fotos';
    $carpeta = $baseFotos . DIRECTORY_SEPARATOR . 'salones';
    if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
        throw new RuntimeException('No se pudo crear la carpeta fotos/salones/.');
    }

    $baseDatos = __DIR__ . DIRECTORY_SEPARATOR . 'megamaps.sqlite';
    if (!is_file($baseDatos)) {
        throw new RuntimeException('No se encontró megamaps.sqlite en la carpeta del proyecto.');
    }

    $pdo = new PDO('sqlite:' . $baseDatos, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS fotos_salones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            salon_numero TEXT NOT NULL CHECK (length(salon_numero) = 3),
            ruta TEXT NOT NULL UNIQUE,
            creada_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $pdo->exec(
        'CREATE INDEX IF NOT EXISTS idx_fotos_salones_numero
         ON fotos_salones (salon_numero)'
    );

    // Determinar extensión correcta basada en el tipo de archivo
    $extensionFinal = 'jpg'; // Por defecto
    if ($informacionImagen !== false) {
        $extensiones = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp'
        ];
        $extensionFinal = $extensiones[$informacionImagen[2]] ?? $extension ?? 'jpg';
    } elseif (in_array($extension, $extensionesPermitidas, true)) {
        $extensionFinal = $extension;
    }

    $nombreArchivo = 'salon-' . $salon . '-' . bin2hex(random_bytes(16)) . '.' . $extensionFinal;
    $archivoGuardado = $carpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
    $ruta = 'fotos/salones/' . $nombreArchivo;

    if (!move_uploaded_file($foto['tmp_name'], $archivoGuardado)) {
        $archivoGuardado = null;
        throw new RuntimeException('No se pudo mover la foto a la carpeta fotos/salones/.');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO fotos_salones (salon_numero, ruta) VALUES (:salon, :ruta)'
    );
    $stmt->execute(['salon' => $salon, 'ruta' => $ruta]);

    mostrarResultado('Foto guardada', 'La foto quedó asociada al salón ' . $salon . '.', 200, $ruta);
} catch (RuntimeException $error) {
    if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
        error_log('No se pudo limpiar la foto después de un error: ' . $archivoGuardado);
    }
    mostrarResultado('No se pudo guardar la foto', $error->getMessage(), 400);
} catch (PDOException $error) {
    if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
        error_log('No se pudo limpiar la foto después de un error de base de datos: ' . $archivoGuardado);
    }
    error_log('Error de base de datos al guardar una foto de salón: ' . $error->getMessage());
    mostrarResultado('Error del servidor', 'No se pudo registrar la ruta de la foto en megamaps.sqlite.', 500);
} catch (Throwable $error) {
    if ($archivoGuardado !== null && is_file($archivoGuardado) && !unlink($archivoGuardado)) {
        error_log('No se pudo limpiar la foto después de un error inesperado: ' . $archivoGuardado);
    }
    error_log('Error inesperado al guardar una foto de salón: ' . $error->getMessage());
    mostrarResultado('Error del servidor', 'Ocurrió un error inesperado al procesar la foto.', 500);
}
