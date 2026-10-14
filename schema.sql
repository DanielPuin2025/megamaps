PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS salones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero TEXT NOT NULL UNIQUE,
    nombre TEXT NOT NULL,
    piso INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS profesores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS asignaturas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS profesor_salones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    salon_id INTEGER NOT NULL REFERENCES salones(id) ON DELETE CASCADE,
    profesor_id INTEGER NOT NULL REFERENCES profesores(id) ON DELETE CASCADE,
    jornada TEXT NOT NULL,
    grado TEXT,
    area_original TEXT
);

CREATE TABLE IF NOT EXISTS profesor_asignaturas (
    profesor_salon_id INTEGER NOT NULL REFERENCES profesor_salones(id) ON DELETE CASCADE,
    asignatura_id INTEGER NOT NULL REFERENCES asignaturas(id) ON DELETE CASCADE,
    PRIMARY KEY (profesor_salon_id, asignatura_id)
);

CREATE TABLE IF NOT EXISTS profesores_itinerantes (
    profesor_id INTEGER PRIMARY KEY REFERENCES profesores(id) ON DELETE CASCADE,
    area_original TEXT,
    nota TEXT
);

CREATE TABLE IF NOT EXISTS profesor_itinerante_asignaturas (
    profesor_id INTEGER NOT NULL REFERENCES profesores_itinerantes(profesor_id) ON DELETE CASCADE,
    asignatura_id INTEGER NOT NULL REFERENCES asignaturas(id) ON DELETE CASCADE,
    PRIMARY KEY (profesor_id, asignatura_id)
);

CREATE TABLE IF NOT EXISTS rutas_personalizadas (
    clave TEXT PRIMARY KEY,
    camino_json TEXT NOT NULL,
    etiquetas_json TEXT,
    actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS salones_aplicacion (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    datos_json TEXT NOT NULL,
    actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fotos_salones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    salon_numero TEXT NOT NULL CHECK (length(salon_numero) = 3),
    ruta TEXT NOT NULL UNIQUE,
    creada_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_fotos_salones_numero
    ON fotos_salones (salon_numero);

CREATE VIEW IF NOT EXISTS vista_salones_docentes AS
SELECT
    s.numero AS salon,
    s.nombre AS salon_nombre,
    s.piso AS piso,
    ps.jornada AS jornada,
    ps.grado AS grado,
    p.nombre AS profesor,
    ps.area_original AS area
FROM profesor_salones AS ps
JOIN salones AS s ON s.id = ps.salon_id
JOIN profesores AS p ON p.id = ps.profesor_id;

CREATE VIEW IF NOT EXISTS vista_salones_asignaturas AS
SELECT
    s.numero AS salon,
    s.nombre AS salon_nombre,
    s.piso AS piso,
    ps.jornada AS jornada,
    ps.grado AS grado,
    p.nombre AS profesor,
    COALESCE(a.nombre, ps.area_original) AS asignatura
FROM profesor_salones AS ps
JOIN salones AS s ON s.id = ps.salon_id
JOIN profesores AS p ON p.id = ps.profesor_id
LEFT JOIN profesor_asignaturas AS pa ON pa.profesor_salon_id = ps.id
LEFT JOIN asignaturas AS a ON a.id = pa.asignatura_id;
