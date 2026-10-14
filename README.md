# MEGA MAPS — Megacolegio El Progreso

Aplicativo web de navegación y búsqueda de salones para el Megacolegio El Progreso, Yopal, Casanare.

## 📱 Cómo usar

1. **Abre `index.html`** en cualquier navegador (desktop o mobile); para compartir rutas editadas, sirve el proyecto mediante Apache/XAMPP y abre su dirección `http://localhost/...`
2. La aplicación funciona **sin internet** (offline)
3. Los datos locales se guardan en **localStorage**; las rutas personalizadas pueden compartirse entre navegadores mediante PHP y SQLite cuando se usa Apache/XAMPP

## 🎯 Modos

### Usuario
- Selecciona tu ubicación actual
- Busca un salón por número o nombre de docente
- Recibe ruta paso a paso con fotos reales
- Ve información de docentes, grados y áreas

### Administrador
- En modo archivo local (`file://`), el PIN de demostración es `1234`.
- En el servidor, el PIN se configura en `MEGAMAPS_ADMIN_PIN`; no se usa el PIN de demostración.
- Gestiona salones y docentes
- Gestiona fotos por salón y por ruta
- Sube fotos JPEG de salones desde «Gestionar fotos de rutas» en el servidor con PHP y SQLite (requiere XAMPP/Apache)
- Guarda los cambios de salones y docentes en SQLite para compartirlos entre navegadores cuando se usa XAMPP
- Consulta las zonas y los profesores con el asistente de voz; puede leer en voz alta los pasos de navegación
- La guía de voz prefiere voces femeninas en español disponibles en el dispositivo y acompaña las instrucciones con mensajes motivadores
- Selecciona uno de los 5 lugares de inicio y visualiza el paso a paso completo
- Sube o cambia la foto de cada paso; las fotos quedan guardadas para esa ruta
- Edita rutas personalizadas

## 🏢 Estructura

- **31 salones** (101-118, 201-215)
- **40+ espacios** mapeados
- **Grafo BFS** para cálculo automático de rutas
- **5 zonas principales** con fotos reales

## 📊 Datos

- Docentes, grados y áreas actualizados
- Pisos 1 y 2
- Planos del colegio
- Fotos de entrada, cafetería, transición, coordinación y biblioteca

## ⚙️ Tecnología

- HTML + CSS + JavaScript, PHP y SQLite
- Sin dependencias de JavaScript
- Responsive (desktop y mobile)

## 🚀 Despliegue en EasyPanel

El despliegue usa el `Dockerfile` de la raíz, PHP 8.3 con Apache y SQLite. En
EasyPanel crea una aplicación desde el repositorio de GitHub, usa la rama
`main`, el directorio raíz `/` y publica el puerto de contenedor `80`.

Configura estas variables en EasyPanel (no guardes valores reales en Git):

```text
MEGAMAPS_DATA_DIR=/data
MEGAMAPS_PHOTOS_DIR=/data/fotos
MEGAMAPS_ADMIN_PIN=<un secreto aleatorio largo>
```

Agrega almacenamiento persistente con punto de montaje `/data`. Allí quedan
SQLite y las fotos subidas; Apache publica las fotos mediante `/fotos/`.
Mantén una sola instancia de la aplicación porque SQLite no está diseñado para
que varias réplicas escriban simultáneamente sobre un volumen local.

Después de desplegar, verifica `https://tu-dominio/api.php?action=health` y
prueba editar un salón, cerrar sesión, volver a abrir la aplicación y confirmar
que el cambio continúa visible. Configura un dominio con HTTPS antes de usar
el administrador en Internet.

El repositorio puede ser público y contiene nombres que la aplicación muestra
como parte del directorio escolar. La base SQLite, el JSON de origen con datos
de docentes, las fotos cargadas, archivos `.env` y respaldos quedan excluidos
de Git. Para migrar una base existente, transfiérela directamente al volumen
persistente de EasyPanel como `/data/megamaps.sqlite`; no la subas al repositorio.

---

**Proyecto SENA** — Técnica en programación de software
**Megacolegio El Progreso** — Yopal, Casanare, Colombia
