# Instalar MegaMaps en un celular

MegaMaps debe estar publicado en un servidor con HTTPS; no abras el archivo HTML directamente desde el teléfono. El despliegue vigente usa Docker Compose y un volumen persistente para las bases SQLite y las fotos.

1. Abre en el navegador del celular el dominio HTTPS configurado para MegaMaps.
2. Espera a que cargue la aplicación al menos una vez con conexión.
3. En Android, abre el menú del navegador y elige **Instalar aplicación** o **Agregar a pantalla principal**. En iPhone, usa **Compartir → Agregar a inicio**.
4. Para consultar y guardar cambios, el servidor y la conexión deben estar disponibles. Las fotos y recursos visitados pueden quedar en caché, pero el service worker no reemplaza la API ni sincroniza cambios sin conexión.

Para desplegar o ejecutar localmente, consulta [README.md](README.md).
