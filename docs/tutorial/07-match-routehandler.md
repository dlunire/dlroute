# 07 — `match()` y `RouteHandler`

Cuando una misma URI debe responder a varios verbos HTTP con la misma configuración (MIME, filtros, controlador), usa `DLRoute::match()` con un objeto `RouteHandler` y el enum `Methods`.

## Por qué `match()`

```php
// Repetitivo
DLRoute::get('/api/{uuid}', [ApiController::class, 'handle']);
DLRoute::post('/api/{uuid}', [ApiController::class, 'handle']);

// Consolidado
DLRoute::match(
    methods: [Methods::GET, Methods::POST],
    route: new RouteHandler(
        uri:        '/api/{uuid}',
        controller: [ApiController::class, 'handle'],
        handler_filters: ['uuid' => 'uuid'],
        mime_type:  'application/json',
    )
);
```

## Enum `Methods`

Los métodos HTTP son un enum tipado, no cadenas sueltas:

```php
use DLRoute\Enums\Methods;

Methods::GET
Methods::POST
Methods::PUT
Methods::PATCH
Methods::DELETE
Methods::HEAD
Methods::OPTIONS
```

Entrada inválida falla al registrar con JSON estructurado:

```php
// ❌ Error en registro
DLRoute::match(['david'], new RouteHandler(...));

// ✅ Correcto
DLRoute::match([Methods::GET], new RouteHandler(...));
```

```json
{
    "status": false,
    "error": "DLRoute::match: Expected «DLRoute\\Enums\\Methods». Received «david» instead.",
    "details": { "filename": "...", "line": 200 }
}
```

## Clase `RouteHandler`

Namespace: `DLRoute\Core\Data\RouteHandler`

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| `uri` | `string` | Plantilla de ruta (`/api/{id}`) |
| `controller` | `callable\|array\|string` | Handler |
| `data` | `array\|object` | Datos inyectados (opcional) |
| `mime_type` | `?string` | Content-Type de respuesta |
| `handler_filters` | `array` | Mapa para `filter_by_type` |

```php
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;
use DLRoute\Requests\DLRoute;

DLRoute::match(
    methods: [Methods::PATCH, Methods::DELETE],
    route: new RouteHandler(
        uri:             '/api/orders/{id}',
        controller:      [OrdersController::class, 'mutate'],
        handler_filters: ['id' => 'integer'],
        mime_type:       'application/json',
    )
);
```

## API REST con `match()`

```php
// Lectura
DLRoute::match(
    methods: [Methods::GET],
    route: new RouteHandler(
        uri:        '/api/products/{id}',
        controller: [ProductsController::class, 'show'],
        handler_filters: ['id' => 'integer'],
    )
);

// Escritura parcial y borrado
DLRoute::match(
    methods: [Methods::PATCH, Methods::DELETE],
    route: new RouteHandler(
        uri:        '/api/products/{id}',
        controller: [ProductsController::class, 'write'],
        handler_filters: ['id' => 'integer'],
    )
);
```

## Diferencia con métodos individuales

| Aspecto | `get()` / `post()` / … | `match()` |
|---------|------------------------|-----------|
| Retorno | `DLParamValueType` → encadena `filter_by_type()` | `void` |
| Filtros | `->filter_by_type([...])` | `handler_filters` en `RouteHandler` |
| MIME | 4.º argumento o en `RouteHandler` | `mime_type` en `RouteHandler` |
| Métodos | Uno por llamada | Array de `Methods` |

## OPTIONS y CORS

En APIs cross-origin, `OPTIONS` suele responder al preflight. DLCore añade CORS vía `Authorizations::init()` ([integración DLCore](16-integracion-dlcore.md)); con DLRoute puro:

```php
DLRoute::options('/api/{path?}', fn () => ['ok' => true]);
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Error al registrar `match()` | String en lugar de `Methods::` | Usa el enum |
| Filtro ignorado | Filtro fuera de `RouteHandler` | `handler_filters` dentro del DTO |
| Solo un verbo funciona | Falta el método en el array | Incluye todos los `Methods` necesarios |
| 404 en PATCH | No registraste `Methods::PATCH` | Añade el verbo al array |

## Siguiente paso

JSON automático, MIME y personalización de 404 en [08-dloutput-respuestas.md](08-dloutput-respuestas.md).