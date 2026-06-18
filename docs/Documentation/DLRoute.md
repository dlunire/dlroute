# `DLRoute`

**Namespace:** `DLRoute\Requests`  
**Tipo:** `class`  
**Extiende:** `Route`  
**Implementa:** `RouteInterface`  
**Versión actual:** `v1.0.11`

---

## Descripción

`DLRoute` es el punto de entrada principal del sistema de enrutamiento de DLUnire. Permite registrar rutas para cada método HTTP, ejecutar el despachador y filtrar parámetros dinámicos por tipo o expresión regular.

A diferencia de frameworks como Laravel o Symfony, DLRoute:

- Detecta automáticamente el subdirectorio base sin configuración adicional.
- Valida la sintaxis de las rutas con un lexer propio que indica la posición exacta del error.
- Expone telemetría completa de la petición HTTP de forma nativa.
- No requiere framework — funciona en cualquier proyecto PHP con `composer require`.

---

## Instalación

```bash
composer require dlunire/dlroute
```

Requiere **PHP 8.2+**. Compatible con cualquier proyecto PHP — con o sin framework.

---

## Referencia de métodos

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
DLRoute::get_routes(): array
```

Los métodos individuales (`get`, `post`, etc.) devuelven `DLParamValueType`, lo que permite encadenar `filter_by_type()` directamente. `match()` devuelve `void` — los filtros se declaran dentro del `RouteHandler`.

---

## Tutorial

### 1. Estructura del proyecto

```
mi-proyecto/
├── public/
│   └── index.php          ← punto de entrada
├── app/
│   └── Controllers/
│       └── ApiController.php
└── vendor/
```

### 2. Punto de entrada

```php
<?php

declare(strict_types=1);

use DLRoute\Requests\DLRoute;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Definir rutas aquí

DLRoute::execute();
```

`DLRoute::execute()` debe llamarse siempre al final — despacha la petición contra todas las rutas registradas.

---

### 3. Ruta básica

```php
DLRoute::get('/', fn() => ['status' => 'ok']);
```

Si el controlador o callback devuelve un array u objeto, DLRoute envía automáticamente una respuesta JSON con `Content-Type: application/json`.

---

### 4. Ruta con parámetro dinámico

```php
DLRoute::get('/api/{id}', function(object $params) {
    return ['id' => $params->id];
});
```

Los parámetros dinámicos se acceden como propiedades del objeto `$params`.

---

### 5. Ruta con parámetro tipado

```php
DLRoute::get('/api/{id}', function(object $params) {
    return ['id' => $params->id];
})->filter_by_type(['id' => 'integer']);
```

Si `{id}` no es un entero, DLRoute responde automáticamente con 404. Los tipos predefinidos son:

| Tipo | Descripción |
|---|---|
| `string` | Cualquier cadena de texto |
| `uuid` | UUID (`xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`) |
| `email` | Dirección de correo electrónico válida |
| `integer` | Número entero |
| `float` | Número real |
| `numeric` | Número con o sin decimal |
| `boolean` | Valor booleano |
| `password` | Mínimo 8 caracteres, mayúscula y carácter especial |

Con expresión regular personalizada:

```php
->filter_by_type(['token' => '/^[a-f0-9]{64}$/'])
```

---

### 6. Ruta con parámetro opcional

Un parámetro opcional se declara con `?` dentro de las llaves:

```php
// Registra simultáneamente:
//   /api
//   /api/{uuid}/detalle
DLRoute::get('/api/{uuid?}/detalle', function(object $params) {
    return $params;
})->filter_by_type(['uuid' => 'uuid']);
```

Es equivalente al operador `?.` de los lenguajes modernos — si el parámetro no está presente, la ruta sigue siendo válida.

---

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

---

### 8. Múltiples métodos HTTP con `match()`

`match()` registra la misma ruta para varios métodos HTTP simultáneamente usando un objeto `RouteHandler`:

```php
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;

// Sin filtros
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(
    uri:        '/api/{id}',
    controller: fn(object $params) => $params,
));

// Con filtros de tipo
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(
    uri:             '/api/{id}',
    controller:      fn(object $params) => $params,
    handler_filters: ['id' => 'integer'],
));

// Con controlador, MIME y filtros
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

Los métodos HTTP disponibles para `match()`:

```php
Methods::GET
Methods::POST
Methods::PUT
Methods::PATCH
Methods::DELETE
Methods::HEAD
Methods::OPTIONS
```

---

### 9. Tipo MIME explícito

Por defecto DLRoute determina el MIME automáticamente según el tipo devuelto. Para forzar uno específico:

```php
DLRoute::get('/reporte/{uuid}', [ReporteController::class, 'pdf'], mime_type: 'application/pdf')
    ->filter_by_type(['uuid' => 'uuid']);
```

---

### 10. Telemetría integrada

`TelemetryRequest::telemetry()` expone en tiempo real el contexto completo de la petición, incluyendo los parámetros del querystring como DTOs tipados:

```php
use DLRoute\Core\Telemetry\TelemetryRequest;

DLRoute::get('/{test?}', function() {
    return TelemetryRequest::telemetry("Mi aplicación");
});
```

Respuesta en producción detrás de Cloudflare:

```json
{
    "message": "Mi aplicación",
    "route": "/api/recursos",
    "uri": "/api/recursos?filtro=activo",
    "dir": "/",
    "base_url": "https://mi-dominio.com",
    "domain": "mi-dominio.com",
    "hostname": "mi-dominio.com",
    "is_https": true,
    "port": 443,
    "local_port": 80,
    "timestamp": "2026-06-18T01:20:47+00:00",
    "cliente_ip": "203.0.113.1",
    "method": "GET",
    "proxy": true,
    "query_param": {
        "filtro": {
            "name": "filtro",
            "offset": 0,
            "value": "activo",
            "offset_value": 7,
            "length": 6
        }
    }
}
```

DLRoute diferencia automáticamente:

- `port: 443` — puerto expuesto al cliente
- `local_port: 80` — puerto interno del servidor
- `cliente_ip` — IP real del cliente, no la del proxy
- `proxy: true` — detección automática de proxy inverso

---

### 11. Detección automática de subdirectorio

DLRoute calcula la ruta real mediante aritmética de posición sobre bytes — sin `str_replace()` ni expresiones regulares:

```
OFFSET = LENGTH(dir) - 1
route  = substr(uri, OFFSET)
```

La aplicación funciona correctamente sin importar en qué subdirectorio esté instalada:

```json
{
    "route":    "/api/productos",
    "uri":      "/subdir/subdir/api/productos",
    "dir":      "/subdir/subdir",
    "base_url": "https://ejemplo.com/subdir/subdir"
}
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

El lexer indica: posición exacta del byte problemático, fragmento recibido, ruta completa y formato correcto esperado.

---

### Método HTTP inválido en `match()`

```php
// ❌ Incorrecto
DLRoute::match(['GET', 'POST'], new RouteHandler(...));

// ✅ Correcto
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(...));
```

```
RouteException: DLRoute::match: Se esperaba «DLRoute\Enums\Methods» como elemento
de «$methods». En su lugar se recibió «GET».
```

---

### Array de métodos vacío en `match()`

```php
// ❌ Incorrecto
DLRoute::match([], new RouteHandler(...));
```

```
RouteException: Debe definir, al menos, un método HTTP
```

---

## Véase también

- [`RouteHandler`](RouteHandler.md) — DTO para registro de rutas con múltiples métodos HTTP
- [`QueryParamComposer`](QueryParamComposer.md) — analizador léxico del querystring
- [`QueryParamValue`](QueryParamValue.md) — DTO de par nombre → valor del querystring