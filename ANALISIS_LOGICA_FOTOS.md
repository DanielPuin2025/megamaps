# 🔍 ANÁLISIS: Lógica de Fotos de Salones

## 📋 PROBLEMA ACTUAL

Cuando subes una foto para el **Salón 215**:
- Desde "Entrada" → crea clave: `entrada__salon_215__salon_215`
- Desde "Cafetería" → crea clave: `cafeteria__salon_215__salon_215`
- Desde "Biblioteca" → crea clave: `biblioteca__salon_215__salon_215`

**Resultado**: Pide la misma foto 3 veces para el mismo salón.

## ✅ SOLUCIÓN PROPUESTA

### Opción 1: Foto Única por Salón (RECOMENDADO)
```javascript
function getFotoRuta(origenId, destinoNodeId, pasoNodeId){
  // Si el paso ES el destino final (el salón), usar foto del salón
  if (pasoNodeId === destinoNodeId) {
    return getFotoSalon(destinoNodeId) || getFoto(pasoNodeId) || null;
  }
  
  // Para pasos intermedios, usar foto específica de ruta
  return FOTOS_RUTA[claveFotoRuta(origenId,destinoNodeId,pasoNodeId)] 
         || getFoto(pasoNodeId) 
         || null;
}

function getFotoSalon(nodeId){
  // Buscar en base de datos fotos_salones por salon_numero
  // O en FOTOS[nodeId] que ya existe
  return FOTOS[nodeId] || null;
}
```

**Ventajas:**
- ✅ Una sola foto por salón
- ✅ Se reutiliza en todas las rutas
- ✅ Solo fotos intermedias son específicas de ruta

### Opción 2: Simplificar Clave (ALTERNATIVA)
```javascript
function claveFotoRuta(origenId, destinoNodeId, pasoNodeId){
  // Si el paso ES el destino, no incluir origen
  if (pasoNodeId === destinoNodeId) {
    return `salon__${pasoNodeId}`;  // Clave universal
  }
  // Para pasos intermedios, mantener clave completa
  return `${origenId}__${destinoNodeId}__${pasoNodeId}`;
}
```

**Ventajas:**
- ✅ Cambio mínimo en el código
- ✅ Foto del salón se comparte entre rutas
- ✅ Pasos intermedios siguen siendo específicos

## 🎯 RECOMENDACIÓN

**Implementar Opción 1** porque:
1. Usa la tabla `fotos_salones` que ya existe
2. Separa lógicamente fotos de salones vs fotos de pasos
3. Más fácil de mantener y entender
4. Compatible con admin.html que ya guarda por salón

## 📝 CAMBIOS NECESARIOS

### 1. Crear API para obtener fotos de salones
```php
// En api.php
if ($accion === 'fotos-salones') {
    $stmt = $pdo->query('SELECT salon_numero, ruta FROM fotos_salones');
    $fotos = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fotos['salon_' . $row['salon_numero']] = $row['ruta'];
    }
    responder(200, ['fotos' => $fotos]);
}
```

### 2. Cargar fotos de salones en index.html
```javascript
let FOTOS_SALONES = {};

async function cargarFotosSalones(){
  try{
    const resp = await fetch('api.php?action=fotos-salones');
    const data = await resp.json();
    if(data.fotos) FOTOS_SALONES = data.fotos;
  }catch(e){}
}
```

### 3. Modificar getFotoRuta
```javascript
function getFotoRuta(origenId, destinoNodeId, pasoNodeId){
  // Si el paso ES el salón destino, usar foto del salón
  if (pasoNodeId === destinoNodeId) {
    return FOTOS_SALONES[pasoNodeId] || FOTOS[pasoNodeId] || null;
  }
  
  // Para pasos intermedios
  return FOTOS_RUTA[claveFotoRuta(origenId,destinoNodeId,pasoNodeId)] 
         || FOTOS[pasoNodeId] 
         || null;
}
```

## ⚠️ COMPATIBILIDAD

Esta solución:
- ✅ No rompe fotos existentes
- ✅ Compatible con admin.html actual
- ✅ Funciona con fotos ya subidas
- ✅ Mejora gradualmente (no requiere remigracion)

---

**¿Quieres que implemente esta solución?**
