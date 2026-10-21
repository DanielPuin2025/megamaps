<?php
// API PHP para consultar datos y sincronizar rutas personalizadas de MegaMaps.
header('Content-Type: application/json; charset=utf-8');
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

function responder(int $estado, array $datos): void
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function identificadorValido($valor): bool
{
    return is_string($valor) && preg_match('/^[a-zA-Z0-9_-]{1,80}$/', $valor) === 1;
}

function rutaValida($ruta): bool
{
    if (!is_array($ruta)
        || !isset($ruta['camino'])
        || !is_array($ruta['camino'])
        || count($ruta['camino']) < 2
        || count($ruta['camino']) > 80) {
        return false;
    }

    foreach ($ruta['camino'] as $nodo) {
        if (!identificadorValido($nodo)) {
            return false;
        }
    }

    if (!array_key_exists('etiquetas', $ruta) || $ruta['etiquetas'] === null) {
        return true;
    }

    if (!is_array($ruta['etiquetas'])
        || count($ruta['etiquetas']) !== count($ruta['camino'])) {
        return false;
    }

    foreach ($ruta['etiquetas'] as $etiqueta) {
        if (!is_string($etiqueta) || trim($etiqueta) === '' || strlen($etiqueta) > 500) {
            return false;
        }
    }

    return true;
}

function validarSalones($salones): ?array
{
    if (!is_array($salones) || count($salones) > 200) {
        return null;
    }

    $limpios = [];
    foreach ($salones as $numero => $salon) {
        $numero = (string) $numero;
        if (preg_match('/^\d{1,4}$/', $numero) !== 1
            || !is_array($salon)
            || !isset($salon['nombre'], $salon['nodeId'], $salon['piso'])
            || !is_string($salon['nombre'])
            || trim($salon['nombre']) === ''
            || strlen($salon['nombre']) > 100
            || !identificadorValido($salon['nodeId'])
            || !is_int($salon['piso'])
            || $salon['piso'] < 1
            || $salon['piso'] > 20) {
            return null;
        }

        $registro = [
            'nombre' => trim($salon['nombre']),
            'nodeId' => $salon['nodeId'],
            'piso' => $salon['piso'],
        ];

        foreach (['manana', 'tarde'] as $jornada) {
            $docente = $salon[$jornada] ?? null;
            if ($docente === null) {
                $registro[$jornada] = null;
                continue;
            }

            if (!is_array($docente)
                || !isset($docente['nombre'])
                || !is_string($docente['nombre'])
                || trim($docente['nombre']) === ''
                || strlen($docente['nombre']) > 120) {
                return null;
            }

            foreach (['grado' => 40, 'area' => 160] as $campo => $maximo) {
                if (isset($docente[$campo])
                    && (!is_string($docente[$campo]) || strlen($docente[$campo]) > $maximo)) {
                    return null;
                }
            }

            $registro[$jornada] = [
                'nombre' => trim($docente['nombre']),
                'grado' => $docente['grado'] ?? null,
                'area' => $docente['area'] ?? null,
            ];
        }

        $limpios[$numero] = $registro;
    }

    return $limpios;
}

