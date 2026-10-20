<?php
// Corrige rutas de fotos antiguas ("assests/") en bases ya existentes. Es idempotente.
foreach (array_slice($argv, 1) as $archivo) {
    if (!is_file($archivo)) {
        continue;
    }
    $pdo = new PDO('sqlite:' . $archivo);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tablas = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")
        ->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tablas as $tabla) {
        $tieneRuta = false;
        foreach ($pdo->query('PRAGMA table_info("' . $tabla . '")') as $columna) {
            $tieneRuta = $tieneRuta || $columna['name'] === 'ruta';
        }
        if ($tieneRuta) {
            $pdo->exec('UPDATE OR IGNORE "' . $tabla . "\" SET ruta = replace(ruta, 'assests/', 'assets/') WHERE ruta LIKE 'assests/%'");
        }
    }
}
