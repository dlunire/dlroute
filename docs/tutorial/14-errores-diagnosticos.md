# 14 — Errores y diagnósticos

DLRoute prioriza **errores accionables**: posición exacta en la sintaxis de rutas, JSON estructurado en fallos de registro y excepciones tipadas en lugar de 404 HTML silenciosos.

## `RouteException`

**Namespace:** `DLRoute\Errors\RouteException`

Se lanza cuando:

- La sintaxis de una URI de ruta es inválida
- `Router::to()` recibe una ruta no normalizable
- Contratos de `match()` se violan (tipos incorrectos)

### Ejemplo — parámetro opcional mal formado

```php
DLRoute::get('/{ciencia?=algo}/users', fn () => []);
```

Salida:

```
RouteException: Expected closing brace (}) after «?» (position 9).
Received instead: «?=algo}/users».
Optional parameters must follow the format → «{param?}»
Route defined: «/{ciencia?=algo}/users»
```

Corrige a `{ciencia?}` sin valor por defecto en la plantilla.

## Errores JSON en registro

Entrada inválida en `match()`:

```json
{
    "status": false,
    "error": "DLRoute::match: Expected «DLRoute\\Enums\\Methods». Received «david» instead.",
    "details": {
        "filename": "/path/routes/api.php",
        "line": 42
    }
}
```

Compara con frameworks que devuelven 404 HTML sin indicar el archivo de rutas.

## 404 de enrutamiento

Causas habituales:

| Causa | Comportamiento |
|-------|----------------|
| URI no coincide con ninguna ruta | 404 |
| Método HTTP no registrado para la URI | 404 |
| `filter_by_type()` falla | 404 (controlador no ejecutado) |

Personalización vía `DLOutput::set_error_404()` ([08-dloutput-respuestas.md](08-dloutput-respuestas.md)).

## `OutputException`

Errores al serializar la respuesta (JSON inválido, MIME inconsistente). Captura en desarrollo y registra el mensaje; en producción devuelve respuesta genérica según tu política.

## Depuración con `get_routes()`

```php
// Solo en desarrollo — expone mapa interno
$map = DLRoute::get_routes();
error_log(json_encode($map, JSON_PRETTY_PRINT));
```

Verifica que el verbo y la URI esperados existan tras `RouteGenerator` expanda opcionales.

## Endpoint de diagnóstico (desarrollo)

```php
if (getenv('APP_DEBUG') === '1') {
    DLRoute::get('/__debug/routes', fn () => DLRoute::get_routes());
    DLRoute::get('/__debug/context', fn () => [
        'route'  => DLRoute\Server\DLServer::get_route(),
        'method' => DLRoute\Server\DLServer::get_method(),
        'uri'    => DLRoute\Server\DLServer::get_uri(),
    ]);
}
```

**No** expongas estos endpoints en producción.

## Telemetría para incidentes

```php
return TelemetryRequest::telemetry('incident');
```

Incluye IP, proxy, puertos, query parseada y timestamp — útil en tickets de soporte ([11-router-telemetria.md](11-router-telemetria.md)).

## Checklist de diagnóstico

1. ¿`DLRoute::execute()` se llama al final del bootstrap?
2. ¿El método HTTP de la petición coincide con el registrado?
3. ¿`filter_by_type` rechaza el segmento? Prueba sin filtro.
4. ¿La sintaxis `{param?}` es válida? Lee el mensaje del lexer.
5. ¿Subdirectorio? Compara `get_uri()` vs `get_route()` ([03-dlserver-contexto.md](03-dlserver-contexto.md)).

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Excepción al cargar rutas | Sintaxis URI inválida | Corrige posición indicada |
| JSON de error en pantalla | Entrada inválida a `match()` | Usa `Methods::` enum |
| 404 intermitente | Variante opcional distinta | Inspecciona `get_routes()` |
| Stack trace en producción | `display_errors` activo | Desactiva en php.ini |

## Siguiente paso

Apache, Nginx, proxies y subdirectorios en [15-despliegue-produccion.md](15-despliegue-produccion.md).