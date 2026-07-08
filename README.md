# DLRoute

## ✨ Sponsors

Gracias a las siguientes personas y empresas por apoyar el desarrollo de **DLRoute** y la futura **v2.0.0** (autómatas puros).

### Patrocinadores Activos

<!-- sponsors -->
<!-- GITHUB SPONSORS LIST START -->
<!-- GITHUB SPONSORS LIST END -->
<!-- sponsors -->

---

**¿Quieres formar parte de esto?**

[❤️ Patrocíname en GitHub Sponsors](https://github.com/sponsors/dlunire)

---

DLRoute no es "otro enrutador PHP más". Es un pipeline de enrutamiento construido sobre la teoría de lenguajes formales —los mismos fundamentos utilizados en los compiladores— aplicada por primera vez al despacho de peticiones HTTP en PHP.

```bash
composer require dlunire/dlroute

```

Requiere **PHP 8.2+**. Funciona con cualquier proyecto PHP —con o sin framework—.

---

## Por qué DLRoute es diferente

Cualquier otro enrutador de PHP —FastRoute, Symfony Routing, Laravel Router— fue construido en torno a un único objetivo: mapear URLs a controladores lo más rápido posible. El emparejamiento (*matching*) era el problema; todo lo demás era secundario.

DLRoute se diseñó bajo una premisa distinta: **el enrutamiento es un pipeline de procesamiento formal, no una tabla de búsqueda.**

Esa premisa produce una arquitectura que no existe en ningún otro enrutador de PHP.

---

## Lo que ningún otro enrutador de PHP hace

### 1. Parser de querystring mediante autómata finito

Todos los demás enrutadores utilizan `parse_str()`, una función que existe desde PHP 4.

DLRoute la reemplaza con un autómata finito que procesa la cadena de consulta **byte por byte en una sola pasada**, con estados explícitos (`QUERY_NAME` → `QUERY_VALUE`), emitiendo DTOs tipados e mutables con el desplazamiento exacto en bytes (*offset*) de cada token en la cadena original.

```php
// GET /?campo=valor&activo
$params = (new QueryParamComposer())->get_query_params();

$params['campo']->value;         // "valor"
$params['campo']->offset;        // 0   — posición en bytes del nombre
$params['campo']->offset_value;  // 6   — posición en bytes del valor
$params['campo']->length;        // 5

$params['activo']->value;        // null — parámetro sin valor

```

Ningún otro enrutador de PHP expone metadatos de posición a nivel de byte para los parámetros de la querystring.

---

### 2. Lexer de sintaxis de rutas con diagnóstico de posición exacta

Cuando defines una ruta con una sintaxis inválida, DLRoute no lanza una excepción genérica. El `RouterLexer` analiza la definición de la ruta **carácter por carácter** y emite un diagnóstico completamente accionable:

```php
// Ruta inválida
DLRoute::get('/{ciencia?=algo}/users', fn() => []);

```

```
RouteException: Expected closing brace (}) after «?» (position 9).
Received instead: «?=algo}/users».
Optional parameters must follow the format → «{param?}»
Route defined: «/{ciencia?=algo}/users»

```

Compara esto con lo que hace Laravel ante un método HTTP inválido:

**Laravel** → Una página silenciosa `404 HTML`

**DLRoute** → JSON estructurado con el error exacto, archivo, línea y traza de la pila (*stack trace*)

Esa es la diferencia entre un sistema con contratos formales y uno sin ellos.

---

### 3. Telemetría como ciudadano de primera clase en el núcleo

`TelemetryRequest` reside en `DLRoute\Core\Telemetry` —no es un middleware, no es un plugin—. Fue diseñado desde el inicio como parte del motor.

```php
DLRoute::get('/{resource?}', function() {
    return TelemetryRequest::telemetry("Mi API");
});

```

```json
{
    "message":     "Mi API",
    "route":       "/api/users",
    "uri":         "/api/users?filter=active",
    "base_url":    "[https://mi-dominio.com](https://mi-dominio.com)",
    "domain":      "mi-dominio.com",
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

Una sola llamada. Cero configuración. Funciona correctamente detrás de Cloudflare, proxies inversos de Nginx y túneles, diferenciando automáticamente el `port` (de cara al cliente) del `local_port` (puerto interno del servidor).

Para lograr un resultado equivalente en Laravel necesitas: Telescope + configuración de proxies de confianza + un paquete de logging externo.

---

### 4. Contratos tipados en el registro de rutas

`Methods::GET` es un enum, no una cadena de texto. El enrutador valida el tipo **antes de registrar la ruta**. Si pasas algo inválido, falla inmediatamente con un error JSON estructurado.

```php
// ❌ Incorrecto
DLRoute::match(['david'], new RouteHandler(...));

// ✅ Correcto
DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(...));

