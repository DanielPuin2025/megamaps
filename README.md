# MegaMaps

Aplicación web de navegación del Megacolegio El Progreso. La versión mantenida usa PHP, SQLite y una interfaz web adaptable a celular.

## Estructura activa

```text
frontend/
  index.html                  Aplicación y panel de administración
  assets/                      Imágenes iniciales, logos e integrantes
  manifest.json, sw.js         Instalación y caché sin conexión
backend/
  api.php                      Rutas, salones, inicio de sesión y fotos de recorridos
  guardar_foto.php             Formulario auxiliar de fotos JPEG de salones
base de datos/
  megamaps.sqlite              Datos de salones, docentes y rutas
  fotos_rutas_personalidas.sqlite  Asociación de fotos a pasos de recorridos
  schema.sql                   Esquema usado por crear_base_datos.py
docker/
  Configuración de Apache, PHP y migración de rutas
docker-compose.yml
Dockerfile
iniciar.bat / iniciar.sh       Inicio local
```

`megamaps.sqlite` y `fotos_rutas_personalidas.sqlite` son bases distintas con responsabilidades diferentes; no son copias. Las dos se inicializan como semillas para Docker y luego se conservan en el volumen persistente `/data`. No borres el volumen de producción para actualizar la aplicación.

Las fotos iniciales están en `frontend/assets/`. Las fotos nuevas de recorridos se guardan en `fotos_rutas/` dentro del directorio de datos persistente y su ruta queda registrada en SQLite. El panel acepta JPEG, PNG y WebP de hasta 5 MB. La página auxiliar `frontend/subir_foto.html` acepta JPEG.

## Ejecutar localmente en Windows

Requisitos: PHP 8.3 con la carpeta `ext` de extensiones. `iniciar.bat` carga `pdo_sqlite` y `fileinfo` desde esa instalación y aplica los límites de carga del proyecto.

1. Ejecuta `iniciar.bat`.
2. Abre `http://localhost:8000/` o `http://localhost:8000/frontend/index.html`.
3. La aplicación usa los archivos de datos de `base de datos/`; conserva una copia antes de restaurar o reemplazar esos archivos.

En Linux/macOS, ejecuta `./iniciar.sh`.

## Publicar con Docker

Configura `MEGAMAPS_ADMIN_PIN` con una clave privada en EasyPanel o en `.env` y ejecuta:

```sh
cp .env.example .env
# Edita .env y reemplaza MEGAMAPS_ADMIN_PIN por una clave privada.
docker compose up --build -d
```

La aplicación responde en el puerto `8080` por defecto. Los archivos SQLite y fotos nuevas persisten en el volumen `megamaps-data`. La configuración inicial de administración está en `.env.example`; no uses una clave de demostración en producción.

## Crear una base inicial

`base de datos/crear_base_datos.py` construye una base desde `datos_salones_seed.json` únicamente cuando el archivo SQLite todavía no existe. Se niega a sobrescribir una base existente para no borrar rutas ni datos guardados.

## Verificación rápida

```sh
php -l backend/api.php
php -l backend/guardar_foto.php
```

Inicia el servidor con el script de tu sistema y comprueba `http://127.0.0.1:8000/backend/api.php?action=health`; debe responder `{"status":"ok"}`.
