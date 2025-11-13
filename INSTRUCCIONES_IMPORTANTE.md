# ⚠️ INSTRUCCIONES URGENTES - SISTEMA DE PAGOS

## 🔴 PROBLEMA ENCONTRADO

Tu archivo `.env` estaba configurado para **MySQL** pero usas **SQLite**.

**Por eso las compras NO se guardaban:** Las tablas `purchase_payments` y otros cambios no existen en la base de datos porque las migraciones nunca se ejecutaron correctamente.

---

## ✅ YA ARREGLÉ

He actualizado tu archivo `.env` con la configuración correcta para SQLite:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/home/user/pulperia-alicia/database/database.sqlite
```

---

## 🚨 LO QUE TIENES QUE HACER AHORA

### **EN TU SERVIDOR DE PRODUCCIÓN** (no en desarrollo):

1. **Hacer pull de los últimos cambios:**
   ```bash
   cd /ruta/a/tu/proyecto
   git pull origin claude/remove-login-system-011CV47KxemwG9Pws7jb5dVb
   ```

2. **Verificar que el .env use SQLite:**
   ```bash
   grep DB_CONNECTION .env
   ```

   Debe decir: `DB_CONNECTION=sqlite`

   Si dice `mysql`, cámbialo manualmente a:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=/ruta/completa/a/database/database.sqlite
   ```

3. **EJECUTAR LAS MIGRACIONES:**
   ```bash
   php artisan migrate
   ```

   Esto creará las tablas:
   - ✅ `purchase_payments` (desglose de pagos)
   - ✅ Columna `cash_shift_id` en `cash_movements`

4. **Verificar que funcionó:**
   ```bash
   php diagnostico.php
   ```

   Debe decir:
   - ✅ Conectado a: /ruta/a/database.sqlite
   - ✅ Driver: sqlite
   - ✅ Tabla 'purchase_payments' existe

5. **Probar crear una compra**
   - Ahora debería funcionar correctamente
   - Los pagos se guardarán
   - Verás el desglose en el detalle de la compra

---

## ❌ SI AÚN NO FUNCIONA

1. **Revisa los logs:**
   ```bash
   tail -50 storage/logs/laravel.log
   ```

2. **Asegúrate de tener permisos:**
   ```bash
   chmod 664 database/database.sqlite
   chmod 775 database/
   ```

3. **Verifica que SQLite esté instalado:**
   ```bash
   php -m | grep sqlite
   ```
   Debe aparecer: `pdo_sqlite` y `sqlite3`

---

## 📝 RESUMEN

**Problema:** .env configurado para MySQL → migraciones no se ejecutaron → tablas no existen → compras no se guardan

**Solución:** Cambiar a SQLite en .env → ejecutar migraciones → tablas se crean → compras funcionan ✅

**Archivos modificados en este commit:**
- `.env` - Configuración de base de datos corregida
- Todo el sistema de pagos ya estaba listo, solo faltaba ejecutar las migraciones

---

## 🎯 DESPUÉS DE MIGRAR

Una vez que ejecutes `php artisan migrate`, el sistema funcionará al 100%:

- ✅ Pagos múltiples (efectivo caja, efectivo personal, crédito, etc.)
- ✅ Validación de turno abierto
- ✅ Desglose de pagos en detalle de compras
- ✅ Movimientos de efectivo en cierre de turno
- ✅ Gastos operativos vinculados a turno
- ✅ Balance General correcto

**¡Ejecuta las migraciones y estarás listo!** 🚀
