# `QueryParamComposer`

**Namespace:** `DLRoute\Core\Routing\Automaton\QueryParams`  
**Tipo:** `final class`  
**Extiende:** `QueryStringLexer`  
**Disponible desde:** `v1.0.9`  
**Actualizado en:** `v1.0.11`

---

## Descripción

`QueryParamComposer` transforma los tokens léxicos del querystring en pares estructurados `nombre → valor` listos para ser consumidos por el desarrollador o por el sistema de telemetría.

Extiende `QueryStringLexer`, que realiza el análisis léxico byte a byte en una sola pasada. `QueryParamComposer` consume esos tokens y construye instancias inmutables de `QueryParamValue`, indexadas por nombre de parámetro para acceso directo en O(1).

No necesitas instanciar `QueryParamComposer` directamente — DLRoute lo hace internamente y expone el resultado a través de la telemetría. Sin embargo, puedes usarlo de forma autónoma si necesitas acceder al querystring fuera del ciclo de vida de una ruta.

---

## Uso autónomo

```php
use DLRoute\Core\Routing\Automaton\QueryParams\QueryParamComposer;

// El compositor lee $_SERVER['QUERY_STRING'] automáticamente
$composer = new QueryParamComposer();

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

Cada par `nombre → valor` es una instancia inmutable de `QueryParamValue`:

| Propiedad | Tipo | Descripción |
|---|---|---|
| `$name` | `string` | Nombre del parámetro normalizado |
| `$offset` | `int` | Posición inicial en bytes del nombre en el querystring |
| `$value` | `?string` | Valor del parámetro, o `null` si no fue asignado |
| `$offset_value` | `int` | Posición inicial en bytes del valor. Vale `0` si `$value` es `null` |
| `$length` | `int` | Longitud en bytes del valor. Vale `0` si `$value` es `null` |

### Ejemplo con metadatos de posición

```php
// URL: /?ciencia=valor

$param = $params['ciencia'];

echo $param->name;         // "ciencia"
echo $param->offset;       // posición inicial del nombre en el querystring
echo $param->value;        // "valor"
echo $param->offset_value; // posición inicial del valor en el querystring
echo $param->length;       // 5

// Localizar el nombre en la cadena original
$query_string = $_SERVER['QUERY_STRING'];
$name = substr($query_string, $param->offset, strlen($param->name));
// → "ciencia"
```

---

## Normalización de nombres

Los nombres de parámetros son normalizados automáticamente antes de ser indexados:

- Los espacios al inicio y al final son eliminados con `trim()`
- Los espacios internos son reemplazados por `_`

```php
// URL: /?nombre con espacios=David

$params = (new QueryParamComposer())->get_query_params();

// La clave es el nombre normalizado
$params['nombre_con_espacios']->value;  // "David"
$params['nombre_con_espacios']->name;   // "nombre_con_espacios"
```

El `$offset` refleja la posición del primer byte real del nombre — después de los espacios iniciales, si los hubiera.

---

## Reglas de composición

| Situación | Resultado |
|---|---|
| `?campo=valor` | `{ name: "campo", value: "valor" }` |
| `?campo` | `{ name: "campo", value: null, length: 0 }` |
| `?campo=` | `{ name: "campo", value: null, length: 0 }` |
| `?=valor` | Descartado — valor huérfano sin nombre |
| `&&&&` | Descartado — separadores vacíos |
| `?campo=a=b=c` | `{ name: "campo", value: "a=b=c" }` — todo después del primer `=` es valor |
| `?campo&=huerfano` | `{ name: "campo", value: null }` — el huérfano no contamina al anterior |
| `?campo=valor&campo=otro` | `{ name: "campo", value: "otro" }` — last-write-wins |

---

## Telemetría integrada

`QueryParamComposer` se ejecuta automáticamente dentro de `TelemetryRequest::telemetry()`. El resultado aparece en `query_param` como objeto asociativo indexado:

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
    // parámetro ausente o sin valor
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

---

## Notas de versión

- **v1.0.9** — Introducción del `QueryStringLexer` y `QueryParamComposer`.
- **v1.0.10** — Corrección del bug de absorción de valores huérfanos adyacentes. Un `QUERY_VALUE` en un bloque distinto ya no contamina el `QUERY_NAME` del bloque anterior.
- **v1.0.11** — Propiedades `$offset` y `$offset_value` en `QueryParamValue`. Exclusión del delimitador `?` antes del análisis léxico. `query_param` en telemetría migra a objeto asociativo indexado por nombre.