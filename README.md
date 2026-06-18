# DLRoute

**The only PHP router with a formal lexical engine, finite automaton querystring parser, and native telemetry.**

DLRoute is not "another PHP router". It is a routing pipeline built on formal language theory — the same foundations used in compilers — applied to HTTP request dispatching for the first time in PHP.

```bash
composer require dlunire/dlroute
```

Requires **PHP 8.2+**. Works with any PHP project — with or without a framework.

---

## Why DLRoute is different

Every other PHP router — FastRoute, Symfony Routing, Laravel Router — was built around one goal: map URLs to controllers as fast as possible. Matching was the problem. Everything else was secondary.

DLRoute was built around a different premise: **routing is a formal processing pipeline, not a lookup table.**

That premise produces an architecture that does not exist in any other PHP router.

---

## What no other PHP router does

### 1. Finite automaton querystring parser

Every other router uses `parse_str()` — a PHP function that has existed since PHP 4.

DLRoute replaces it with a finite automaton that processes the querystring **byte by byte in a single pass**, with explicit states (`QUERY_NAME` → `QUERY_VALUE`), emitting immutable typed DTOs with the exact byte offset of each token in the original string.

```php
// GET /?campo=valor&activo
$params = (new QueryParamComposer())->get_query_params();

$params['campo']->value;         // "valor"
$params['campo']->offset;        // 0   — byte position of the name
$params['campo']->offset_value;  // 6   — byte position of the value
$params['campo']->length;        // 5

$params['activo']->value;        // null — parameter without value
```

No other PHP router exposes byte-level position metadata for querystring parameters.

---

### 2. Route syntax lexer with exact-position diagnostics

When you define a route with invalid syntax, DLRoute does not throw a generic exception. The `RouterLexer` analyzes the route definition **character by character** and emits a fully actionable diagnostic:

```php
// Invalid route
DLRoute::get('/{ciencia?=algo}/users', fn() => []);
```

```
RouteException: Expected closing brace (}) after «?» (position 9).
Received instead: «?=algo}/users».
Optional parameters must follow the format → «{param?}»
Route defined: «/{ciencia?=algo}/users»
```

Compare this to what Laravel does with an invalid HTTP method:

**Laravel** → silent `404 HTML page`

**DLRoute** → structured JSON with exact error, file, line, and stack trace

That is the difference between a system with formal contracts and one without.

---

### 3. Telemetry as a first-class citizen of the core

`TelemetryRequest` lives in `DLRoute\Core\Telemetry` — not a middleware, not a plugin. It was designed from the start as part of the engine.

```php
DLRoute::get('/{resource?}', function() {
    return TelemetryRequest::telemetry("My API");
});
```

```json
{
    "message":     "My API",
    "route":       "/api/users",
    "uri":         "/api/users?filter=active",
    "base_url":    "https://my-domain.com",
    "domain":      "my-domain.com",
    "is_https":    true,
    "port":        443,
    "local_port":  80,
    "timestamp":   "2026-06-18T01:20:47+00:00",
    "cliente_ip":  "203.0.113.1",
    "method":      "GET",
    "proxy":       true,
    "query_param": {
        "filter": {
            "name":         "filter",
            "offset":       0,
            "value":        "active",
            "offset_value": 7,
            "length":       6
        }
    }
}
```

Single call. No configuration. Works correctly behind Cloudflare, Nginx reverse proxies, and tunnels — differentiating `port` (client-facing) from `local_port` (internal server port) automatically.

To achieve equivalent output in Laravel you need: Telescope + trusted proxy configuration + an external logging package.

---

### 4. Typed contracts in route registration

`Methods::GET` is an enum, not a string. The router validates the type **before registering the route**. If you pass something invalid, it fails immediately with a structured JSON error.

```php
// ❌ Wrong
DLRoute::match(['david'], new RouteHandler(...));

// ✅ Correct
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(...));
```

```json
{
    "status": false,
    "error": "DLRoute::match: Expected «DLRoute\\Enums\\Methods». Received «david» instead.",
    "details": { "filename": "...", "line": 200 }
}
```

Laravel silently responds with `404 HTML` for the same input.

---

### 5. Zero-configuration subdirectory detection

DLRoute calculates the real request path via **byte-position arithmetic** — no `str_replace()`, no regular expressions:

```
OFFSET = LENGTH(dir) - 1
route  = substr(uri, OFFSET)
```

Deterministic and O(1), regardless of whether the subdirectory name appears repeated in the URI.

