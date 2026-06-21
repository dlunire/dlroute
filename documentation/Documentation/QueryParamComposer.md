# `QueryParamComposer`

**Namespace:** `DLRoute\Core\Routing\Automaton\QueryParams`  
**Tipo:** `final class`  
**Extiende:** `QueryStringLexer`  
**Disponible desde:** `v1.0.9`

---

## Descripción

`QueryParamComposer` transforma los tokens léxicos del querystring en pares estructurados `nombre → valor` listos para ser consumidos por el desarrollador o por el sistema de telemetría.

Extiende `QueryStringLexer`, que realiza el análisis léxico byte a byte en una sola pasada. `QueryParamComposer` consume esos tokens y construye instancias de `QueryParamValue` indexadas por nombre normalizado para acceso directo en O(1).

No necesitas instanciar `QueryParamComposer` directamente — DLRoute lo hace internamente y expone el resultado a través de la telemetría. Sin embargo, puedes usarlo de forma autónoma si necesitas acceder al querystring fuera del ciclo de vida de una ruta, o suministrándole directamente una cadena de querystring.

---

## Uso autónomo

```php
use DLRoute\Core\Routing\Automaton\QueryParams\QueryParamComposer;

// Lee $_SERVER['QUERY_STRING'] automáticamente
$composer = new QueryParamComposer();

// O con una cadena suministrada externamente
$composer = new QueryParamComposer('nombre=David&rol=admin&activo');

/** @var \DLRoute\Core\Data\QueryParamValue[] $params */
$params = $composer->get_query_params();
```

---

## Acceso a los parámetros

`get_query_params()` devuelve un array asociativo indexado por el nombre normalizado del parámetro:

```php
// URL: /?nombre=David&rol=admin&activo
$params = (new QueryParamComposer())->get_query_params();

// Acceso directo O(1) por nombre
$nombre = $params['nombre']->value;  // "David"
$rol    = $params['rol']->value;     // "admin"
$activo = $params['activo']->value;  // null — sin valor asignado
```

---

## Estructura de `QueryParamValue`

Cada par `nombre → valor` es una instancia de `QueryParamValue`:

| Propiedad      | Tipo      | Descripción                                                              |
| -------------- | --------- | ------------------------------------------------------------------------ |
| `$name`        | `string`  | Nombre del parámetro normalizado                                         |
| `$offset`      | `int`     | Posición del primer byte real del nombre en el querystring, tras el trim |
| `$value`       | `?string` | Valor del parámetro, o `null` si no fue asignado o estaba vacío          |
| `$offset_value`| `int`     | Posición inicial en bytes del valor. Vale `0` si `$value` es `null`      |
| `$length`      | `int`     | Longitud en bytes del valor. Vale `0` si `$value` es `null`              |

### Ejemplo con metadatos de posición

```php
// URL: /?ciencia=valor

$param = $params['ciencia'];

echo $param->name;          // "ciencia"
echo $param->offset;        // posición del primer byte real del nombre
echo $param->value;         // "valor"
echo $param->offset_value;  // posición inicial del valor en el querystring
echo $param->length;        // 5

// Localizar el nombre en la cadena original
$query_string = $_SERVER['QUERY_STRING'];
$name = substr($query_string, $param->offset, strlen($param->name));
// → "ciencia"
```

---

## Normalización de nombres

Los nombres de parámetros son normalizados automáticamente por `normalize_key()` antes de ser indexados. El proceso opera en dos fases:

1. **Saneamiento de bordes** — `trim()` elimina espacios al inicio y al final.
2. **Sustitución interna** — recorrido byte a byte que reemplaza cada espacio interno por `_`.

El ajuste de `$offset` refleja los bytes eliminados por `trim()`, de modo que siempre apunta al primer byte real del nombre en la cadena original.

```php
// URL: /?nombre con espacios=David

$params = (new QueryParamComposer())->get_query_params();

$params['nombre_con_espacios']->value;   // "David"
$params['nombre_con_espacios']->name;    // "nombre_con_espacios"
$params['nombre_con_espacios']->offset;  // posición del primer byte real, sin espacios iniciales
```

