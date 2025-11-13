# 🔧 Instrucciones para Arreglar la Base de Datos

## Problema

La tabla `purchase_payments` fue creada con un `ENUM` que tiene el valor `'externo'`, pero el código usa `'efectivo_personal'`. SQLite no puede modificar este tipo de constraint una vez creado.

## Solución

Tienes **2 opciones**:

---

### ⚡ Opción 1: Recrear la Base de Datos (RECOMENDADO para desarrollo)

Esta opción elimina todos los datos y recrea la base de datos desde cero con la estructura correcta.

```bash
# 1. Eliminar la base de datos actual
rm database/database.sqlite

# 2. Crear nueva base de datos vacía
touch database/database.sqlite

# 3. Ejecutar todas las migraciones desde cero
php artisan migrate:fresh --seed
```

**Ventajas:**
- ✅ Limpia y garantizada
- ✅ No quedan datos inconsistentes
- ✅ Rápida

**Desventajas:**
- ❌ Pierdes todos los datos de prueba
- ❌ Necesitas recrear usuarios, productos, etc.

---

### 🛠️ Opción 2: Arreglar Manualmente con Python/Script

Si tienes datos que quieres preservar, usa este script Python:

```bash
# Instalar sqlite3 para Python (si no lo tienes)
pip3 install pysqlite3

# Crear y ejecutar este script:
cat > fix_db.py << 'EOF'
import sqlite3
import json

# Conectar a la base de datos
conn = sqlite3.connect('database/database.sqlite')
cursor = conn.cursor()

# 1. Deshabilitar foreign keys
cursor.execute('PRAGMA foreign_keys = OFF')

# 2. Respaldar datos
cursor.execute('SELECT * FROM purchase_payments')
backup = cursor.fetchall()
print(f"📦 Respaldados {len(backup)} pagos")

# 3. Eliminar tabla
cursor.execute('DROP TABLE IF EXISTS purchase_payments')
print("🗑️  Tabla eliminada")

# 4. Recrear tabla SIN ENUM
cursor.execute('''
    CREATE TABLE purchase_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        purchase_id INTEGER NOT NULL,
        amount NUMERIC(12, 2) NOT NULL,
        payment_method VARCHAR NOT NULL,
        affects_cash INTEGER NOT NULL DEFAULT 0,
        notes TEXT,
        user_id INTEGER NOT NULL,
        created_at DATETIME,
        updated_at DATETIME,
        FOREIGN KEY(purchase_id) REFERENCES inventory_transactions(id) ON DELETE CASCADE,
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )
''')
print("🔨 Tabla recreada sin ENUM")

# 5. Restaurar datos (mapeando 'externo' → 'efectivo_personal')
for row in backup:
    payment_method = 'efectivo_personal' if row[3] == 'externo' else row[3]
    cursor.execute('''
        INSERT INTO purchase_payments
        (id, purchase_id, amount, payment_method, affects_cash, notes, user_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ''', (row[0], row[1], row[2], payment_method, row[4], row[5], row[6], row[7], row[8]))

print(f"📥 Restaurados {len(backup)} pagos")

# 6. Re-habilitar foreign keys
cursor.execute('PRAGMA foreign_keys = ON')

# Confirmar cambios
conn.commit()
conn.close()

print("✅ ¡Base de datos arreglada!")
EOF

python3 fix_db.py
```

---

## Después de Arreglar

Una vez que ejecutes cualquiera de las dos opciones, podrás:

1. ✅ Crear compras con método de pago **"Efectivo personal (no sale del turno)"**
2. ✅ Usar los 5 métodos de pago sin errores:
   - `caja` - Efectivo de caja (afecta turno)
   - `efectivo_personal` - Efectivo personal (no afecta turno)
   - `credito` - A crédito
   - `transferencia` - Transferencia bancaria
   - `tarjeta` - Tarjeta de crédito/débito

## Verificar que Funciona

Intenta crear una compra con:
- Un producto cualquiera
- Método de pago: **Efectivo personal**
- Monto: cualquier valor

Debería guardarse sin errores.

---

## Si Sigues Teniendo Problemas

Contáctame y te ayudo a diagnosticar el problema específico.
