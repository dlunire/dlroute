# 03 — `DLServer` y contexto de ejecución

`DLRoute\Server\DLServer` abstrae `$_SERVER` y calcula la ruta relativa de la aplicación, el host, el puerto y la IP del cliente. Es la base sobre la que el enrutador decide qué handler ejecutar.

## Propiedades principales

| Método | Devuelve | Uso |
|--------|----------|-----|
| `get_uri()` | URI completa sin barras extremas | `/subdir/api/products?page=1` |
| `get_route()` | Ruta relativa a la app | `/api/products` |
| `get_method()` | Verbo HTTP | `GET`, `POST`, … |
| `get_document_root()` | Raíz del proyecto | vía `DLRealPath` |
| `get_base_url()` | URL base con esquema | `https://example.com/subdir` |
| `get_ipaddress()` | IP del cliente | Detrás de proxy si está configurado |
| `get_user_agent()` | User-Agent | Auditoría, validación de sesión |
| `get_hostname()` | Host sin puerto | Cookies, CORS |
| `get_http_host()` | Host con puerto | URLs absolutas |
| `get_port()` / `get_local_port()` | Puerto cliente / interno | Telemetría, proxies |

## Detección de subdirectorios

DLRoute calcula la ruta de la aplicación con **aritmética de posición de bytes**, sin `str_replace()` ni regex:

```
OFFSET = LENGTH(dir) - 1
route  = substr(uri, OFFSET)
```

Ejemplo con la app en `/subdir/subdir`:

| Campo | Valor |
|-------|-------|
| `uri` | `/subdir/subdir/api/products` |
| `dir` | `/subdir/subdir` |
| `route` | `/api/products` |
| `base_url` | `https://example.com/subdir/subdir` |

No necesitas configurar `APP_URL` ni prefijos manuales para que las rutas coincidan en subdirectorios.

## `DLRealPath` — document root

`DLServer::get_document_root()` delega en `DLRoute\Config\DLRealPath`:

1. Si existe la constante `DOCUMENT_ROOT`, la usa.
2. Si no, sube un nivel desde `getcwd()` (típico cuando `index.php` está en `public/`).

```php
// public/index.php — opcional, fija la raíz explícitamente
define('DOCUMENT_ROOT', dirname(__DIR__));
```

Detalle en [15-despliegue-produccion.md](15-despliegue-produccion.md).

## Helpers de método HTTP

```php
DLServer::is_get();      // REQUEST_METHOD === 'GET'
DLServer::is_post();
DLServer::is_put();
DLServer::is_patch();
DLServer::is_delete();
DLServer::is_head();
DLServer::is_options();
```

`DLRoute::get()` solo despacha si `is_get()` es verdadero; lo mismo para cada verbo.

## HTTPS y esquema

`DLRoute\Server\DLHost::is_https()` consulta cabeceras de proxy (`X-Forwarded-Proto`, etc.) además de `HTTPS` en `$_SERVER`. Útil detrás de Nginx o Cloudflare ([15-despliegue-produccion.md](15-despliegue-produccion.md)).

## IP detrás de proxy

`DLServer::get_ipaddress()` considera `X-Forwarded-For`, `X-Real-IP` y `REMOTE_ADDR` según la configuración del trait `IPAddress`. La telemetría expone `cliente_ip` y si hay proxy activo ([11-router-telemetria.md](11-router-telemetria.md)).

## Uso en controladores

`DLRoute\Config\Controller` expone atajos:

```php
final class AuditController extends Controller {

    public function log(): array {
        return [
            'ip'   => $this->get_ip(),
            'host' => $this->get_http_host(),
        ];
    }
}
```

## Ejemplo de depuración

```php
DLRoute::get('/debug/context', function () {
    return [
        'uri'    => DLRoute\Server\DLServer::get_uri(),
        'route'  => DLRoute\Server\DLServer::get_route(),
        'method' => DLRoute\Server\DLServer::get_method(),
        'root'   => DLRoute\Server\DLServer::get_document_root(),
        'base'   => DLRoute\Server\DLServer::get_base_url(),
    ];
});
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Rutas no coinciden en subdirectorio | Servidor mal configurado | Apunta el vhost a `public/`; revisa `uri` vs `route` en `/debug/context` |
| `document_root` incorrecto | `getcwd()` distinto al esperado | Define `DOCUMENT_ROOT` en `index.php` |
| IP siempre del proxy | Sin cabeceras reales | Configura `X-Forwarded-For` en Nginx/Apache |
| Cookies con dominio erróneo | `get_hostname()` no coincide | Revisa `HTTP_HOST` y TLS |

## Siguiente paso

Tres formas de definir controladores y datos inyectados en [04-registro-controladores.md](04-registro-controladores.md).