---

## Reglas de composición

| Situación | Resultado |
|---|---|
| `?campo=valor` | `{ name: "campo", value: "valor" }` |
| `?campo` | `{ name: "campo", value: null, length: 0 }` |
| `?campo=` | `{ name: "campo", value: null, length: 0 }` |
| `?=valor` | Descartado — valor huérfano sin nombre |
| `? campo=valor` | `{ name: "campo", value: "valor" }` — espacio inicial eliminado por trim |
| `?nombre con espacios=David` | `{ name: "nombre_con_espacios", value: "David" }` — espacios internos → `_` |
| `&&&&` | Descartado — separadores vacíos |
| `?campo=a=b=c` | `{ name: "campo", value: "a=b=c" }` — todo después del primer `=` es valor |
| `?campo&=huerfano` | `{ name: "campo", value: null }` — el huérfano no contamina al anterior |
| `?campo=valor&campo=otro` | `{ name: "campo", value: "otro" }` — last-write-wins |
| `?   =valor` | Descartado — nombre vacío tras trim |

---

## Telemetría integrada

`QueryParamComposer` se ejecuta automáticamente dentro de `TelemetryRequest::telemetry()`. El resultado aparece en `query_param` como objeto asociativo indexado por nombre normalizado:

```php
use DLRoute\Core\Telemetry\TelemetryRequest;

DLRoute::get('/', function() {
    return TelemetryRequest::telemetry("Mi aplicación");
});
```

```json
{
    "query_param": {
        "ciencia": {
            "name": "ciencia",
            "offset": 0,
            "value": "valor",
            "offset_value": 8,
            "length": 5
        },
        "activo": {
            "name": "activo",
            "offset": 14,
            "value": null,
            "offset_value": 0,
            "length": 0
        }
    }
}
```

---

## Casos de uso

### Leer un parámetro con valor garantizado

```php
$params = (new QueryParamComposer())->get_query_params();

$page = $params['page'] ?? null;

if ($page === null || $page->value === null) {
    $current_page = 1;
} else {
    $current_page = (int) $page->value;
}
```

### Verificar si un parámetro existe

```php
$params = (new QueryParamComposer())->get_query_params();

if (array_key_exists('debug', $params)) {
    // ?debug fue enviado, independientemente de si tiene valor
}
```

### Iterar sobre todos los parámetros

```php
$params = (new QueryParamComposer())->get_query_params();

foreach ($params as $name => $param) {
    echo "{$name}: " . ($param->value ?? '(sin valor)') . PHP_EOL;
}
```

### Uso con cadena externa (delegación desde `RouteLexer`)

```php
// Suministrar directamente el fragmento del querystring
$composer = new QueryParamComposer('filtro=activo&pagina=2');
$params   = $composer->get_query_params();

$params['filtro']->value;  // "activo"
$params['pagina']->value;  // "2"
```

---

## Notas de versión

- **v1.0.9** — Introducción de `QueryStringLexer` y `QueryParamComposer`.
- **v1.0.10** — Corrección del bug de absorción de valores huérfanos adyacentes. Un `QUERY_VALUE` en un bloque distinto ya no contamina el `QUERY_NAME` del bloque anterior.
- **v1.0.11** — Propiedades `$offset` y `$offset_value` en `QueryParamValue`. Exclusión del delimitador `?` antes del análisis léxico. `query_param` en telemetría migra a objeto asociativo indexado por nombre.
- **v1.0.12** — `normalize_key()` ajusta `$offset` con `$diff_lexeme_length` para reflejar la posición del primer byte real tras el trim. Soporte de cadena externa en el constructor.

---

## Véase también

- [`QueryStringLexer`](QueryStringLexer.md) — autómata léxico base
- [`QueryStringTokenType`](QueryStringTokenType.md) — enum de estados del autómata
- [`QueryParam`](QueryParam.md) — DTO de token emitido por el lexer
- [`QueryParamValue`](QueryParamValue.md) — DTO de par nombre → valor listo para el desarrollador