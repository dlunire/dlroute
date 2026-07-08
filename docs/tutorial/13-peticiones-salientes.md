# 13 — Peticiones HTTP salientes

El trait `DLRoute\Traits\Request` (incluido en `Controller`) abstrae **cURL** para llamar APIs externas desde controladores o servicios: GET, POST, PUT, PATCH, DELETE, JSON, form-urlencoded y multipart.

## Clases auxiliares

| Clase | Uso |
|-------|-----|
| `RequestInit` | Método, cuerpo, cabeceras de la petición |
| `HeadersInit` | Cabeceras HTTP individuales |
| `DLRoute\Http\Request` | Constantes de métodos (`GET`, `POST`, …) |

Documentación clásica: [Request-ES.md](../../documentation/Request/Request-ES.md)

## Patrón con `fetch()`

```php
<?php
namespace App\Controllers;

use DLRoute\Config\Controller;
use DLRoute\Http\Request;
use DLRoute\Requests\HeadersInit;
use DLRoute\Requests\RequestInit;

final class ProxyController extends Controller {

    public function forward(): array|false {
        $request = new RequestInit();
        $headers = new HeadersInit();

        $headers->set('Accept', 'application/json');
        $headers->set('Authorization', 'Bearer ' . $this->get_token());

        $request->set_headers($headers);
        $request->set_method(Request::GET);

        $response = $this->fetch('https://api.example.com/data', $request);

        if ($response === false) {
            http_response_code(502);
            return ['error' => 'Upstream no disponible'];
        }

        return json_decode($response, true) ?? ['raw' => $response];
    }

    private function get_token(): string {
        return 'secreto';
    }
}
```

## Cuerpo JSON

```php
$request = new RequestInit();
$request->set_method(Request::POST);
$request->set_body(['name' => 'DLRoute', 'version' => '2.0']);

$headers = new HeadersInit();
$headers->set('Content-Type', 'application/json');
$request->set_headers($headers);

$response = $this->fetch('https://api.example.com/items', $request);
```

## Capacidades del trait `Request`

- Métodos: GET, POST, PUT, PATCH, DELETE
- `application/x-www-form-urlencoded`, `application/json`, `multipart/form-data` (con `CURLFile`)
- Cookies persistentes en archivo Netscape (`dlunire-cookie.txt`)
- Redirecciones, timeouts y SSL configurables

## Servicio dedicado (recomendado)

En proyectos medianos, encapsula el trait en una clase de servicio:

```php
<?php
namespace App\Services;

use DLRoute\Traits\Request;

final class PaymentGateway {
    use Request;

    public function charge(array $payload): string|false {
        // $this->request(...) o $this->fetch(...)
    }
}
```

Así los controladores permanecen delgados y el cliente HTTP es testeable.

## SSL y producción

Por defecto el trait puede tener verificación SSL relajada en desarrollo. En producción:

- Habilita verificación de certificados
- Fija timeouts razonables (`connect_timeout`, `timeout`)
- No desactives `verify_peer` salvo entornos controlados

Revisa las propiedades privadas del trait y expón configuración vía métodos públicos en tu servicio.

## Comparativa con peticiones entrantes

| Dirección | Componente | Capítulo |
|-----------|------------|----------|
| Entrante (browser → tu app) | `DLRequest`, `DLRoute::execute()` | [09-controladores-peticiones.md](09-controladores-peticiones.md) |
| Saliente (tu app → API externa) | Trait `Request`, `RequestInit` | Este capítulo |

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| `fetch()` devuelve `false` | Timeout, DNS, SSL | Log de cURL, revisa URL y certificados |
| JSON malformado en respuesta | Upstream devuelve HTML de error | Comprueba código HTTP del origen |
| Authorization no llega | Cabecera no configurada | `HeadersInit::set('Authorization', …)` |
| Cookie no persistida | Ruta de cookies no escribible | `set_cookies()` con path válido |

## Siguiente paso

`RouteException`, lexer y JSON de errores en [14-errores-diagnosticos.md](14-errores-diagnosticos.md).