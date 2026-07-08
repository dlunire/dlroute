# 04 — Registro de rutas y controladores

DLRoute acepta tres formas de definir el handler: **array clase+método**, **closure** y **cadena `Clase@metodo`**. El cuarto argumento de registro permite inyectar datos estáticos y fijar el tipo MIME de respuesta.

## 1. Array clase + método (recomendado)

```php
<?php
use DLRoute\Requests\DLRoute;
use App\Controllers\ProductsController;

DLRoute::get('/api/products', [ProductsController::class, 'index']);
DLRoute::post('/api/products', [ProductsController::class, 'store']);
DLRoute::get('/api/products/{id}', [ProductsController::class, 'show']);
```

El controlador debe extender `DLRoute\Config\Controller` (o `DLCore\Core\BaseController` en DLUnire). DLRoute instancia la clase y llama al método indicado.

```php
<?php
namespace App\Controllers;

use DLRoute\Config\Controller;

final class ProductsController extends Controller {

    public function index(): array {
        return ['products' => []];
    }

    public function show(object $params): array {
        return ['id' => $params->id];
    }
}
```

## 2. Closure (callback)

```php
DLRoute::get('/health', function (object $params, array|object $data) {
    return [
        'status' => 'ok',
        'time'   => date('c'),
        'meta'   => $data,
    ];
}, data: ['version' => '1.0']);
```

| Argumento | Contenido |
|-----------|-----------|
| `$params` | Segmentos capturados de la URI (`{id}` → `$params->id`) |
| `$data` | Cuarto argumento de `get()`/`post()` al registrar la ruta |

## 3. Cadena `Clase@metodo`

```php
DLRoute::get('/legacy', 'App\\Controllers\\HomeController@index');
```

Debe haber **exactamente una** `@` entre namespace/clase y el nombre del método.

## Inyección de datos estáticos

```php
DLRoute::get(
    '/api/info',
    [InfoController::class, 'version'],
    ['min_php' => '8.2', 'package' => 'dlroute']
);
```

```php
public function version(object $params, array|object $data): array {
    return [
        'min_php' => $data['min_php'] ?? null,
        'package' => $data['package'] ?? null,
    ];
}
```

Útil para metadatos de versión, flags de feature o configuración de solo lectura sin tocar variables globales.

## Tipo MIME explícito

```php
DLRoute::get(
    '/export.csv',
    [ExportController::class, 'csv'],
    [],
    'text/csv; charset=utf-8'
);
```

Si el controlador devuelve `string`, `DLOutput` usa el MIME registrado. Arrays siguen serializándose como JSON salvo que indiques otro tipo ([08-dloutput-respuestas.md](08-dloutput-respuestas.md)).

## Convenciones DLUnire

| Elemento | Convención |
|----------|------------|
| Namespace controladores | `DLUnire\Controllers` |
| Nombre de clase | PascalCase, preferible `final` |
| Métodos de acción | **snake_case** (`show_login`, `sales_by_category`) |
| Archivos de rutas | `routes/*.php`, cargados por el bootstrap |

No uses camelCase en métodos nuevos (`showLogin` fallará si el registro espera `show_login`).

## Registro por verbo HTTP

```php
DLRoute::get('/resource', [ResourceController::class, 'index']);
DLRoute::post('/resource', [ResourceController::class, 'store']);
DLRoute::put('/resource/{id}', [ResourceController::class, 'replace']);
DLRoute::patch('/resource/{id}', [ResourceController::class, 'update']);
DLRoute::delete('/resource/{id}', [ResourceController::class, 'destroy']);
DLRoute::head('/resource', [ResourceController::class, 'probe']);
DLRoute::options('/resource', fn () => ['allow' => 'GET, POST']);
```

Cada verbo es independiente: `GET /resource` no ejecuta el handler de `POST`.

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Método no encontrado | camelCase vs snake_case | Usa `show_login` en ruta y controlador |
| Clase no encontrada | PSR-4 incorrecto | Revisa `composer.json` autoload |
| `$params` vacío | Ruta sin `{param}` | Añade segmentos dinámicos o ignora `$params` |
| HTML en lugar de JSON | Retorno `string` sin querer | Devuelve `array` o fija MIME |

## Siguiente paso

Segmentos `{id}` y `{param?}` en [05-parametros-dinamicos.md](05-parametros-dinamicos.md).