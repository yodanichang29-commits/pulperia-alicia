# 🔴 SOLUCIÓN PARA WINDOWS - Driver SQLite

## El problema

Tu PHP en Windows NO tiene habilitado el driver de SQLite.

**Error:** `could not find driver (Connection: sqlite)`

---

## ✅ SOLUCIÓN: Habilitar SQLite en php.ini

### Paso 1: Encontrar tu archivo php.ini

Ejecuta en tu terminal:
```bash
php --ini
```

Te dirá algo como:
```
Configuration File (php.ini) Path: C:\xampp\php
Loaded Configuration File:         C:\xampp\php\php.ini
```

### Paso 2: Editar php.ini

Abre el archivo `php.ini` con un editor de texto (Notepad++, VSCode, etc.)

### Paso 3: Habilitar las extensiones SQLite

Busca estas líneas en el archivo (usa Ctrl+F para buscar):

```ini
;extension=pdo_sqlite
;extension=sqlite3
```

**IMPORTANTE:** Quita el punto y coma (`;`) del inicio:

```ini
extension=pdo_sqlite
extension=sqlite3
```

### Paso 4: Guardar y reiniciar

1. **Guarda el archivo php.ini**
2. **Reinicia tu servidor:**
   - Si usas **XAMPP**: Reinicia Apache desde el panel de control
   - Si usas **Laragon**: Reinicia todos los servicios
   - Si usas **WAMP**: Reinicia los servicios
   - Si usas **php artisan serve**: Detén (Ctrl+C) y vuelve a ejecutar

### Paso 5: Verificar que funcionó

En la terminal, ejecuta:
```bash
php -m | findstr sqlite
```

Debe mostrar:
```
pdo_sqlite
sqlite3
```

---

## 🧪 Probar que funciona

Después de habilitar las extensiones:

```bash
php diagnostico.php
```

Debe mostrar:
```
✓ Conectado a: C:\ruta\tu\proyecto\database\database.sqlite
✓ Driver: sqlite
✓ Tabla 'purchase_payments' existe
✓ No hay migraciones pendientes
✓ El sistema está configurado correctamente
```

---

## ❌ Si las extensiones no existen

Si al buscar en php.ini NO encuentras las líneas `extension=pdo_sqlite` o `extension=sqlite3`, significa que tu versión de PHP no las incluye.

### Solución: Reinstalar PHP o usar XAMPP/Laragon

1. **Opción 1: Usar XAMPP** (recomendado)
   - Descarga XAMPP desde: https://www.apachefriends.org/
   - Incluye PHP con SQLite habilitado por defecto
   - Más fácil de configurar

2. **Opción 2: Usar Laragon**
   - Descarga Laragon desde: https://laragon.org/
   - Incluye PHP con todas las extensiones necesarias
   - Perfecto para desarrollo Laravel

3. **Opción 3: Descargar PHP oficial**
   - Descarga PHP para Windows desde: https://windows.php.net/download/
   - Elige "Thread Safe" si usas Apache
   - Elige "Non Thread Safe" si usas Nginx o php artisan serve
   - Las extensiones SQLite vienen incluidas

---

## 📝 Notas para Windows

### Rutas en .env

Tu archivo `.env` debe usar rutas de Windows:

```env
DB_CONNECTION=sqlite
DB_DATABASE=C:\ruta\completa\a\tu\proyecto\database\database.sqlite
```

O usa la ruta relativa (Laravel la resolverá):
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### Permisos

Asegúrate de que la carpeta `database/` tenga permisos de escritura.

---

## 🎯 Resumen rápido

1. Abre `php.ini` (usa `php --ini` para encontrarlo)
2. Busca `;extension=pdo_sqlite` y `;extension=sqlite3`
3. Quita el `;` al inicio de ambas líneas
4. Guarda el archivo
5. Reinicia Apache/servidor
6. Verifica con `php -m | findstr sqlite`
7. Prueba con `php diagnostico.php`
8. ¡Crea una compra y debería funcionar! 🎉

---

## ❓ ¿Necesitas ayuda?

Si después de seguir estos pasos sigue sin funcionar:

1. Dime qué servidor web usas (XAMPP, Laragon, WAMP, otro)
2. Ejecuta `php --ini` y compárteme la ruta del php.ini
3. Ejecuta `php -v` y compárteme la versión de PHP
4. Compárteme si encontraste las líneas de SQLite en el php.ini