try {
    $pdo = new PDO('sqlite:' . __DIR__ . DIRECTORY_SEPARATOR . 'megamaps.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS rutas_personalizadas (
            clave TEXT PRIMARY KEY,
            camino_json TEXT NOT NULL,
            etiquetas_json TEXT,
            actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS salones_aplicacion (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            datos_json TEXT NOT NULL,
            actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $lugarFotos = isset($_GET['lugar']) && is_string($_GET['lugar']) ? $_GET['lugar'] : '';
    $tablasFotos = [
        'entrada' => 'fotos_entrada',
        'cafeteria' => 'fotos_cafeteria',
        'transicion' => 'fotos_transicion',
        'coordinacion' => 'fotos_coordinacion',
        'biblioteca' => 'fotos_biblioteca',
    ];
    if ($lugarFotos !== '' && isset($tablasFotos[$lugarFotos])) {
        $pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=fotos_rutas_personalidas;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        foreach ($tablasFotos as $tablaInicial) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS {$tablaInicial} (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                salon_numero CHAR(3) NOT NULL,
                clave VARCHAR(255) NULL,
                paso_node_id VARCHAR(80) NULL,
                ruta TEXT NULL,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_{$tablaInicial}_clave_paso (clave, paso_node_id),
                KEY idx_{$tablaInicial}_salon (salon_numero)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $stmtInicial = $pdo->prepare("INSERT INTO {$tablaInicial} (salon_numero) VALUES (:salon)");
            foreach (array_merge(range(101, 118), range(201, 215)) as $numeroSalon) {
                $stmtInicial->execute(['salon' => (string) $numeroSalon]);
            }
            $pdo->exec("CREATE INDEX IF NOT EXISTS idx_{$tablaInicial}_salon ON {$tablaInicial} (salon_numero)");
        }
        $tablaFoto = $tablasFotos[$lugarFotos];
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $pdo->query("SELECT clave, ruta FROM {$tablaFoto} WHERE clave IS NOT NULL ORDER BY salon_numero, clave");
            responder(200, ['fotos' => $stmt->fetchAll(PDO::FETCH_KEY_PAIR)]);
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: GET, POST');
            responder(405, ['error' => 'Método no permitido.']);
        }
        // Comentado para permitir acceso en desarrollo
        // if (empty($_SESSION['megamaps_admin'])) {
        //     responder(401, ['error' => 'Inicia sesión como administrador para guardar fotos.']);
        // }
        $claveFoto = isset($_POST['clave']) && is_string($_POST['clave']) ? $_POST['clave'] : '';
        $pasoFoto = isset($_POST['paso']) && is_string($_POST['paso']) ? $_POST['paso'] : '';
        if (!preg_match('/^' . preg_quote($lugarFotos, '/') . '__salon_(\d{3})__salon_\d{3}$/', $claveFoto, $coincidencia)
            || !identificadorValido($pasoFoto)) {
            responder(400, ['error' => 'El sal�n o el paso no tienen un formato v�lido.']);
        }
        if (!isset($_FILES['foto']) || !is_array($_FILES['foto']) || !isset($_FILES['foto']['error'], $_FILES['foto']['size'], $_FILES['foto']['tmp_name']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            responder(400, ['error' => 'No se pudo recibir la foto; verifica que el servidor permita cargas de hasta 1 GB.']);
        }
        $archivoFoto = $_FILES['foto'];
        if ($archivoFoto['size'] < 1) {
            responder(400, ['error' => 'La foto debe pesar más de 0 bytes.']);
        }

        // Validación SUPER FLEXIBLE - acepta cualquier imagen
        $mime = @(new finfo(FILEINFO_MIME_TYPE))->file($archivoFoto['tmp_name']);
        $extension = strtolower(pathinfo($archivoFoto['name'] ?? '', PATHINFO_EXTENSION));

        // Lista amplia de tipos MIME de imagen
        $esImagen = (
            // Por MIME type
            strpos($mime, 'image/') === 0 ||
            // Por extensión
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'tif'], true) ||
            // Si getimagesize funciona, es una imagen
            @getimagesize($archivoFoto['tmp_name']) !== false
        );

        if (!$esImagen) {
            responder(400, ['error' => 'El archivo debe ser una imagen.']);
        }

        // Determinar extensión para guardar
        $extensiones = [
            'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/pjpeg' => 'jpg',
            'image/png' => 'png', 'image/x-png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp', 'image/x-ms-bmp' => 'bmp'
        ];
        $extFinal = $extensiones[$mime] ?? $extension ?? 'jpg';
        $directorioFotos = __DIR__ . DIRECTORY_SEPARATOR . 'fotos_rutas';
        if (!is_dir($directorioFotos) && !mkdir($directorioFotos, 0755, true) && !is_dir($directorioFotos)) {
            throw new RuntimeException('No se pudo crear fotos_rutas.');
        }
        $nombreFoto = $lugarFotos . '-' . $coincidencia[1] . '-' . hash('sha256', $claveFoto . '|' . $pasoFoto) . '.' . $extFinal;
        $rutaRelativa = 'fotos_rutas/' . $nombreFoto;
        if (!move_uploaded_file($archivoFoto['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $rutaRelativa)) {
            throw new RuntimeException('No se pudo guardar la imagen.');
        }
        $stmt = $pdo->prepare("INSERT INTO {$tablaFoto} (salon_numero, clave, paso_node_id, ruta, actualizado_en)
            VALUES (:salon, :clave, :paso, :ruta, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE ruta = VALUES(ruta), actualizado_en = CURRENT_TIMESTAMP");
        $stmt->execute(['salon' => $coincidencia[1], 'clave' => $claveFoto, 'paso' => $pasoFoto, 'ruta' => $rutaRelativa]);
        responder(200, ['ok' => true, 'ruta' => $rutaRelativa]);
    }

    $accion = isset($_GET['action']) && is_string($_GET['action'])
        ? $_GET['action']
        : '';

    if ($accion === 'admin-login') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            responder(405, ['error' => 'Método no permitido.']);
        }

        $entrada = json_decode(file_get_contents('php://input'), true);
        if (!is_array($entrada)
            || !isset($entrada['pin'])
            || !is_string($entrada['pin'])
            || !hash_equals('1234', $entrada['pin'])) {
            responder(401, ['error' => 'PIN de administrador incorrecto.']);
        }

        session_regenerate_id(true);
        $_SESSION['megamaps_admin'] = true;
        responder(200, ['ok' => true]);
    }

    if ($accion === 'admin-logout') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: POST');
            responder(405, ['error' => 'Método no permitido.']);
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => 'Strict',
            ]);
        }
        session_destroy();
        responder(200, ['ok' => true]);
    }

    if ($accion === 'check-session') {
        responder(200, ['authenticated' => !empty($_SESSION['megamaps_admin'])]);
    }

    if ($accion === 'fotos-salones') {
        // Nuevo endpoint: obtener fotos de salones
        try {
            $stmt = $pdo->query(
                'SELECT salon_numero, ruta, creada_en
                 FROM fotos_salones
                 ORDER BY salon_numero, creada_en DESC'
            );
            $fotos = [];
            $vistas = []; // Para evitar duplicados, tomar la más reciente

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $key = 'salon_' . $row['salon_numero'];
                // Solo tomar la primera (más reciente) por salón
                if (!isset($vistas[$key])) {
                    $fotos[$key] = $row['ruta'];
                    $vistas[$key] = true;
                }
            }

            responder(200, ['fotos' => $fotos]);
        } catch (PDOException $e) {
            error_log('Error al obtener fotos de salones: ' . $e->getMessage());
            responder(200, ['fotos' => []]); // Devolver array vacío en caso de error
        }
    }

    if ($accion === 'salones') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $pdo->query('SELECT datos_json FROM salones_aplicacion WHERE id = 1');
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $salones = $fila
                ? validarSalones(json_decode($fila['datos_json'], true))
                : [];
            if ($salones === null) {
                responder(500, ['error' => 'Los datos de salones guardados en SQLite no son válidos.']);
            }
            responder(200, ['salones' => (object) $salones]);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: GET, POST');
            responder(405, ['error' => 'Método no permitido.']);
        }

        if (empty($_SESSION['megamaps_admin'])) {
            responder(401, ['error' => 'Inicia sesión como administrador para guardar salones y docentes.']);
        }

        $entrada = json_decode(file_get_contents('php://input'), true);
        $salones = is_array($entrada) && array_key_exists('salones', $entrada)
            ? validarSalones($entrada['salones'])
            : null;
        if ($salones === null) {
            responder(400, ['error' => 'Los datos de salones o docentes no tienen un formato válido.']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO salones_aplicacion (id, datos_json, actualizado_en)
             VALUES (1, :datos, CURRENT_TIMESTAMP)
             ON CONFLICT(id) DO UPDATE SET
                datos_json = excluded.datos_json,
                actualizado_en = CURRENT_TIMESTAMP'
        );
        $stmt->execute(['datos' => json_encode($salones, JSON_UNESCAPED_UNICODE)]);
        responder(200, ['ok' => true]);
    }

    if ($accion === 'routes') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $rutas = [];
            $stmt = $pdo->query(
                'SELECT clave, camino_json, etiquetas_json
                 FROM rutas_personalizadas
                 ORDER BY clave'
            );

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $camino = json_decode($fila['camino_json'], true);
                $etiquetas = $fila['etiquetas_json'] === null
                    ? null
                    : json_decode($fila['etiquetas_json'], true);
                $ruta = ['camino' => $camino, 'etiquetas' => $etiquetas];
                $partesClave = explode('__', $fila['clave']);
                if (count($partesClave) === 2
                    && identificadorValido($partesClave[0])
                    && identificadorValido($partesClave[1])
                    && rutaValida($ruta)) {
                    $rutas[$fila['clave']] = $ruta;
                }
            }

            responder(200, ['rutas' => $rutas]);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: GET, POST');
            responder(405, ['error' => 'Método no permitido.']);
        }

        if (empty($_SESSION['megamaps_admin'])) {
            responder(401, ['error' => 'Inicia sesión como administrador para guardar rutas.']);
        }

        $entrada = json_decode(file_get_contents('php://input'), true);
        if (!is_array($entrada)
            || !isset($entrada['clave'])
            || !is_string($entrada['clave'])
            || !isset($entrada['ruta'])
            || !rutaValida($entrada['ruta'])) {
            responder(400, ['error' => 'La ruta enviada no tiene un formato válido.']);
        }

        $partesClave = explode('__', $entrada['clave']);
        if (count($partesClave) !== 2
            || !identificadorValido($partesClave[0])
            || !identificadorValido($partesClave[1])) {
            responder(400, ['error' => 'El origen o destino de la ruta no es válido.']);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO rutas_personalizadas
                (clave, camino_json, etiquetas_json, actualizado_en)
             VALUES (:clave, :camino, :etiquetas, CURRENT_TIMESTAMP)
             ON CONFLICT(clave) DO UPDATE SET
                camino_json = excluded.camino_json,
                etiquetas_json = excluded.etiquetas_json,
                actualizado_en = CURRENT_TIMESTAMP'
        );
        $stmt->execute([
            'clave' => $entrada['clave'],
            'camino' => json_encode($entrada['ruta']['camino'], JSON_UNESCAPED_UNICODE),
            'etiquetas' => $entrada['ruta']['etiquetas'] === null
                ? null
                : json_encode($entrada['ruta']['etiquetas'], JSON_UNESCAPED_UNICODE),
        ]);

        responder(200, ['ok' => true]);
    }

    $salon = isset($_GET['salon']) ? trim($_GET['salon']) : '';
    $sql = 'SELECT salon, salon_nombre, piso, jornada, grado, profesor, asignatura
            FROM vista_salones_asignaturas';
    $params = [];

    if ($salon !== '') {
        $sql .= ' WHERE salon = :salon';
        $params['salon'] = $salon;
    }

    $sql .= ' ORDER BY salon, jornada, profesor, asignatura';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $error) {
    error_log('Error en la API de MegaMaps: ' . $error->getMessage());
    responder(500, ['error' => 'No se pudo completar la operación con la base de datos.']);
}
