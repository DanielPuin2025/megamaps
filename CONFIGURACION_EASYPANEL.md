# Configuración de MEGA MAPS en EasyPanel

## Problema identificado
La aplicación queda en estado amarillo (unhealthy) cada vez que se cierra EasyPanel o se reinicia el servidor porque falta configurar la **política de reinicio automático**.

## Solución: Configuración necesaria en EasyPanel

### 1. Configuración del Servicio

En la configuración del servicio en EasyPanel, asegúrate de tener:

#### **Restart Policy (Política de Reinicio)**
```
Restart: always
```
Esta configuración hace que el contenedor se reinicie automáticamente:
- Después de un apagado del servidor
- Si el contenedor falla
- Si se detiene por cualquier razón

#### **Configuración de puertos**
```
Puerto interno: 80
Puerto externo: (el que EasyPanel asigne o el que prefieras)
```

### 2. Variables de Entorno Requeridas

En la sección de **Environment Variables**:

```bash
MEGAMAPS_DATA_DIR=/data
MEGAMAPS_ADMIN_PIN=tu-pin-secreto-aqui-cambiar
```

⚠️ **IMPORTANTE**: Cambia `MEGAMAPS_ADMIN_PIN` por un PIN seguro (no uses "1234").

### 3. Volumen Persistente

**CRÍTICO**: Debes configurar un volumen para que los datos no se pierdan:

```
Volumen:
  - Tipo: Volume
  - Nombre: megamaps-data
  - Mount Path: /data
```

Esto garantiza que:
- Las bases de datos SQLite se mantengan entre reinicios
- Las fotos subidas no se pierdan
- Los cambios de administración persistan

### 4. Health Check (Ya configurado en Dockerfile)

El Dockerfile ya incluye un HEALTHCHECK que verifica cada 30 segundos si la aplicación responde:
```dockerfile
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3
```

EasyPanel usa esto para detectar si la aplicación está saludable (verde) o no (amarillo/rojo).

### 5. Recursos Mínimos Recomendados

```
CPU: 0.5 cores mínimo
RAM: 256 MB mínimo
```

## Pasos para Aplicar la Configuración

1. **Ve a tu proyecto en EasyPanel**
2. **Selecciona el servicio megamaps**
3. **Ve a "Settings" o "Configuración"**
4. **En "Restart Policy"**: selecciona **"Always"** o **"Unless Stopped"**
5. **En "Volumes"**: agrega el volumen para `/data` si no existe
6. **En "Environment"**: verifica que estén las variables de entorno
7. **Guarda los cambios**
8. **Redeploy** el servicio

## Verificación

Después de aplicar estos cambios:

1. Reinicia el servidor o cierra EasyPanel
2. Espera 30-60 segundos
3. La aplicación debería:
   - Reiniciarse automáticamente (estado verde)
   - Mantener todos los datos
   - Funcionar normalmente

## Troubleshooting

### El contenedor sigue en amarillo después de configurar restart policy

Verifica los logs en EasyPanel:
```bash
# Busca errores como:
# - Problemas de permisos en /data
# - Base de datos corrupta
# - Falta de espacio en disco
```

### Los datos se pierden entre reinicios

Verifica que el volumen esté montado correctamente:
- Debe estar en `/data`
- Debe ser persistente (no temporal)
- Debe tener permisos para el usuario `www-data`

### El health check falla constantemente

Posibles causas:
- El puerto 80 no está disponible
- Apache no inicia correctamente
- Las bases de datos no se pueden abrir por permisos

Revisa los logs del contenedor en EasyPanel para más detalles.

## Comandos útiles para verificar en EasyPanel

Si tienes acceso a la terminal del contenedor:

```bash
# Verificar que Apache esté corriendo
ps aux | grep apache

# Verificar que la base de datos exista
ls -la /data/*.sqlite

# Probar el health check manualmente
curl http://127.0.0.1/backend/api.php?action=health

# Verificar permisos
ls -la /data
```

## Contacto de Soporte

Si después de aplicar estos cambios el problema persiste, guarda:
- Los logs del contenedor
- La configuración actual de EasyPanel (screenshot)
- El mensaje de error específico que aparece
