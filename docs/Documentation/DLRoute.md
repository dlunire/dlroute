# `DLRoute`

**Namespace:** `DLRoute\Requests`  
**Tipo:** `class`  
**Extiende:** `Route`  
**Implementa:** `RouteInterface`

---

## Descripción

`DLRoute` es el punto de entrada principal del sistema de enrutamiento. Permite registrar rutas para cada método HTTP, ejecutar el despachador y filtrar parámetros dinámicos por tipo o expresión regular.

Desde `v1.0.10`, incluye el método `match()` que permite registrar una misma ruta para múltiples métodos HTTP simultáneamente mediante un objeto `RouteHandler`.

---

## Instalación

```bash
composer require dlunire/dlroute
```

Requiere **PHP 8.2+**. Compatible con cualquier proyecto PHP — con o sin framework.

---

## Métodos de registro de rutas

Todos los métodos de registro devuelven `DLParamValueType`, lo que permite encadenar `filter_by_type()` directamente:

```php
DLRoute::get(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::head(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::post(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::put(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::patch(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::delete(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::options(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType
DLRoute::match(array $methods, RouteHandler $route): void
DLRoute::execute(): void
```

---

## `DLRoute::match()`

Registra una misma ruta para múltiples métodos HTTP simultáneamente.

```php
public static function match(array $methods, RouteHandler $route): void
```

### Parámetros

| Parámetro  | Tipo           | Descripción                                                   |
| ---------- | -------------- | ------------------------------------------------------------- |
| `$methods` | `Methods[]`    | Array de casos del enum `Methods`. Mínimo uno.                |
| `$route`   | `RouteHandler` | Objeto que encapsula URI, controlador, datos, MIME y filtros. |

### Retorno

Este método no devuelve ningún valor (`void`). Realiza de manera interna y dinámica el registro de cada método HTTP en la tabla de enrutamiento principal.

### Comportamiento interno

1. Valida que `$methods` no esté vacío — lanza `RouteException` si lo está.
2. Valida que cada elemento sea una instancia de `Methods` — lanza `RouteException` con el valor recibido si no lo es.
3. Por cada método, invoca dinámicamente `self::{$method_name}()` con los datos del `RouteHandler`.
4. Si `$handler_filters` tiene al menos un filtro, encadena `filter_by_type()` automáticamente.

---

## Tutorial de inicio rápido

### 1. Estructura del proyecto

```
mi-proyecto/
├── public/
│   └── index.php       ← punto de entrada
├── app/
│   └── Controllers/
│       └── ApiController.php
└── vendor/
```

### 2. Configurar el punto de entrada

```php
<?php

declare(strict_types=1);

use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;
use DLRoute\Requests\DLRoute;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Definir rutas aquí

DLRoute::execute();
```

### 3. Ruta básica con callback

```php
DLRoute::get('/', fn() => ['status' => 'ok']);
```

### 4. Ruta con parámetro dinámico

```php
DLRoute::get('/api/{id}', function(object $params) {
    return [
        'id' => $params->id
    ];
});
```

### 5. Ruta con parámetro tipado

```php
DLRoute::get('/api/{id}', function(object $params) {
    return ['id' => $params->id];
})->filter_by_type(['id' => 'integer']);
```

Si `{id}` no es un entero, DLRoute responde automáticamente con 404.

### 6. Ruta con parámetro opcional

```php
// Registra simultáneamente:
//   /api
//   /api/{uuid}/detalle
DLRoute::get('/api/{uuid?}/detalle', function(object $params) {
    return $params;
})->filter_by_type(['uuid' => 'uuid']);
```

### 7. Ruta con controlador

```php
use App\Controllers\ApiController;

DLRoute::get('/api/{uuid}', [ApiController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);
```

```php
// app/Controllers/ApiController.php
final class ApiController {
    public function show(object $params): array {
        return ['uuid' => $params->uuid];
    }
}
```

### 8. Múltiples métodos con `match()` — forma básica

```php
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;

DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(
    uri:        '/api/{id}',
    controller: fn(object $params) => $params,
));
```

### 9. Múltiples métodos con filtros

```php
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(
    uri:             '/api/{id}',
    controller:      fn(object $params) => $params,
    handler_filters: ['id' => 'integer'],
));
```

### 10. Múltiples métodos con controlador, MIME y filtros

```php
DLRoute::match(
    [Methods::GET, Methods::POST, Methods::PUT],
    new RouteHandler(
        uri:             '/api/{uuid}/recurso',
        controller:      [ApiController::class, 'handle'],
        mime_type:       'application/json',
        handler_filters: ['uuid' => 'uuid'],
    )
);
```

### 11. Telemetría integrada

```php
use DLRoute\Core\Telemetry\TelemetryRequest;

DLRoute::get('/{test?}', function() {
    return TelemetryRequest::telemetry("Mi aplicación");
});
```

Respuesta:

```json
{
    "message": "Mi aplicación",
    "route": "/",
    "uri": "/",
    "dir": "/",
    "base_url": "https://mi-dominio.com",
    "is_https": true,
    "port": 443,
    "local_port": 80,
    "proxy": true,
    "query_param": []
}
```

---

## Tipos predefinidos para `filter_by_type()`

| Tipo       | Descripción                                        |
| ---------- | -------------------------------------------------- |
| `string`   | Cualquier cadena de texto                          |
| `uuid`     | UUID (`xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`)      |
| `email`    | Dirección de correo electrónico válida             |
| `integer`  | Número entero                                      |
| `float`    | Número real                                        |
| `numeric`  | Número con o sin decimal                           |
| `boolean`  | Valor booleano                                     |
| `password` | Mínimo 8 caracteres, mayúscula y carácter especial |

Con expresión regular personalizada:

```php
->filter_by_type(['token' => '/^[a-f0-9]{64}$/'])
```

---

## Errores comunes

### Sintaxis inválida en la URI

```php
// ❌ Incorrecto
DLRoute::get('/{ciencia?=algo}/ruta', fn() => []);
```

```
RouteException: Se esperaba una llave de cierre (}) después del símbolo «?» (posición 9).
En su lugar, se recibió «?=algo}/ruta».
Los parámetros opcionales deben tener el formato → «{parametro?}»
Ruta definida: «/{ciencia?=algo}/ruta»
```

```php
// ✅ Correcto
DLRoute::get('/{ciencia?}/ruta', fn() => []);
```

### Método HTTP inválido en `match()`

```php
// ❌ Incorrecto — strings en lugar de Methods::*
DLRoute::match(['GET', 'POST'], new RouteHandler(...));

// ✅ Correcto
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(...));
```

### Array de métodos vacío

```php
// ❌ Incorrecto
DLRoute::match([], new RouteHandler(...));
// RouteException: Debe definir, al menos, un método HTTP
```

---

## Detección automática de subdirectorio

DLRoute detecta automáticamente el directorio base de instalación mediante aritmética de posición sobre bytes — sin `str_replace()` ni expresiones regulares:

```
OFFSET = LENGTH(dir) - 1
route  = substr(uri, OFFSET)
```

Esto significa que la aplicación funciona correctamente sin importar en qué subdirectorio esté instalada, sin ninguna configuración adicional:

```json
{
    "route":    "/api/productos",
    "uri":      "/subdir/api/productos",
    "dir":      "/subdir",
    "base_url": "https://ejemplo.com/subdir"
}
```