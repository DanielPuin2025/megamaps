# SQLite y fotos en MegaMaps

MegaMaps mantiene dos archivos SQLite con funciones diferentes:

- `base de datos/megamaps.sqlite`: información de salones, docentes, rutas y fotos JPEG asociadas a salones.
- `base de datos/fotos_rutas_personalidas.sqlite`: fotos de los pasos de recorrido, organizadas por tipo de lugar.

No son copias entre sí. El backend utiliza consultas preparadas y crea las tablas de aplicación que requiere si no existen. `base de datos/schema.sql` corresponde al catálogo normalizado de docentes y asignaturas.

## Fotos de recorridos

El administrador puede agregar o cambiar fotos de los pasos de una ruta desde MegaMaps. Se aceptan JPEG, PNG y WebP de hasta 5 MB. La asociación se guarda en `fotos_rutas_personalidas.sqlite`; los archivos se escriben en `base de datos/fotos_rutas/` localmente y en el volumen `/data/fotos_rutas/` al publicar con Docker.

Al reemplazar una foto por otra extensión, el backend elimina el archivo generado anterior después de guardar la nueva ruta. Las fotos iniciales permanecen en `frontend/assets/` y no se borran al actualizar rutas.

## Fotos de salones

El panel también ofrece un formulario auxiliar para subir una foto JPEG de hasta 5 MB por salón. Su asociación queda en la tabla `fotos_salones` de `megamaps.sqlite`; las imágenes se almacenan en `backend/fotos/salones/` localmente y en `/data/fotos/salones/` en Docker.

## Copias y regeneración

En producción, conserva el volumen Docker `megamaps-data`, que contiene las bases de datos y las fotos cargadas. No reemplaces el volumen por los archivos semilla del repositorio.

`base de datos/crear_base_datos.py` genera una base inicial desde `datos_salones_seed.json` solo si `megamaps.sqlite` no existe. Se niega a sobrescribir bases existentes para proteger rutas y cambios del administrador.

Consulta [README.md](README.md) para iniciar el servidor y configurar el despliegue.
