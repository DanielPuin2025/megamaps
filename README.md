# MEGA MAPS

Aplicación web para localizar salones y consultar rutas dentro del Megacolegio El Progreso, Yopal.

## Componentes del proyecto

### Frontend — HTML, CSS y JavaScript

Ubicación: [`frontend/`](./frontend/)

- [`index.html`](./frontend/index.html): estructura de la aplicación, estilos principales y modal del equipo.
- [`style.css`](./frontend/style.css): estilos visuales generales.
- [`script.js`](./frontend/script.js): navegación, búsqueda de salones, panel administrativo, rutas, fotos, sincronización con el backend y almacenamiento local.
- [`nodos.js`](./frontend/nodos.js) y [`aristas.js`](./frontend/aristas.js): nodos y conexiones del mapa para calcular recorridos.
- [`sitios.js`](./frontend/sitios.js): lugares de origen disponibles.
- [`sw.js`](./frontend/sw.js): funcionamiento offline y caché de recursos.
- `assets/`: planos, fotos de salones, fotos de rutas, íconos e imágenes de los integrantes.

Funciones: buscar salones, mostrar docentes y jornadas, calcular rutas, visualizar pasos y fotos, guardar rutas personalizadas y administrar salones.

### Backend — PHP y Flask/Python

Ubicación: [`backend/`](./backend/)

- [`api.php`](./backend/api.php): API principal actual. Usa PHP, PDO y SQLite para consultar y guardar salones, rutas personalizadas y fotos de recorridos. También valida datos y protege operaciones administrativas.
- [`server.py`](./backend/server.py): servidor alternativo o legado en Python con Flask, Flask-CORS y SQLite. Expone endpoints para salones, rutas, sitios y estado del servicio.
- [`requirements.txt`](./backend/requirements.txt): dependencias del servidor Flask.

La aplicación usa principalmente `api.php`; Flask sirve como alternativa para una API Python independiente.

### Base de datos — SQLite y SQL

Ubicación: [`base de datos/`](./base%20de%20datos/)

- `megamaps.sqlite`: salones, profesores, asignaturas y relaciones.
- `fotos_rutas_personalidas.sqlite`: fotos asociadas a pasos de rutas personalizadas.
- [`schema.sql`](./base%20de%20datos/schema.sql): tablas, claves foráneas, índices y vistas.
- [`datos_salones_seed.json`](./base%20de%20datos/datos_salones_seed.json): datos iniciales de salones.
- [`crear_base_datos.py`](./base%20de%20datos/crear_base_datos.py): creación o preparación de la base.

La base organiza salones, profesores, asignaturas, jornadas mañana/tarde, docentes itinerantes y sus relaciones.

## Ejecución local

Para usar solo la interfaz, abre [`frontend/index.html`](./frontend/index.html). Para sincronizar cambios entre dispositivos, sirve el proyecto con Apache/XAMPP, PHP y PDO SQLite habilitados.

Como alternativa, instala [`backend/requirements.txt`](./backend/requirements.txt) y ejecuta `python backend/server.py`.

## Documentación adicional

- [`COMO_ACTIVAR_LA_BASE_DE_DATOS.md`](./COMO_ACTIVAR_LA_BASE_DE_DATOS.md)
- [`COMO_USAR_SQLITE_DESDE_PHP.md`](./COMO_USAR_SQLITE_DESDE_PHP.md)
- [`COMO_FUNCIONA_SIN_INTERNET.md`](./COMO_FUNCIONA_SIN_INTERNET.md)
- [`COMO_INSTALAR_EN_CELULAR.md`](./COMO_INSTALAR_EN_CELULAR.md)