```json
{
    "route":    "/api/products",
    "uri":      "/subdir/subdir/api/products",
    "dir":      "/subdir/subdir",
    "base_url": "https://example.com/subdir/subdir"
}
```

---

## Feature comparison

| Capability | DLRoute | FastRoute | Symfony Router | Laravel Router |
|---|---|---|---|---|
| Finite automaton querystring parser | ✅ | ❌ | ❌ | ❌ |
| Byte-level token position metadata | ✅ | ❌ | ❌ | ❌ |
| Route syntax lexer with diagnostics | ✅ | ❌ | ❌ | ❌ |
| Exact byte position on syntax errors | ✅ | ❌ | ❌ | ❌ |
| Native telemetry in the core | ✅ | ❌ | ❌ | ❌ |
| Structured JSON errors | ✅ | ❌ | ❌ | ❌ |
| Typed HTTP method contracts (enum) | ✅ | ❌ | ❌ | ❌ |
| Zero-config subdirectory detection | ✅ | ❌ | ❌ | ❌ |
| Automatic JSON response from array | ✅ | ❌ | ❌ | ❌ |
| Optional parameters natively | ✅ | ❌ | ❌ | workaround |
| Explicit MIME type per route | ✅ | ❌ | ❌ | ❌ |
| Zero external dependencies | ✅ | ✅ | ❌ | ❌ |

---

## Quick start

### 1. Project structure

```
my-project/
├── public/
│   └── index.php
├── app/
│   └── Controllers/
│       └── ApiController.php
└── vendor/
```

### 2. Entry point

```php
<?php
declare(strict_types=1);

use DLRoute\Requests\DLRoute;

require dirname(__DIR__) . '/vendor/autoload.php';

// Define routes here

DLRoute::execute();
```

### 3. Basic route

```php
DLRoute::get('/', fn() => ['status' => 'ok']);
```

Arrays and objects are automatically serialized as JSON with the correct `Content-Type`.

### 4. Route with typed parameter

```php
DLRoute::get('/api/{id}', function(object $params) {
    return ['id' => $params->id];
})->filter_by_type(['id' => 'integer']);
```

If `{id}` is not an integer, DLRoute automatically responds with `404`. No additional code needed.

### 5. Optional parameter

```php
// Registers both /products and /products/{uuid}/detail simultaneously
DLRoute::get('/products/{uuid?}/detail', [ProductController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);
```

### 6. Multiple HTTP methods

```php
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;

DLRoute::match(
    [Methods::GET, Methods::POST],
    new RouteHandler(
        uri:             '/api/{uuid}',
        controller:      [ApiController::class, 'handle'],
        mime_type:       'application/json',
        handler_filters: ['uuid' => 'uuid'],
    )
);
```

### 7. Supported parameter types

| Type | Description |
|---|---|
| `string` | Any text string |
| `uuid` | UUID format (`xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`) |
| `email` | Valid email address |
| `integer` | Integer number |
| `float` | Decimal number |
| `numeric` | Number with or without decimal |
| `boolean` | Boolean value |
| `password` | Min 8 chars, uppercase and special character |

Custom regular expression:

```php
->filter_by_type(['token' => '/^[a-f0-9]{64}$/'])
```

---

## Supported HTTP methods

`GET` · `HEAD` · `POST` · `PUT` · `PATCH` · `DELETE` · `OPTIONS`

---

## Part of the DLUnire ecosystem

DLRoute is the routing engine of [DLUnire](https://github.com/dlunire) — a modern PHP framework for building API-oriented web applications rapidly and with formal rigor.

---

## Support this project

DLRoute is MIT-licensed and free forever.

If your company depends on PHP infrastructure and values formal correctness over convention magic, consider sponsoring:

- **[GitHub Sponsors](https://github.com/sponsors/dlunire)** — recurring support for continued development
- **[Open Collective](https://opencollective.com/dlunire)** — transparent community funding

Corporate sponsorship tiers with logo placement, priority issue response, and architecture consulting are available. Contact: [dlunireframework@gmail.com](mailto:dlunireframework@gmail.com)

---

## Author

**David E Luna M** — Creator and lead developer of DLUnire

- GitHub: [@dlunire](https://github.com/dlunire)
- X: [@dlunire](https://x.com/dlunire)
- Email: [dlunireframework@gmail.com](mailto:dlunireframework@gmail.com)

---

## License

[MIT](LICENSE)