```

```json
{
    "status": false,
    "error": "DLRoute::match: Expected «DLRoute\\Enums\\Methods». Received «david» instead.",
    "details": { "filename": "...", "line": 200 }
}

```

Laravel responde silenciosamente con un `404 HTML` ante la misma entrada.

---

### 5. Detección de subdirectorios sin configuración

DLRoute calcula la ruta real de la petición mediante **aritmética de posición de bytes** —sin `str_replace()`, sin expresiones regulares—:

```
OFFSET = LENGTH(dir) - 1
route  = substr(uri, OFFSET)

```

Determinista y O(1), independientemente de si el nombre del subdirectorio aparece repetido en la URI.

```json
{
    "route":    "/api/products",
    "uri":      "/subdir/subdir/api/products",
    "dir":      "/subdir/subdir",
    "base_url": "[https://example.com/subdir/subdir](https://example.com/subdir/subdir)"
}

```

---

## Comparativa de características

| Capacidad                                       | DLRoute | FastRoute | Symfony Router | Laravel Router       |
| ----------------------------------------------- | ------- | --------- | -------------- | -------------------- |
| Parser de querystring por autómata finito       | ✅       | ❌         | ❌              | ❌                    |
| Metadatos de posición de token a nivel de byte  | ✅       | ❌         | ❌              | ❌                    |
| Lexer de sintaxis de rutas con diagnósticos     | ✅       | ❌         | ❌              | ❌                    |
| Posición exacta en bytes en errores de sintaxis | ✅       | ❌         | ❌              | ❌                    |
| Telemetría nativa en el núcleo                  | ✅       | ❌         | ❌              | ❌                    |
| Errores JSON estructurados                      | ✅       | ❌         | ❌              | ❌                    |
| Contratos de métodos HTTP tipados (enum)        | ✅       | ❌         | ❌              | ❌                    |
| Detección de subdirectorios sin configuración   | ✅       | ❌         | ❌              | ❌                    |
| Respuesta JSON automática desde un array        | ✅       | ❌         | ❌              | ❌                    |
| Parámetros opcionales de forma nativa           | ✅       | ❌         | ❌              | solución alternativa |
| Tipo MIME explícito por ruta                    | ✅       | ❌         | ❌              | ❌                    |
| Cero dependencias externas                      | ✅       | ✅         | ❌              | ❌                    |

---

## Inicio rápido

### 1. Estructura del proyecto

```
mi-proyecto/
├── public/
│   └── index.php
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

require dirname(__DIR__) . '/vendor/autoload.php';

// Define las rutas aquí

DLRoute::execute();

```

### 3. Ruta básica

```php
DLRoute::get('/', fn() => ['status' => 'ok']);

```

Los arrays y objetos se serializan automáticamente como JSON con el encabezado `Content-Type` correcto.

### 4. Ruta con parámetro tipado

```php
DLRoute::get('/api/{id}', function(object $params) {
    return ['id' => $params->id];
})->filter_by_type(['id' => 'integer']);

```

Si `{id}` no es un entero, DLRoute responde automáticamente con un `404`. No se requiere código adicional.

### 5. Parámetro opcional

```php
// Registra tanto /products como /products/{uuid}/detail simultáneamente
DLRoute::get('/products/{uuid?}/detail', [ProductController::class, 'show'])
    ->filter_by_type(['uuid' => 'uuid']);

