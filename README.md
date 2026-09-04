# DLRoute

**DLRoute** es un motor de enrutamiento para PHP construido como un pipeline de análisis y resolución de peticiones, con componentes basados en conceptos de teoría de lenguajes formales.

No se limita a asociar una URI con un controlador. El motor analiza rutas y parámetros, conserva información estructural durante el procesamiento y utiliza esa información para resolver, validar y ejecutar una petición.

```bash
composer require dlunire/dlroute
```

Requiere **PHP 8.2+** y puede utilizarse en proyectos PHP existentes, con o sin framework.

---

## Por qué DLRoute es diferente

La arquitectura de DLRoute parte de una premisa:

> **El enrutamiento puede tratarse como un pipeline de procesamiento formal, no solamente como una tabla de búsqueda URI → controlador.**

La ruta de una petición atraviesa diferentes etapas de análisis y resolución:

```text
Petición HTTP
     ↓
DLServer
     ↓
normalización del contexto
     ↓
análisis de la ruta
     ↓
resolución de parámetros
     ↓
validación
     ↓
resolución del controlador
     ↓
ejecución
     ↓
respuesta HTTP
```

Esta arquitectura permite que información obtenida durante el análisis de la petición pueda utilizarse posteriormente por otras partes del motor.

---

## Características principales

### 1. Parser de query string basado en autómata

DLRoute implementa su propio procesamiento de parámetros de query string mediante un autómata que recorre la entrada byte por byte.

El parser conserva información estructural de cada parámetro, incluyendo sus posiciones dentro de la cadena original.

Por ejemplo:

```text
GET /?campo=valor&activo
```

puede producir información equivalente a:

```php
$params['campo']->value;        // "valor"
$params['campo']->offset;       // posición del nombre
$params['campo']->offset_value; // posición del valor
$params['campo']->length;       // longitud del valor

$params['activo']->value;       // null
```

Esto permite que el procesamiento de parámetros no sea solamente una conversión de:

```text
string → array
```

sino un análisis estructurado de la entrada original.

La información de posición también puede ser utilizada por otras capas del ecosistema DLUnire.

---

### 2. Lexer de rutas con diagnóstico estructural

Las definiciones de rutas son analizadas mediante `RouterLexer`.

El lexer procesa la sintaxis de la ruta y distingue elementos como:

* texto literal;
* parámetros;
* parámetros opcionales;
* delimitadores;
* estructuras inválidas.

Por ejemplo:

```php
DLRoute::get('/{ciencia?=algo}/users', fn() => []);
```

contiene una definición inválida de parámetro opcional.

En lugar de tratar la URI como una cadena opaca, el lexer puede identificar la posición en la que la estructura deja de cumplir la gramática esperada y producir un diagnóstico asociado a esa posición.

El objetivo es que un error de definición sea **diagnosticable**, no simplemente detectable.

---

### 3. Parámetros opcionales generados por el motor

Los parámetros opcionales forman parte de la gramática de DLRoute:

```php
DLRoute::get(
    '/products/{uuid?}/detail',
    [ProductController::class, 'show']
);
```

El motor puede generar las variantes de ruta correspondientes durante el registro.

La generación ocurre en la infraestructura de routing, no mediante lógica adicional dentro del controlador.

Esto permite que una declaración pueda representar múltiples formas válidas de una ruta.

---

### 4. Contratos tipados para métodos HTTP

DLRoute utiliza enums para representar métodos HTTP internamente:

```php
use DLRoute\Enums\Methods;

DLRoute::match(
    [Methods::GET, Methods::POST],
    new RouteHandler(
        uri: '/api/{uuid}',
        controller: [ApiController::class, 'handle'],
        mime_type: 'application/json',
        handler_filters: [
            'uuid' => 'uuid'
        ]
    )
);
```

El método HTTP forma parte del contrato de registro de la ruta y no se representa internamente únicamente como una cadena arbitraria.

Esto permite que PHP realice comprobaciones de tipo durante el registro.

---

### 5. Validación y tipado de parámetros

Las rutas pueden declarar la semántica esperada de sus parámetros:

```php
DLRoute::get(
    '/api/{id}',
    function (object $params) {
        return [
            'id' => $params->id
        ];
    }
)->filter_by_type([
    'id' => 'integer'
]);
```

DLRoute dispone de tipos predefinidos como:

```text
string
uuid
email
integer
float
numeric
boolean
```

También puede utilizarse una expresión regular personalizada:

```php
->filter_by_type([
    'token' => '/^[a-f0-9]{64}$/'
]);
```

La validación forma parte del pipeline de resolución de la ruta, de manera que el controlador no necesita implementar manualmente estas comprobaciones.

---

### 6. Contextos de registro

DLRoute permite registrar grupos de rutas dentro de un contexto semántico.

Por ejemplo:

```php
$auth->require_auth(function () {
    DLRoute::get('/profile', fn() => [
        'status' => true
    ]);
});
```

El contexto no ejecuta condicionalmente el callback según el estado de la sesión.

