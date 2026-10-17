<?php
declare(strict_types=1);

function megamaps_data_dir(): string
{
    $configured = getenv('MEGAMAPS_DATA_DIR');
    $directory = $configured === false || $configured === '' ? __DIR__ : $configured;

    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo crear el directorio persistente de MegaMaps.');
    }

    return $directory;
}

function megamaps_database_path(): string
{
    return rtrim(megamaps_data_dir(), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . 'megamaps.sqlite';
}

function megamaps_photos_dir(): string
{
    $configured = getenv('MEGAMAPS_PHOTOS_DIR');
    return $configured === false || $configured === ''
        ? __DIR__ . DIRECTORY_SEPARATOR . 'fotos'
        : $configured;
}

function megamaps_get_pdo(): PDO
{
    $databasePath = megamaps_database_path();

    if (!is_file($databasePath)) {
        $legacyDatabase = __DIR__ . DIRECTORY_SEPARATOR . 'megamaps.sqlite';
        if ($databasePath !== $legacyDatabase && is_file($legacyDatabase)
            && !copy($legacyDatabase, $databasePath)) {
            throw new RuntimeException('No se pudo copiar la base de datos existente al almacenamiento persistente.');
        }
    }

    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $schemaPath = __DIR__ . DIRECTORY_SEPARATOR . 'schema.sql';
    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
        throw new RuntimeException('No se pudo leer el esquema SQLite de MegaMaps.');
    }
    $pdo->exec($schema);

    return $pdo;
}

function megamaps_get_route_photos_pdo(): PDO
{
    $name = 'fotos_rutas_personalidas.sqlite';
    $path = rtrim(megamaps_data_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;

    if (!is_file($path)) {
        $seedDir = getenv('MEGAMAPS_SEED_DIR');
        $seed = ($seedDir === false || $seedDir === '' ? __DIR__ . DIRECTORY_SEPARATOR . 'docker' . DIRECTORY_SEPARATOR . 'seed' : $seedDir)
            . DIRECTORY_SEPARATOR . $name;
        if (is_file($seed) && !copy($seed, $path)) {
            throw new RuntimeException('No se pudo copiar la base de fotos de rutas al almacenamiento persistente.');
        }
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}
