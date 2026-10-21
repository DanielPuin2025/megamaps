# Activar la base de datos de MegaMaps

Esta aplicación usa PHP y SQLite. Las instrucciones antiguas sobre Flask, Render y `server.py` ya no aplican.

- Para ejecutar localmente, usa `iniciar.bat` en Windows o `./iniciar.sh` en Linux/macOS.
- Para publicar, usa `docker-compose.yml` y configura `MEGAMAPS_ADMIN_PIN` en el entorno del servicio.
- Conserva el volumen Docker `megamaps-data`: allí quedan las dos bases SQLite y las fotos que suben los administradores.
- En el repositorio, `base de datos/megamaps.sqlite` contiene la información escolar y `base de datos/fotos_rutas_personalidas.sqlite` asocia imágenes a pasos de recorridos.

Consulta [README.md](README.md) para requisitos, comandos y comprobación de salud.
