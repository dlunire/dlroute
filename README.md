# DLRoute – PHP Routing System

## Documentación del Proyecto / Project Documentation

Este repositorio contiene la documentación de los componentes principales del proyecto: manejo de peticiones HTTP, enrutamiento, telemetría y gestión de recursos.

---

## Tabla de Contenidos / Table of Contents

### Request
| Idioma / Language | Documentación / Documentation              |
| ----------------- | ------------------------------------------ |
| English           | [Request-EN](docs/Request/Request-EN.md)   |
| Spanish           | [Request-ES](docs/Request/Request-ES.md)   |

### Router
| Idioma / Language | Documentación / Documentation           |
| ----------------- | --------------------------------------- |
| English           | [Router-EN](docs/Router/Router-EN.md)   |
| Spanish           | [Router-ES](docs/Router/Router-ES.md)   |

### Resource Manager
| Idioma / Language | Documentación / Documentation          |
| ----------------- | -------------------------------------- |
| English / Spanish | [ResourceManager](docs/ResourceManager.md) |

> Cada documento ofrece explicaciones detalladas, ejemplos y buenas prácticas para usar las clases y métodos en tu aplicación PHP.  
> Each document provides detailed explanations, examples, and best practices for using the respective classes and methods in your PHP application.

---

## 🌐 Descripción en Español

**DLRoute** es un sistema de enrutamiento diseñado para facilitar la gestión de rutas y URLs en aplicaciones web PHP, con bajo acoplamiento al entorno de ejecución.

Permite filtrar parámetros por tipo o expresiones regulares, admite contenido JSON en el cuerpo de la petición y, desde la versión **v1.0.4**, proporciona información detallada y coherente del **host, esquema, puertos e IP**, incluso detrás de *reverse proxies*, túneles o en ejecución por CLI.

Desde la versión **v1.0.9**, DLRoute incorpora un **analizador léxico propio** (`RouterLexer`) que valida la sintaxis de las rutas definidas por el desarrollador carácter a carácter, emitiendo diagnósticos precisos con posición exacta del error, el fragmento problemático y la corrección esperada. Ningún framework PHP conocido ofrece esto.

---

## 💾 Instalación

```bash
composer require dlunire/dlroute
```

Ubica tu archivo principal en una carpeta pública (`public/`, `html_public/`, etc.), define tus rutas y ejecuta:

```php
DLRoute::execute();
```

Compatible con **PHP 8.2+**. Instalable en cualquier proyecto PHP — con o sin framework.

---

## ✅ Características

- Definición de rutas simples y complejas.
- Métodos HTTP soportados: `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`.
- Parámetros dinámicos con validación por tipo o expresión regular.
- Parámetros opcionales con sintaxis `{param?}`.
- Controladores o funciones anónimas (*callbacks*).
- Integración con proyectos PHP nativos o con el framework **DLUnire**.
- Detección automática de subdirectorio base — sin configuración adicional.
- Exposición del contexto HTTP completo con telemetría integrada.
- **Lexer propio con diagnóstico de errores de sintaxis por posición exacta** (v1.0.9).
- **Análisis léxico del querystring** con tokens tipados como DTOs inmutables (v1.0.9).

---

## 🆕 Novedades destacadas (v1.0.9)

### Lexer con diagnóstico de errores por posición exacta

Cuando el desarrollador define una ruta con sintaxis inválida, DLRoute no falla en silencio ni lanza un 404 genérico. El `RouterLexer` analiza la ruta byte a byte y emite un diagnóstico completo y accionable:

```php
// Ruta inválida
DLRoute::get('/{ciencia?=ciencia}/usuarios', function() {
    return TelemetryRequest::telemetry("Telemetría de la petición");
});
```

```
Fatal error: Uncaught DLRoute\Errors\RouteException:
Se esperaba una llave de cierre (}) después del símbolo «?» (posición 9).
En su lugar, se recibió «?=ciencia}/usuarios».
Los parámetros opcionales deben tener el formato → «{parametro?}» en la definición de rutas.
Ruta definida: «/{ciencia?=ciencia}/usuarios»
```

El error indica: posición exacta del byte problemático, fragmento recibido, ruta completa y formato correcto esperado. Laravel, Symfony y Slim no ofrecen este nivel de diagnóstico.

---

### Detección automática de subdirectorio base

DLRoute calcula la ruta real de la petición mediante aritmética de posición sobre bytes — sin `str_replace()`, sin `preg_replace()`:

```
OFFSET = LENGTH(dir) - 1
route  = substr(uri, OFFSET)
```

Esto garantiza que la separación entre el directorio de instalación y la ruta definida sea **determinista y O(1)**, sin importar si el nombre del subdirectorio se repite en la URI.

```json
{
    "route":    "/api/usuarios",
    "uri":      "/subdir/subdir/api/usuarios?q=1",
    "dir":      "/subdir/subdir",
    "base_url": "http://localhost:4000/subdir/subdir"
}
```

---

### Análisis léxico del querystring con DTOs inmutables

DLRoute v1.0.9 incorpora un `QueryStringLexer` propio que analiza el querystring de la petición en una sola pasada, produciendo tokens tipados con el enum `QueryStringTokenType`:

- `QUERY_NAME` → nombre del parámetro
- `QUERY_VALUE` → valor del parámetro

Los tokens se componen en pares `nombre → valor` mediante `QueryParamComposer`, entregando instancias inmutables de `QueryParamValue` con tipado estricto:

```php
// Querystring: ?campo=valor&activo
[
    QueryParamValue { name: "campo",  value: "valor", length: 5 },
    QueryParamValue { name: "activo", value: null,    length: 0 },
]
```

