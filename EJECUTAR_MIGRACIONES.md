# ⚠️ IMPORTANTE - Ejecutar Migraciones

Para que el sistema de pagos múltiples funcione correctamente, debes ejecutar las siguientes migraciones en tu servidor de producción:

## Comando a ejecutar:

```bash
php artisan migrate
```

## Migraciones que se aplicarán:

1. **2025_11_12_222154_create_purchase_payments_table.php**
   - Crea la tabla `purchase_payments` para el desglose de pagos
   - Campos: purchase_id, amount, payment_method, affects_cash, notes, user_id

2. **2025_11_12_225723_add_cash_shift_id_to_cash_movements_table.php**
   - Agrega columna `cash_shift_id` a la tabla `cash_movements`
   - Vincula movimientos de efectivo a turnos

## ¿Qué pasa si no ejecutas las migraciones?

❌ Las compras NO se guardarán
❌ Aparecerá error de tabla no encontrada: `purchase_payments`
❌ Los gastos operativos no se vincularán a turnos

## ¿Cómo verificar si ya se ejecutaron?

```bash
php artisan migrate:status
```

Busca las migraciones mencionadas arriba. Si aparecen como "Ran", ya están aplicadas.

---

**Nota:** Este archivo puede ser eliminado después de ejecutar las migraciones.
