# 11 — `Router` y telemetría

DLRoute incluye dos herramientas de contexto HTTP: **`Router`** (URLs absolutas y telemetría estructurada) y **`TelemetryRequest`** (snapshot de la petición actual en un solo llamado).

## Clase `Router`

**Namespace:** `DLRoute\Core\Routing\Router`

> `Router` **no** registra rutas ni valida permisos. Solo construye URLs y lee el estado de la petición.

### `Router::to()` — URLs absolutas

```php
use DLRoute\Core\Routing\Router;

$url = Router::to('/api/products/42');
// https://tu-dominio.com/subdirectorio/api/products/42

$base = Router::to('/');
// https://tu-dominio.com/subdirectorio
```

Normaliza barras y concatena con `DLServer::get_base_url()`. Lanza `RouteException` si la ruta no es válida tras normalizar.

### `Router::from()` — telemetría de la petición actual

```php
$data = Router::from();

$data->url;        // URL completa
$data->method;     // GET, POST, …
$data->route;      // ruta relativa de la app
$data->uri;        // URI con directorio de ejecución
$data->ip_client;  // IP del cliente
$data->scheme;     // http | https
$data->host;
$data->port;       // puerto visto por el cliente
$data->local_port;// puerto interno del servidor
$data->dir;        // subdirectorio de despliegue
$data->time;       // marca temporal
```

Útil para logs de auditoría sin ensamblar `$_SERVER` a mano.

## `TelemetryRequest`

**Namespace:** `DLRoute\Core\Telemetry\TelemetryRequest`

```php
use DLRoute\Core\Telemetry\TelemetryRequest;

DLRoute::get('/api/status', function () {
    return TelemetryRequest::telemetry('Health check');
});
```

Respuesta típica (campos según versión):

```json
{
    "message":     "Health check",
    "route":       "/api/status",
    "uri":         "/subdir/api/status?verbose=1",
    "base_url":    "https://mi-dominio.com/subdir",
    "domain":      "mi-dominio.com",
    "is_https":    true,
    "port":        443,
    "local_port":  8080,
    "timestamp":   "2026-07-08T12:00:00+00:00",
    "cliente_ip":  "203.0.113.1",
    "method":      "GET",
    "proxy":       true,
    "query_param": { }
}
```

Una llamada. Cero configuración de middleware. Diferencia `port` (cliente) y `local_port` (servidor) detrás de proxies.

## Comparativa

| Necesidad | Usa |
|-----------|-----|
| Enlace en plantilla o email | `Router::to()` |
| Log estructurado en controlador | `Router::from()` |
| Debug rápido en API | `TelemetryRequest::telemetry()` |
| Offsets de querystring | `query_param` dentro de telemetría ([10-querystring-automata.md](10-querystring-automata.md)) |

## Ejemplo en controlador

```php
final class AuditController extends Controller {

    public function record(): array {
        $ctx = \DLRoute\Core\Routing\Router::from();

        return [
            'logged' => true,
            'ip'     => $ctx->ip_client,
            'path'   => $ctx->route,
        ];
    }
}
```

En DLUnire, el helper `route()` del skeleton es equivalente conceptual a `Router::to()` ([tutorial DLCore — helpers](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/21-helpers-skeleton.md)).

## Buenas prácticas

1. Usa `Router::to()` en lugar de hardcodear dominios.
2. No confundas `Router` con `DLRoute` — el primero no despacha.
3. En producción, filtra qué campos de telemetría expones al cliente final.
4. Combina telemetría con logs en disco solo en desarrollo.

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| URL con doble barra | Ruta mal formada | Pasa rutas relativas limpias a `to()` |
| `port` incorrecto detrás de TLS | Proxy sin cabeceras | Configura `X-Forwarded-Proto` ([15-despliegue-produccion.md](15-despliegue-produccion.md)) |
| Telemetría vacía en CLI | Sin `$_SERVER` HTTP | Simula entorno o usa composer directo |

## Siguiente paso

Subida de archivos y saneamiento SVG en [12-subida-archivos.md](12-subida-archivos.md).