# 06 — `filter_by_type()` y validación

Tras registrar una ruta, encadena `->filter_by_type([...])` para validar segmentos capturados. Si algún parámetro no cumple el tipo, DLRoute responde **404** sin invocar el controlador.

## Uso básico

```php
DLRoute::get('/api/products/{id}', [ProductsController::class, 'show'])
    ->filter_by_type(['id' => 'integer']);

DLRoute::get('/api/users/{uuid}', [UsersController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);

DLRoute::get('/api/search/{q}', [SearchController::class, 'query'])
    ->filter_by_type(['q' => 'string']);
```

Solo se validan las claves que declares; otros segmentos capturados no se filtran.

## Tipos predefinidos

| Tipo | Comprueba |
|------|-----------|
| `string` | `is_string()` |
| `integer` | `is_int()` |
| `float` | `is_float()` |
| `numeric` | entero o flotante |
| `boolean` | `is_bool()` |
| `uuid` | formato UUID estándar |
| `email` | dirección de correo válida |

## Expresión regular personalizada

```php
DLRoute::get('/api/tokens/{token}', [TokenController::class, 'verify'])
    ->filter_by_type(['token' => '/^[a-f0-9]{64}$/']);
```

Pasa un patrón PCRE como valor del filtro cuando los tipos predefinidos no bastan.

## Varios parámetros

```php
DLRoute::get('/api/{year}/{month}', [ArchiveController::class, 'month'])
    ->filter_by_type([
        'year'  => 'integer',
        'month' => 'integer',
    ]);
```

## Comportamiento ante fallo

```
GET /api/products/abc
    └── filter id => integer
            └── segmento "abc" → string, no int
                    └── 404 (handler no ejecutado)
```

No necesitas `if (!is_numeric($id))` en cada controlador para rutas bien tipadas.

## Con parámetros opcionales

```php
DLRoute::get('/products/{uuid?}/detail', [ProductController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);
```

Si el segmento opcional está presente, debe cumplir el filtro. Si la variante de ruta no incluye `{uuid}`, el filtro no aplica.

## `match()` y filtros en `RouteHandler`

`DLRoute::match()` no devuelve `DLParamValueType`; los filtros van dentro de `RouteHandler`:

```php
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;

DLRoute::match(
    methods: [Methods::GET],
    route: new RouteHandler(
        uri:             '/api/{id}',
        controller:      [ApiController::class, 'show'],
        handler_filters: ['id' => 'integer'],
    )
);
```

Detalle de `match()` en [07-match-routehandler.md](07-match-routehandler.md).

## Trait `DLValidates` en controladores

Los controladores que extienden `Controller` incluyen `DLValidates` para validar **cuerpo** y query en lógica de negocio (`is_email()`, `is_password()`, etc.). Eso es independiente de `filter_by_type()`, que actúa sobre **segmentos de URI** antes del despacho ([09-controladores-peticiones.md](09-controladores-peticiones.md)).

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 404 con ID numérico en URL | Filtro en clave distinta | Nombre en filtro = nombre en `{param}` |
| Filtro no se aplica | Olvidaste encadenar tras `get()` | `->filter_by_type()` sobre el retorno de `get()` |
| UUID válido rechazado | Mayúsculas o formato no estándar | Normaliza en cliente o usa regex custom |
| `match()` sin filtro | Filtros solo en `RouteHandler` | Usa `handler_filters` |

## Siguiente paso

Varios métodos HTTP con `Methods` enum y `RouteHandler` en [07-match-routehandler.md](07-match-routehandler.md).