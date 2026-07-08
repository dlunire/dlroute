# 02 — Ciclo de despacho

Las llamadas `DLRoute::get()`, `DLRoute::post()`, etc. **registran** rutas; ningún controlador se ejecuta hasta `DLRoute::execute()`. Entender esa separación evita errores de orden en el bootstrap.

## Flujo completo

```
public/index.php
    ├── require autoload
    ├── include routes/*.php     ← efecto lateral: registra rutas
    └── DLRoute::execute()
            ├── RouteGenerator expande {param?}
            ├── compara método HTTP + URI con rutas registradas
            ├── filter_by_type() si aplica
            ├── instancia controlador / ejecuta closure
            └── DLOutput → cabeceras + cuerpo
```

## Registro vs ejecución

```php
<?php
use DLRoute\Requests\DLRoute;

// Fase 1 — registro (se ejecuta al incluir el archivo)
DLRoute::get('/api/users', fn () => ['users' => []]);
DLRoute::post('/api/users', fn () => ['created' => true]);

// Fase 2 — despacho (una sola vez al final)
DLRoute::execute();
```

Si inviertes el orden y llamas `execute()` antes de registrar rutas, obtendrás **404** aunque el archivo de rutas exista.

## Métodos HTTP disponibles

| Método estático | Verbo | Retorno |
|-----------------|-------|---------|
| `get()` | GET | `DLParamValueType` (encadenable) |
| `post()` | POST | `DLParamValueType` |
| `put()` | PUT | `DLParamValueType` |
| `patch()` | PATCH | `DLParamValueType` |
| `delete()` | DELETE | `DLParamValueType` |
| `head()` | HEAD | `DLParamValueType` |
| `options()` | OPTIONS | `DLParamValueType` |
| `match()` | Varios | `void` |

Firma común de los métodos individuales:

```php
DLRoute::get(
    string $uri,
    callable|array|string $controller,
    array|object $data = [],
    ?string $mime_type = null
): DLParamValueType;
```

## Organización de rutas

### Un solo archivo

```php
// routes/web.php
DLRoute::get('/health', fn () => ['ok' => true]);
DLRoute::get('/api/products', [ProductsController::class, 'index']);
```

### Varios archivos

```php
// public/index.php
foreach (glob(dirname(__DIR__) . '/routes/*.php') as $file) {
    require $file;
}
DLRoute::execute();
```

Patrón del skeleton DLUnire: `Boot\Project::run()` incluye `routes/*.php` automáticamente ([16-integracion-dlcore.md](16-integracion-dlcore.md)).

### Prefijos por dominio

```php
// routes/api.php — solo JSON
DLRoute::get('/api/status', fn () => ['api' => 'v1']);

// routes/web.php — HTML
DLRoute::get('/', [HomeController::class, 'index']);
```

## Inspeccionar rutas registradas

```php
$routes = DLRoute::get_routes();
// [ 'GET' => [ '/health' => ..., '/api/users' => ... ], 'POST' => [ ... ] ]
```

Útil en desarrollo y tests. No expone rutas de otros métodos bajo la misma URI.

## `RouteGenerator` y parámetros opcionales

Al registrar `/blog/{year}/{month?}`, `RouteGenerator` crea variantes internas:

- `/blog/{year}`
- `/blog/{year}/{month}`

Ocurre en la fase de registro, no en cada petición. Detalle en [05-parametros-dinamicos.md](05-parametros-dinamicos.md).

## Bootstrap sin autoload (tests)

```php
require 'vendor/autoload.php';

DLRoute::get('/test/ping', fn () => ['pong' => true]);
DLRoute::execute();
```

En PHPUnit, registra rutas en `setUp()` y llama `execute()` simulando `$_SERVER['REQUEST_URI']` y `REQUEST_METHOD`.

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 404 en ruta definida | `execute()` antes del registro | Registra primero, `execute()` al final |
| 404 con método correcto | Verbo distinto (GET vs POST) | Registra el método HTTP adecuado |
| Controlador no corre | Falta `execute()` | Añade `DLRoute::execute()` en `index.php` |
| Ruta duplicada | Misma URI+verbo dos veces | La última definición puede sobrescribir según el orden |

## Siguiente paso

`DLServer`, URI, subdirectorios y contexto de la petición en [03-dlserver-contexto.md](03-dlserver-contexto.md).