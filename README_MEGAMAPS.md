# 🎓 MegaMaps - Sistema de Navegación del Megacolegio

## 📋 Descripción
Sistema web interactivo de navegación y localización para el Megacolegio El Progreso, con gestión de fotografías de salones sin límites de tamaño.

## ✨ Características Principales

### 🗺️ Navegación Interactiva
- Búsqueda de salones por número
- Visualización de rutas personalizadas
- Mapas interactivos por piso
- Información de profesores y horarios

### 📸 Gestión de Fotografías
- **Panel de administrador mejorado** (`admin.html`)
- **Sin límites de tamaño** para subir fotos
- Formatos soportados: JPEG, PNG, WebP
- Vista previa antes de subir
- Múltiples fotos por salón
- Galería organizada por salones

### 🔧 Tecnologías
- Frontend: HTML5, CSS3, JavaScript vanilla
- Backend: PHP 7.4+
- Base de datos: SQLite
- Sin dependencias externas complejas

## 🚀 Instalación

### Requisitos Previos
- Servidor web (Apache, Nginx, o PHP built-in server)
- PHP 7.4 o superior
- SQLite3 habilitado en PHP

### Pasos de Instalación

1. **Copiar archivos al servidor web**
   ```bash
   # Ejemplo con servidor local
   cd /ruta/del/servidor/
   cp -r MEGAMAPS-main/* .
   ```

2. **Configurar permisos**
   ```bash
   chmod 755 .
   chmod 777 fotos/
   chmod 666 megamaps.sqlite
   ```

3. **Configurar PHP** (opcional)
   
   El proyecto incluye archivos de configuración:
   - `.htaccess` - Para Apache
   - `php.ini` - Configuración personalizada de PHP

   **Importante**: Para subir archivos grandes sin límites, verifica en tu `php.ini`:
   ```ini
   upload_max_filesize = 0  ; Sin límite
   post_max_size = 0        ; Sin límite
   max_execution_time = 300
   memory_limit = 512M
   ```

4. **Iniciar el servidor**
   ```bash
   # Opción 1: Servidor PHP integrado
   php -S localhost:8000
   
   # Opción 2: Con Apache/Nginx
   # Accede a través del navegador
   ```

5. **Acceder a la aplicación**
   - Aplicación principal: `http://localhost:8000/index.html`
   - Panel de administrador: `http://localhost:8000/admin.html`
   - Subir fotos (legacy): `http://localhost:8000/subir_foto.html`

## 📱 Uso del Sistema

### Para Usuarios
1. Abre `index.html` en tu navegador
2. Ingresa el número del salón que buscas
3. Visualiza la información y ruta al salón
4. Sigue las instrucciones de navegación

### Para Administradores

#### Subir Fotografías
1. Accede a `admin.html`
2. Selecciona el salón del listado
3. Elige una o varias fotos (sin límite de tamaño)
4. Haz clic en "Subir Foto"
5. La foto se guardará automáticamente

#### Ver Estadísticas
- API de fotos: `http://localhost:8000/api_fotos.php?accion=estadisticas`
- Listar todas: `http://localhost:8000/api_fotos.php?accion=listar`
- Por salón: `http://localhost:8000/api_fotos.php?accion=salon&numero=101`

## 📂 Estructura del Proyecto

```
MEGAMAPS-main/
├── index.html              # Aplicación principal
├── admin.html              # Panel de administrador mejorado
├── subir_foto.html         # Formulario legacy de subida
├── guardar_foto.php        # Backend de subida de fotos
├── api_fotos.php           # API REST para fotos
├── api.php                 # API principal de datos
├── server.py               # Servidor alternativo Python
├── megamaps.sqlite         # Base de datos principal
├── fotos/                  # Carpeta de fotografías
│   └── salones/           # Fotos organizadas
├── assests/               # Recursos multimedia
│   ├── Salones/          # Fotos predefinidas de salones
│   └── fotosSalonesPersonalizadas/
├── .htaccess              # Configuración Apache
├── php.ini                # Configuración PHP personalizada
└── README_MEGAMAPS.md     # Este archivo
```

## 🔒 Seguridad

### Autenticación (Deshabilitada en Desarrollo)
La autenticación está comentada en `guardar_foto.php` para facilitar el desarrollo.

**Para producción**, descomenta las líneas 42-45:
```php
if (empty($_SESSION['megamaps_admin'])) {
    mostrarResultado('Acceso requerido', '...', 401);
    exit;
}
```

### Protección de Archivos
- Los archivos `.sqlite` están protegidos en `.htaccess`
- Las subidas se validan por tipo MIME real
- Rutas sanitizadas para prevenir directory traversal

## 🛠️ Solución de Problemas

### Error: "No se pudo guardar la foto"
- Verifica permisos de la carpeta `fotos/salones/`
- Asegúrate de que SQLite está habilitado: `php -m | grep sqlite`

### Error: "La foto supera el límite"
- Revisa la configuración de PHP (`php.ini`)
- En Apache, verifica que `.htaccess` se esté leyendo
- Reinicia el servidor después de cambios

### Las fotos no se muestran
- Verifica que existan en `fotos/salones/`
- Revisa la base de datos: `sqlite3 megamaps.sqlite "SELECT * FROM fotos_salones;"`
- Verifica permisos de lectura

### Base de datos bloqueada
```bash
# Liberar bloqueos
fuser -k megamaps.sqlite
# O reinicia el servidor
```

## 📊 Base de Datos

### Tabla: fotos_salones
```sql
CREATE TABLE fotos_salones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    salon_numero TEXT NOT NULL CHECK (length(salon_numero) = 3),
    ruta TEXT NOT NULL UNIQUE,
    creada_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

### Consultas Útiles
```sql
-- Ver todas las fotos
SELECT * FROM fotos_salones ORDER BY salon_numero;

-- Contar fotos por salón
SELECT salon_numero, COUNT(*) as total 
FROM fotos_salones 
GROUP BY salon_numero;

-- Eliminar una foto
DELETE FROM fotos_salones WHERE id = 1;
```

## 🔄 Actualización

Para actualizar el sistema:
1. Haz backup de `megamaps.sqlite` y la carpeta `fotos/`
2. Copia los nuevos archivos
3. Restaura tu base de datos y fotos
4. Verifica la configuración

## 📝 Notas Importantes

- **Sin límite de fotos**: Puedes subir tantas fotos como necesites por salón
- **Sin límite de tamaño**: Configura tu servidor según tus necesidades
- **Performance**: Para grandes cantidades de fotos, considera optimizar imágenes
- **Backup**: Respalda regularmente `megamaps.sqlite` y la carpeta `fotos/`

## 👥 Créditos

**Desarrollado por:**
- Daniel Puín
- Equipo de desarrollo MegaMaps

**Institución:**
Megacolegio El Progreso

---

**Versión:** 2.0 (Octubre 2026)
**Última actualización:** 19 de Octubre de 2026
