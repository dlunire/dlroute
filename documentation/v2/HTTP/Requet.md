# Request HTTP — DLRoute

## Introducción

La clase `DLRoute\Http\Request` es un componente de bajo nivel encargado de interpretar el método HTTP de la petición actual y exponerlo de forma estructurada mediante el enum `DLRoute\Enums\Methods`.

Su objetivo es eliminar el acceso directo a `$_SERVER['REQUEST_METHOD']` y centralizar la lógica de detección del método HTTP, incluyendo comportamiento seguro en entornos CLI y pruebas automatizadas.

---

## Responsabilidad de la clase

Esta clase tiene una única responsabilidad clara:

> Determinar y exponer el método HTTP actual de la petición.

Además, proporciona utilidades derivadas basadas en la semántica del protocolo HTTP:

- detección del método
- validaciones tipo `is_*`
- clasificación semántica (`safe`, `idempotent`, `cacheable`)
- detección de contexto (`CLI`, `Ajax`)

---

## Obtención del método HTTP

### Método principal

```php
use DLRoute\Http\Request;
use DLRoute\Enums\Methods;

$method = Request::get_method();
```

Esto devuelve una instancia del enum:

```php
Methods::GET
Methods::POST
Methods::PUT
...
```

### Alternativa textual

```php
$method_name = Request::get_method_name();
```

Resultado:

```text
"GET"
"POST"
"PATCH"
```

---

## Validación de métodos HTTP

La clase expone métodos explícitos para evitar comparaciones manuales:

### Ejemplos

```php
if (Request::is_get()) {
    // lógica GET
}

if (Request::is_post()) {
    // lógica POST
}

if (Request::is_put()) {
    // lógica PUT
}
```

### Métodos disponibles

* `is_get()`
* `is_head()`
* `is_post()`
* `is_put()`
* `is_patch()`
* `is_delete()`
* `is_options()`

---

## Clasificación semántica HTTP

Además de identificar el método, la clase permite interpretar su comportamiento dentro del protocolo HTTP.

---

### Métodos seguros (Safe)

```php
Request::is_safe();
```

Se consideran seguros los métodos que no modifican el estado del servidor:

* GET
* HEAD
* OPTIONS

Uso típico:

* lectura de recursos
* navegación
* consultas

---

### Métodos idempotentes

```php
Request::is_idempotent();
```

Un método es idempotente si ejecutar la misma petición múltiples veces produce el mismo estado final.

Incluye:

* GET
* HEAD
* PUT
* DELETE
* OPTIONS

Ejemplo conceptual:

```text
DELETE /users/5
DELETE /users/5
```

El resultado final es el mismo.

---

### Métodos cacheables

```php
Request::is_cacheable();
```

Generalmente:

* GET
* HEAD

Estos métodos pueden ser almacenados en caché por clientes, proxies o CDNs.

---

## Detección de contexto

### CLI

```php
Request::is_cli();
```

Retorna `true` cuando la ejecución ocurre fuera del servidor HTTP.

Uso típico:

* pruebas unitarias
* scripts de consola
* workers

---

### AJAX

```php
Request::is_ajax();
```

Detecta solicitudes realizadas con:

```http
X-Requested-With: XMLHttpRequest
```

Uso típico:

* endpoints JSON
* llamadas asincrónicas frontend

---

## Cómo se resuelve el método internamente

La clase implementa un mecanismo de caché por petición:

1. Se lee `$_SERVER['REQUEST_METHOD']`
2. Se normaliza a mayúsculas
3. Se convierte a `Methods::tryFrom()`
4. Si falla, se usa `Methods::GET`
5. Se almacena en memoria estática

Esto evita lecturas repetidas del entorno global.

---

## Comportamiento en CLI

En CLI:

```php
PHP_SAPI === 'cli'
```

El método HTTP se fuerza a:

```php
Methods::GET
```

Esto permite:

* ejecutar controladores en tests
* evitar dependencias de servidor HTTP

---

## Cuándo usar esta clase

### Usar `Request` cuando:

* estás dentro de controladores del framework
* necesitas lógica basada en HTTP verbs
* quieres evitar acceso directo a `$_SERVER`
* quieres mantener compatibilidad con CLI/testing
* necesitas reglas semánticas (safe, idempotent, cacheable)

---

### No usar directamente cuando:

* estás construyendo infraestructura de bajo nivel del router (antes de inicializar HTTP)
* estás en capas donde aún no existe contexto de request
* necesitas acceso crudo a headers específicos no abstraídos

---

## Ejemplo completo

```php
use DLRoute\Http\Request;

if (Request::is_post()) {
    // procesamiento de datos
}

if (Request::is_ajax()) {
    return json_encode(['status' => 'ok']);
}

if (Request::is_idempotent()) {
    // lógica segura para reintentos
}
```

---

## Conclusión

`Request` actúa como una abstracción estable sobre el método HTTP y su semántica, reduciendo dependencia del entorno global y promoviendo decisiones basadas en el protocolo HTTP en lugar de comparaciones manuales de strings.
