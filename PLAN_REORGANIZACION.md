# 🎯 PLAN DE REORGANIZACIÓN COMPLETA - MEGAMAPS

## 📋 ANÁLISIS DE ARCHIVOS ACTUALES

### ❌ ARCHIVOS DUPLICADOS E INNECESARIOS:
```
- api_fotos.php (redundante, funcionalidad ya en api.php)
- subir_foto.html (redundante, reemplazado por admin.html)
- server.py (innecesario, usamos PHP)
- crear_base_datos.py (innecesario, usar SQL directo)
- fotos_salones.sql (duplicado)
- fotos_salones_rutas_.sql (duplicado)
- schema.sql (duplicado de base de datos/schema.sql)
- megamaps.sqlite (duplicado)
- fotos_rutas_personalidas.sqlite (duplicado)
```

### ✅ ARCHIVOS NECESARIOS:
```
index.html - App principal ✅
admin.html - Panel administrador ✅
api.php - Backend único ✅
guardar_foto.php - Subida de fotos ✅
manifest.json, sw.js - PWA ✅
```

## 🗄️ ESTRUCTURA DE BASE DE DATOS SIMPLIFICADA

### Tabla Principal: `salones`
```sql
- id, numero, nombre, piso
→ Info básica de cada salón
```

### Tabla: `nodos`
```sql
- id, nombre, tipo, piso, coordenadas
→ Todos los puntos del mapa (salones, pasillos, etc.)
```

### Tabla: `rutas`
```sql
- id, origen_id, destino_id, camino (JSON)
→ Rutas entre puntos
```

### Tabla: `fotos`
```sql
- id, nodo_id, ruta, descripcion, orden
→ UNA tabla para TODAS las fotos
→ Sin duplicados, sin redundancia
```

## 📁 ESTRUCTURA FINAL PROPUESTA

```
CODIGO_/
├── index.html                  # App principal
├── admin.html                  # Panel admin
├── api.php                     # Backend único
├── guardar_foto.php           # Subida fotos
├── .htaccess                  # Config servidor
├── php.ini                    # Config PHP
├── manifest.json              # PWA
├── sw.js                      # Service Worker
│
├── db/
│   ├── megamaps.db           # Base de datos ÚNICA
│   └── schema.sql            # Esquema limpio
│
├── assets/
│   ├── fotos/                # Todas las fotos aquí
│   │   ├── salones/
│   │   ├── pasillos/
│   │   └── puntos/
│   └── css/
│       └── style.css         # Estilos
│
└── docs/
    └── README.md             # Documentación
```

## 🔧 LÓGICA SIMPLIFICADA

### 1. Sistema de Fotos
```
- UNA foto por nodo (salón, pasillo, punto)
- Se reutiliza en TODAS las rutas
- Sin redundancia
- Tabla: fotos (nodo_id → única foto)
```

### 2. Sistema de Rutas
```
- Origen → Destino = 1 ruta óptima
- Camino guardado como JSON: [paso1, paso2, ..., destino]
- Cada paso muestra su foto única
```

### 3. Panel Admin
```
- Gestionar salones
- Subir/editar fotos (sin límites)
- Configurar rutas
- Vista previa
```

## ⚡ ACCIONES A REALIZAR

### Fase 1: Limpieza (10 min)
1. Eliminar archivos duplicados
2. Mover archivos a carpetas correctas
3. Unificar bases de datos

### Fase 2: Simplificación (15 min)
1. Crear esquema DB simple
2. Migrar datos existentes
3. Simplificar api.php

### Fase 3: Panel Admin (10 min)
1. Mejorar admin.html
2. Agregar gestión de rutas
3. Vista previa de todo

### Fase 4: Testing (5 min)
1. Probar subida fotos
2. Probar rutas
3. Verificar todo

### Fase 5: Deploy (5 min)
1. Commit a GitHub
2. Deploy en EasyPanel
3. Verificar producción

## 🎯 RESULTADO FINAL

✅ Sin archivos duplicados
✅ Estructura clara y simple
✅ Base de datos única optimizada
✅ Una foto por punto (no duplicados)
✅ Panel admin completo
✅ Sin límites para fotos
✅ Código limpio y mantenible

---

**¿Procedo con la reorganización completa?**
Tiempo estimado: 45 minutos