Su función es modificar el **contexto de registro** mientras se declaran las rutas.

Internamente, una ruta registrada dentro de este contexto adquiere una identidad diferenciada:

```text
/profile
AUTH-/profile
```

Esto permite que una misma URI pueda tener simultáneamente una representación pública y otra que requiere autenticación.

La selección entre ambas ocurre posteriormente durante la resolución de la petición.

El contexto se restaura mediante `finally`, por lo que su estado no se propaga accidentalmente a las declaraciones posteriores.

---

### 7. Telemetría integrada

DLRoute incluye infraestructura de telemetría dentro de su propio núcleo.

Por ejemplo:

```php
DLRoute::get('/telemetry', function () {
    return TelemetryRequest::telemetry('Mi API');
});
```

La información disponible puede incluir:

```json
{
    "message": "Mi API",
    "route": "/api/users",
    "uri": "/api/users?filter=active",
    "base_url": "https://mi-dominio.com",
    "domain": "mi-dominio.com",
    "is_https": true,
    "port": 443,
    "local_port": 80,
    "method": "GET",
    "proxy": true,
    "query_param": {
        "filter": {
            "name": "filter",
            "offset": 0,
            "value": "active",
            "offset_value": 7,
            "length": 6
        }
    }
}
```

La infraestructura distingue entre información correspondiente al origen público de la petición y el contexto local del servidor.

Por ejemplo:

```text
port       → puerto expuesto al cliente
local_port → puerto utilizado internamente por el servidor
```

Esto resulta particularmente útil cuando la aplicación se encuentra detrás de proxies inversos, balanceadores o servicios como Cloudflare.

---

### 8. Detección de subdirectorios

DLRoute puede determinar la ruta lógica de una petición incluso cuando la aplicación se encuentra instalada en un subdirectorio.

Por ejemplo:

```json
{
    "route": "/api/products",
    "uri": "/subdir/subdir/api/products",
    "dir": "/subdir/subdir",
    "base_url": "https://example.com/subdir/subdir"
}
```

La resolución utiliza la información estructural disponible en el contexto de la petición, evitando depender de sustituciones globales de cadenas para determinar la ruta lógica.

El objetivo es conservar una transformación determinista entre:

```text
URI recibida
      ↓
directorio de instalación
      ↓
ruta lógica
```

---

### 9. Respuestas estructuradas

Los controladores pueden devolver directamente arrays u objetos:

```php
DLRoute::get('/', fn() => [
    'status' => 'ok'
]);
```

DLRoute determina automáticamente una representación apropiada para tipos comunes de respuesta.

Por ejemplo, arrays y objetos pueden serializarse como JSON:

```http
Content-Type: application/json; charset=utf-8
```

También puede establecerse explícitamente el tipo MIME:

```php
DLRoute::get(
    '/data',
    fn() => $data,
    mime_type: 'application/json'
);
```

---

## Ejemplos

### Ruta básica

```php
DLRoute::get('/check', fn() => [
    'status' => true
]);
```

### Controlador de clase

```php
DLRoute::get(
    '/logout',
    [AuthController::class, 'logout']
);
```

### Parámetro dinámico

```php
DLRoute::get(
    '/users/{id}',
    function (object $params) {
        return [
            'id' => $params->id
        ];
    }
);
```

### Parámetro tipado

```php
DLRoute::get(
    '/users/{id}',
    function (object $params) {
        return [
            'id' => $params->id
        ];
    }
)->filter_by_type([
    'id' => 'integer'
]);
```

### Parámetro opcional

```php
DLRoute::get(
    '/products/{uuid?}/detail',
    [ProductController::class, 'show']
)->filter_by_type([
    'uuid' => 'uuid'
]);
```

### Múltiples métodos

```php
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;

DLRoute::match(
    [Methods::GET, Methods::POST],
    new RouteHandler(
        uri: '/api/{uuid}',
        controller: [ApiController::class, 'handle'],
        mime_type: 'application/json',
        handler_filters: [
            'uuid' => 'uuid'
        ]
    )
);
```

### Ruta pública y ruta autenticada

```php
DLRoute::get('/profile', fn() => [
    'scope' => 'PUBLIC'
]);

$auth->require_auth(function () {
    DLRoute::get('/profile', fn() => [
        'scope' => 'AUTHENTICATED'
    ]);
});
```

Las dos declaraciones pueden coexistir porque internamente representan identidades diferentes:

```text
/profile
AUTH-/profile
```

La resolución de la petición utiliza el estado de autenticación para determinar qué representación debe ejecutarse.

---

## Tipos de parámetros soportados

| Tipo      | Descripción                     |
| --------- | ------------------------------- |
| `string`  | Cadena de texto                 |
| `uuid`    | Identificador UUID              |
| `email`   | Dirección de correo electrónico |
| `integer` | Número entero                   |
| `float`   | Número decimal                  |
| `numeric` | Número entero o decimal         |
| `boolean` | Valor booleano                  |

También pueden definirse expresiones regulares personalizadas.

---

## Métodos HTTP soportados