```

### 6. Múltiples métodos HTTP

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

### 7. Tipos de parámetros soportados

| Tipo       | Descripción                                           |
| ---------- | ----------------------------------------------------- |
| `string`   | Cualquier cadena de texto                             |
| `uuid`     | Formato UUID (`xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`) |
| `email`    | Dirección de correo electrónico válida                |
| `integer`  | Número entero                                         |
| `float`    | Número decimal                                        |
| `numeric`  | Número con o sin decimales                            |
| `boolean`  | Valor booleano                                        |


Expresión regular personalizada:

```php
->filter_by_type(['token' => '/^[a-f0-9]{64}$/'])

```

---

## Métodos HTTP soportados

`GET` · `HEAD` · `POST` · `PUT` · `PATCH` · `DELETE` · `OPTIONS`

---

## Tutorial de uso

Guía progresiva en español (16 capítulos): [`docs/tutorial/README.md`](docs/tutorial/README.md)

| # | Tema |
|---|------|
| 1 | [Inicio rápido](docs/tutorial/01-inicio-rapido.md) |
| 2 | [Ciclo de despacho](docs/tutorial/02-ciclo-despacho.md) |
| 3 | [`DLServer` y contexto](docs/tutorial/03-dlserver-contexto.md) |
| 4 | [Registro de rutas y controladores](docs/tutorial/04-registro-controladores.md) |
| 5 | [Parámetros dinámicos](docs/tutorial/05-parametros-dinamicos.md) |
| 6 | [`filter_by_type()`](docs/tutorial/06-filter-by-type.md) |
| 7 | [`match()` y `RouteHandler`](docs/tutorial/07-match-routehandler.md) |
| 8 | [`DLOutput` y respuestas](docs/tutorial/08-dloutput-respuestas.md) |
| 9 | [Controladores y peticiones](docs/tutorial/09-controladores-peticiones.md) |
| 10 | [Query string y autómata](docs/tutorial/10-querystring-automata.md) |
| 11 | [`Router` y telemetría](docs/tutorial/11-router-telemetria.md) |
| 12 | [Subida de archivos](docs/tutorial/12-subida-archivos.md) |
| 13 | [Peticiones salientes](docs/tutorial/13-peticiones-salientes.md) |
| 14 | [Errores y diagnósticos](docs/tutorial/14-errores-diagnosticos.md) |
| 15 | [Despliegue en producción](docs/tutorial/15-despliegue-produccion.md) |
| 16 | [Integración con DLCore](docs/tutorial/16-integracion-dlcore.md) |

Referencia por módulo: [`docs/README.md`](docs/README.md). Kernel DLUnire: [tutorial DLCore](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/README.md).

---

## Parte del ecosistema DLUnire

DLRoute es el motor de enrutamiento de [DLUnire](https://github.com/dlunire) —un framework moderno de PHP para construir aplicaciones web orientadas a APIs de forma rápida y con rigor formal—.

---

## Apoya este proyecto

DLRoute tiene licencia AGPL-3.0 y es gratuito para siempre.

Si tu empresa depende de la infraestructura PHP y valora la corrección formal por encima de la magia de las convenciones, considera convertirte en patrocinador:

* **[GitHub Sponsors](https://github.com/sponsors/dlunire)** — apoyo recurrente para el desarrollo continuo.
* **[Open Collective](https://opencollective.com/dlunire)** — financiación comunitaria transparente.

Disponemos de niveles de patrocinio corporativo con colocación de logotipo, respuesta prioritaria a incidencias y consultoría de arquitectura. Contacto: [dlunireframework@gmail.com](https://www.google.com/search?q=mailto%3Adlunireframework%40gmail.com)

---

## Autor

**David E Luna M** — Creador y desarrollador principal de DLUnire

* GitHub: [@dlunire](https://github.com/dlunire)
* X: [@dlunire](https://x.com/dlunire)
* Correo electrónico: [dlunireframework@gmail.com](https://www.google.com/search?q=mailto%3Adlunireframework%40gmail.com)

---

## Licencia

[GNU Affero General Public License v3.0 (AGPL-3.0)](https://www.google.com/search?q=LICENSE)
