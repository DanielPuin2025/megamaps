# 🚀 MegaMaps - Sistema de Navegación del Megacolegio

## 📋 Descripción
Aplicación web tipo Google Maps para navegación interna del Megacolegio El Progreso. Muestra rutas optimizadas con fotografías de cada punto del recorrido.

## ✨ Características

- 🗺️ Navegación visual con rutas optimizadas
- 📸 Fotos de cada punto del recorrido
- 🎯 Sin redundancia: una foto por punto
- 🔧 Panel de administración completo
- 📱 Diseño responsive (móvil y desktop)
- ⚡ Sin límites de tamaño/formato de fotos

## 📁 Estructura del Proyecto

```
CODIGO_/
├── index.html              # Aplicación principal
├── admin.html              # Panel de administrador
├── api.php                 # Backend API
├── guardar_foto.php        # Subida de fotos
├── .htaccess              # Configuración Apache
├── php.ini                # Configuración PHP
│
├── db/
│   ├── megamaps.db       # Base de datos única
│   └── schema.sql        # Esquema de la BD
│
├── assets/
│   └── fotos/            # Todas las fotografías
│       ├── salones/
│       └── galeria/
│
└── docs/
    ├── PLAN_REORGANIZACION.md
    └── SOLUCION_FOTOS_UNICAS.md
```

## 🗄️ Base de Datos

### Tabla: fotos_salones
```sql
CREATE TABLE fotos_salones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    salon_numero TEXT NOT NULL,
    ruta TEXT NOT NULL UNIQUE,
    creada_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

**Lógica:** Una foto por salón, reutilizada en todas las rutas.

## 🚀 Instalación

### Requisitos
- PHP 7.4+
- Apache con mod_rewrite
- SQLite3

### Pasos
```bash
# 1. Clonar repositorio
git clone https://github.com/DanielPuin2025/megamaps.git

# 2. Configurar permisos
chmod 755 .
chmod 777 assets/fotos/
chmod 666 db/megamaps.db

# 3. Iniciar servidor
php -S localhost:8000

# 4. Acceder
http://localhost:8000/index.html
http://localhost:8000/admin.html (PIN: 1234)
```

## 📱 Uso

### Para Usuarios
1. Ingresa el número del salón
2. Visualiza la ruta con fotos
3. Sigue las indicaciones visuales

### Para Administradores
1. Accede a `admin.html`
2. Login con PIN: 1234
3. Sube fotos por salón
4. Las fotos aparecen automáticamente en todas las rutas

## 🔧 Configuración

### Subida de Fotos
Sin límites configurados por defecto. Para establecer límites:

**php.ini:**
```ini
upload_max_filesize = 50M  ; Límite personalizado
post_max_size = 50M
```

### Formatos Aceptados
- JPEG, JPG, PNG, WebP, GIF, BMP, SVG, TIFF

## 🛠️ API Endpoints

### Fotos de Salones
```
GET api.php?action=fotos-salones
→ Retorna todas las fotos de salones
```

### Login Admin
```
POST api.php?action=admin-login
Body: {"pin": "1234"}
```

### Subir Foto
```
POST guardar_foto.php
FormData: { salon, foto }
```

## 🐛 Solución de Problemas

### No se pueden subir fotos
```bash
# Verificar permisos
chmod 777 assets/fotos/

# Verificar PHP
php -m | grep sqlite
```

### Base de datos bloqueada
```bash
# Liberar
fuser -k db/megamaps.db
```

## 📝 Changelog

### v2.0 (Octubre 2026)
- ✅ Reorganización completa del proyecto
- ✅ Estructura de carpetas limpia
- ✅ Base de datos unificada
- ✅ Sin archivos duplicados
- ✅ Lógica de fotos simplificada
- ✅ Panel admin mejorado

### v1.0 (Septiembre 2026)
- Versión inicial

## 👥 Equipo

- **Desarrollador:** Daniel Puín
- **Institución:** Megacolegio El Progreso
- **Año:** 2026

## 📄 Licencia

Proyecto educativo - Megacolegio El Progreso

## 🔗 Enlaces

- **Repositorio:** https://github.com/DanielPuin2025/megamaps
- **Producción:** http://megamaps.programamega.com
- **EasyPanel:** http://144.126.156.160:3000/projects/yearbook/app/megamaps

---

**Última actualización:** 20 de Octubre 2026