```text
GET
HEAD
POST
PUT
PATCH
DELETE
OPTIONS
QUERY
```

---

## Arquitectura

DLRoute está organizado alrededor de varias responsabilidades especializadas.

```text
                    HTTP Request
                         │
                         ▼
                     DLServer
                         │
                         ▼
                 Router / Lexer
                         │
              ┌──────────┴──────────┐
              ▼                     ▼
          Route tokens          Query parser
              │                     │
              ▼                     ▼
       Route generation        Query parameters
              │                     │
              └──────────┬──────────┘
                         ▼
                   Route metadata
                         │
                         ▼
                  Parameter filters
                         │
                         ▼
                 Route resolution
                         │
                         ▼
                    Controller
                         │
                         ▼
                      Output
```

La intención es que cada etapa tenga una responsabilidad definida y que la información producida por una etapa pueda ser utilizada por las siguientes.

---

## Comparativa conceptual

DLRoute puede compararse con otros routers PHP, pero su diferencia principal no está solamente en la cantidad de características.

| Capacidad                                              | DLRoute |               Otros routers PHP               |
| ------------------------------------------------------ | :-----: | :-------------------------------------------: |
| Lexer propio para la sintaxis de rutas                 |    ✅    |          Depende de la implementación         |
| Análisis estructurado de query strings                 |    ✅    |          Depende de la implementación         |
| Metadatos de posición de parámetros                    |    ✅    |       No es una característica habitual       |
| Diagnósticos asociados a posiciones de la entrada      |    ✅    |          Depende de la implementación         |
| Parámetros opcionales integrados en el generador       |    ✅    |          Depende de la implementación         |
| Tipado de parámetros en la definición de ruta          |    ✅    |          Depende de la implementación         |
| Contextos semánticos de registro                       |    ✅    |    Depende del sistema de middleware/guards   |
| Identidad interna diferenciada para rutas autenticadas |    ✅    |                Modelo diferente               |
| Telemetría integrada en el núcleo                      |    ✅    | Generalmente mediante componentes adicionales |
| Respuestas JSON automáticas                            |    ✅    |             Depende del framework             |
| Tipo MIME explícito por ruta                           |    ✅    |          Depende de la implementación         |
| Dependencias externas de runtime                       |  **0**  |          Depende del router/framework         |

La comparación importante no es solamente:

```text
¿Qué características tiene?
```

Sino:

```text
¿Cómo modela internamente el procesamiento de una petición?
```

`DLRoute` está diseñado alrededor de un pipeline explícito de análisis, transformación y resolución.

---

## Inicio rápido

### 1. Instalar

```bash
composer require dlunire/dlroute
```

### 2. Punto de entrada

```php
<?php

declare(strict_types=1);

use DLRoute\Requests\DLRoute;

require dirname(__DIR__) . '/vendor/autoload.php';

DLRoute::get('/', fn() => [
    'status' => 'ok'
]);

DLRoute::execute();
```

### 3. Crear una ruta

```php
DLRoute::get('/api/{id}', function (object $params) {
    return [
        'id' => $params->id
    ];
});
```

### 4. Agregar validación

```php
DLRoute::get('/api/{id}', function (object $params) {
    return [
        'id' => $params->id
    ];
})->filter_by_type([
    'id' => 'integer'
]);
```

---

## Documentación

DLRoute dispone de una guía progresiva de 16 capítulos:

| #  | Tema                              |
| -- | --------------------------------- |
| 1  | Inicio rápido                     |
| 2  | Ciclo de despacho                 |
| 3  | `DLServer` y contexto             |
| 4  | Registro de rutas y controladores |
| 5  | Parámetros dinámicos              |
| 6  | `filter_by_type()`                |
| 7  | `match()` y `RouteHandler`        |
| 8  | `DLOutput` y respuestas           |
| 9  | Controladores y peticiones        |
| 10 | Query string y autómata           |
| 11 | `Router` y telemetría             |
| 12 | Subida de archivos                |
| 13 | Peticiones salientes              |
| 14 | Errores y diagnósticos            |
| 15 | Despliegue en producción          |
| 16 | Integración con DLCore            |

Consulta la documentación completa en:

```text
docs/tutorial/README.md
```

Referencia por módulo:

```text
docs/README.md
```

---

## Parte del ecosistema DLUnire

DLRoute es el motor de enrutamiento del ecosistema **DLUnire**.

Está diseñado para trabajar junto con otros componentes de la infraestructura, incluyendo:

* DLCore
* DLStorage
* DL Typed Environment
* componentes de request/response
* infraestructura de autenticación
* futuras capas del ecosistema DLUnire

El objetivo del ecosistema no es únicamente proporcionar componentes independientes, sino construir una infraestructura coherente donde diferentes capas puedan compartir conceptos, tipos y representación semántica.

---

## Licencia

DLRoute se distribuye bajo la licencia:

**GNU Affero General Public License v3.0 or later (AGPL-3.0-or-later)**.

Consulta el archivo `LICENSE` incluido en el repositorio para conocer los términos completos de la licencia.
