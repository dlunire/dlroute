# 10 — Query string y autómata finito

DLRoute no usa `parse_str()` para analizar la querystring. Un **autómata finito** (`QueryStringLexer`) procesa la cadena byte a byte y `QueryParamComposer` construye DTOs `QueryParamValue` indexados por nombre.

## Por qué importa

| `parse_str()` | Autómata DLRoute |
|---------------|------------------|
| Sin offsets | `offset`, `offset_value`, `length` por parámetro |
| Comportamiento opaco | Estados explícitos `QUERY_NAME` → `QUERY_VALUE` |
| Difícil depurar | Integrado en telemetría nativa |

## Uso directo

```php
use DLRoute\Core\Routing\Automaton\QueryParams\QueryParamComposer;

$params = (new QueryParamComposer())->get_query_params();

$params['campo']->value;         // "valor"
$params['campo']->offset;        // posición del nombre en bytes
$params['campo']->offset_value;  // posición del valor
$params['campo']->length;        // longitud del valor

$params['activo']->value;        // null — flag sin valor
```

## Cadena personalizada

```php
$composer = new QueryParamComposer('filtro=activo&pagina=2');
$params = $composer->get_query_params();
```

Útil en tests o scripts fuera del ciclo HTTP.

## Normalización de claves

El compositor sustituye espacios inválidos en nombres de parámetro por `_` antes de indexar. Acceso O(1) por nombre normalizado.

## Parámetros sin valor

```
GET /?activo&campo=valor
```

- `activo` → `value: null`
- `campo` → `value: "valor"`

Comportamiento alineado con flags booleanos en querystrings.

## Parámetros duplicados

Si la misma clave aparece varias veces, **prevalece el último valor** — consistente con `$_GET` de PHP.

## Integración con telemetría

`TelemetryRequest` incluye `query_param` como objeto asociativo:

```php
use DLRoute\Core\Telemetry\TelemetryRequest;

DLRoute::get('/api/{resource?}', function () {
    return TelemetryRequest::telemetry('Mi API');
});
```

Fragmento de respuesta:

```json
{
    "message": "Mi API",
    "route": "/api/users",
    "uri": "/api/users?filter=active",
    "query_param": {
        "filter": {
            "name": "filter",
            "value": "active",
            "offset": 0,
            "offset_value": 7,
            "length": 6
        }
    }
}
```

Detalle en [11-router-telemetria.md](11-router-telemetria.md).

## Cuándo usar `QueryParamComposer` vs `$_GET`

| Escenario | Recomendación |
|-----------|---------------|
| Lógica de negocio simple | `$_GET` o helpers DLCore |
| Auditoría, logs, offsets | `QueryParamComposer` |
| Telemetría / diagnóstico | `TelemetryRequest` |
| Tests unitarios del parser | Constructor con cadena explícita |

## Referencia técnica

| Clase | Rol |
|-------|-----|
| `QueryStringLexer` | Tokenización byte a byte |
| `QueryStringTokenType` | Tipos de token (`QUERY_NAME`, `QUERY_VALUE`, …) |
| `QueryParamValue` | DTO inmutable por parámetro |
| `QueryParamComposer` | Mapa nombre → `QueryParamValue` |

Documentación: [QueryParamComposer.md](../../documentation/Documentation/QueryParamComposer.md)

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Clave no encontrada | Nombre normalizado distinto | Revisa sustitución de espacios |
| Offset distinto al esperado | `?` excluido del cálculo | Offsets relativos a cadena sin `?` |
| Parámetro "perdido" | Duplicado en query | Prevalece el último |

## Siguiente paso

`Router::to()`, `Router::from()` y `TelemetryRequest` en [11-router-telemetria.md](11-router-telemetria.md).