Reglas del analizador:
- Parámetros sin valor → `value: null`
- Valores vacíos o en blanco → normalizados a `null`
- Parámetros huérfanos (sin nombre) → descartados silenciosamente
- Separadores consecutivos (`&&&&`) → descartados silenciosamente
- Todo lo que viene después del primer `=` es valor (incluyendo `=` adicionales)

---

### Telemetría integrada

`TelemetryRequest::telemetry()` expone en tiempo real el contexto completo de la petición HTTP, incluyendo ahora los parámetros del querystring como DTOs tipados:

```json
{
    "message":     "Telemetría de la petición",
    "route":       "/api/usuarios",
    "uri":         "/api/usuarios?campo=valor",
    "dir":         "/",
    "base_url":    "http://localhost:4000",
    "domain":      "localhost",
    "hostname":    "localhost:4000",
    "is_https":    false,
    "port":        4000,
    "local_port":  4000,
    "timestamp":   "2026-06-14T03:00:00+00:00",
    "cliente_ip":  "127.0.0.1",
    "method":      "GET",
    "user_agent":  "Mozilla/5.0 ...",
    "proxy":       false,
    "query_param": [
        { "name": "campo", "value": "valor", "length": 5 }
    ]
}
```

---

## 🧠 Contexto del servidor (v1.0.4+)

DLRoute expone información consistente incluso en entornos mal configurados o no estándar:

```json
{
    "dir":        "/subdir",
    "route":      "/ruta/registrada",
    "uri":        "/subdir/ruta/registrada",
    "base_url":   "https://example.com/subdir",
    "domain":     "example.com",
    "hostname":   "example.com:443",
    "is_https":   true,
    "cliente_ip": "203.0.113.1",
    "port":       443,
    "local_port": 4000,
    "method":     "GET",
    "proxy":      true
}
```

---

## 🌐 Control explícito del host externo

Para escenarios específicos (tests, simulaciones, entornos incompletos):

```php
DLServer::set_external_host('example.test', false);
```

- `false` → el host impuesto se usa solo si no se detectó uno válido.
- `true`  → el host impuesto es el único permitido.

---

## ✏️ Sintaxis de rutas

```php
DLRoute::get(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::head(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::post(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::put(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::patch(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::delete(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::options(string $uri, callable|array|string $controller): DLParamValueType;
```

---

## 📌 Ejemplos de uso

### Rutas básicas con controlador

```php
use DLRoute\Requests\DLRoute;
use DLRoute\Test\TestController;

DLRoute::get('/ruta', [TestController::class, 'method']);
DLRoute::get('/ruta/{parametro}', [TestController::class, 'method']);
```

### Definición del controlador

```php
final class TestController extends Controller {
    public function tu_metodo(object $params): object|string {
        return $params;
    }
}
```

### Filtrado por tipo

```php
DLRoute::get('/ruta/{id}', [TestController::class, 'method'])
    ->filter_by_type(['id' => 'numeric']);
```

### Filtrado con expresión regular

```php
DLRoute::get('/ruta/{token}', [TestController::class, 'method'])
    ->filter_by_type(['token' => '/[a-f0-9]+/']);
```

### Tipos admitidos

`string`, `uuid`, `email`, `integer`, `float`, `numeric`, `boolean`.  
Los tipos no soportados se definen con expresiones regulares.

### Parámetros opcionales

```php
DLRoute::get('/products/{uuid?}/product-name', [ProductController::class, 'products']);
```

La ruta `/{uuid?}/product-name` registra simultáneamente:
- `/products`
- `/products/{uuid}/product-name`

### Uso de callbacks

```php
DLRoute::get('/ruta/{parametro}', function (object $params) {
    return $params;
});
```

> Si el controlador o callback devuelve un array u objeto, DLRoute envía automáticamente una respuesta JSON con el Content-Type correspondiente.

---

## 🌍 English Description

**DLRoute** is a simple, flexible, and efficient PHP routing system for modern web applications. It provides advanced route syntax validation, typed querystring parsing, integrated telemetry, and automatic subdirectory detection — features not found together in any other PHP router.

### Installation

```bash
composer require dlunire/dlroute
```

Requires **PHP 8.2+**. Works with any PHP project — framework or vanilla.

### Key Features

- Route definitions with `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`.
- Dynamic parameters with type filtering or regular expressions.
- Optional parameters via `{param?}` syntax.
- **Lexer-based route syntax validation** with exact byte-position error diagnostics (v1.0.9).
- **Typed querystring lexer** producing immutable DTOs (v1.0.9).
- Automatic base subdirectory detection via mathematical offset — no regex, no `str_replace`.
- Integrated telemetry exposing the full HTTP context.
- Deterministic HTTP context even behind reverse proxies, tunnels, or in CLI.

### Highlights (v1.0.9)

- `RouterLexer` catches syntax errors in route definitions with exact position, fragment, and correction hint.
- `QueryStringLexer` parses querystrings in a single pass, producing `QueryParamValue` DTOs with strict types.
- `TelemetryRequest::telemetry()` now includes typed querystring parameters in its output.

### Callback Example

```php
DLRoute::get(uri: '/info', controller: function () {
    return ['status' => 'ok'];
});
```

---

## 👤 Autor / Author

**David E Luna M**

Creador y desarrollador principal de [DLUnire](https://github.com/dlunire) — un ecosistema PHP para construir aplicaciones web orientadas a APIs de forma rápida y robusta.

- GitHub: [@dlunire](https://github.com/dlunire)
- Email: [dlunireframework@gmail.com](mailto:dlunireframework@gmail.com)
- X: [@dlunire](https://x.com/dlunire)
- Facebook: [DLUnire Framework](https://www.facebook.com/profile.php?id=61575156278078)
- License: MIT