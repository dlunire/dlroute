# 05 — Parámetros dinámicos en la URI

DLRoute captura segmentos de la URI con la sintaxis `{nombre}` (obligatorio) y `{nombre?}` (opcional). Los valores llegan al controlador como propiedades de un objeto `$params`.

## Parámetros obligatorios — `{param}`

```php
DLRoute::get('/api/products/{id}', [ProductsController::class, 'show']);
```

Petición `GET /api/products/42`:

```php
public function show(object $params, array|object $data = []): array {
    $id = $params->id;  // int 42 si el segmento es numérico
    return ['id' => $id];
}
```

## Coerción de tipos en segmentos

Antes de `filter_by_type()`, DLRoute normaliza segmentos:

| Segmento URI | Tipo en `$params` |
|--------------|-------------------|
| `42` | `int` |
| `3.14` | `float` |
| `true` / `false` | `bool` |
| Texto cualquiera | `string` |

Por eso `filter_by_type(['id' => 'integer'])` funciona tras la coerción numérica.

## Parámetros opcionales — `{param?}`

`RouteGenerator` expande la plantilla en varias rutas al registrar:

```php
DLRoute::get('/blog/{year}/{month?}', [BlogController::class, 'archive']);
```

Variantes internas:

- `/blog/{year}`
- `/blog/{year}/{month}`

Coinciden:

- `GET /blog/2026` → `$params->year = 2026`, `$params->month` ausente
- `GET /blog/2026/03` → ambos presentes

## Varios parámetros

```php
DLRoute::get('/api/users/{user_id}/orders/{order_id}', [OrdersController::class, 'show']);
```

```php
public function show(object $params): array {
    return [
        'user_id'  => $params->user_id,
        'order_id' => $params->order_id,
    ];
}
```

Usa **snake_case** en nombres de parámetro (`user_id`, no `userId`).

## Sintaxis inválida — diagnóstico del lexer

```php
DLRoute::get('/{ciencia?=algo}/users', fn () => []);
```

DLRoute no lanza un 404 genérico: el `RouterLexer` indica la posición exacta:

```
RouteException: Expected closing brace (}) after «?» (position 9).
Received instead: «?=algo}/users».
Optional parameters must follow the format → «{param?}»
Route defined: «/{ciencia?=algo}/users»
```

En desarrollo recibes JSON estructurado con archivo y línea. Detalle en [14-errores-diagnosticos.md](14-errores-diagnosticos.md).

## Parámetros opcionales al final de ruta

Patrón habitual para recursos con y sin identificador:

```php
DLRoute::get('/products/{uuid?}/detail', [ProductController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);
```

Registra rutas para `/products/detail` y `/products/{uuid}/detail` según la expansión del generador.

## Relación con `filter_by_type()`

Los parámetros dinámicos capturan **cualquier** segmento; la validación de tipo es un paso posterior:

```php
DLRoute::get('/api/users/{uuid}', [UsersController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);
```

Si `{uuid}` no cumple el filtro → **404** automático, sin ejecutar el controlador ([06-filter-by-type.md](06-filter-by-type.md)).

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 404 con ID válido | Filtro de tipo rechaza el valor | Revisa `filter_by_type` y coerción |
| Parámetro opcional no coincide | Variante de ruta distinta | Comprueba rutas generadas con `get_routes()` |
| `RouteException` al registrar | Sintaxis `{param?}` incorrecta | Usa `{param?}`, no `{param?=x}` |
| `$params->id` undefined | Nombre distinto en URI | Unifica nombre en `{id}` y acceso |

## Siguiente paso

Tipos predefinidos, regex y 404 en [06-filter-by-type.md](06-filter-by-type.md).