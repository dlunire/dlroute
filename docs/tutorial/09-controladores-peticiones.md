# 09 — Controladores y peticiones entrantes

`DLRoute\Config\Controller` es la base de todos los controladores. Combina lectura de peticiones (`DLRequest`), validación (`DLValidates`), subida de archivos (`DLUpload`) y peticiones salientes (`Request` trait).

## Anatomía del controlador base

```php
<?php
namespace DLRoute\Config;

abstract class Controller {
    use DLValidates, DLUpload, Request;

    protected DLRequest $request;

    public function __construct() {
        $this->request = DLRequest::get_instance();
    }
    // get_ip(), get_http_host(), get_json(), …
}
```

| Trait / clase | Responsabilidad |
|---------------|-----------------|
| `DLRequest` | Valida esquemas GET/POST/PUT/DELETE |
| `DLValidates` | `is_email()`, `is_integer()`, `is_password()`, … |
| `DLUpload` | `upload_file()`, thumbnails, SVG |
| `Request` | Cliente HTTP saliente (cURL) |

## `DLRequest` — validación por esquema

`DLRequest` compara la petición actual con un mapa de campos requeridos:

```php
final class WebhookController extends Controller {

    public function receive(): array {
        $valid = $this->request->post([
            'event'   => true,   // requerido
            'payload' => false,  // opcional
        ]);

        if (!$valid) {
            return ['error' => 'Petición inválida'];
        }

        return ['ok' => true];
    }
}
```

| Método | Actúa si el verbo es |
|--------|----------------------|
| `$this->request->get($params)` | GET |
| `$this->request->post($params)` | POST |
| `$this->request->put($params)` | PUT |
| `$this->request->delete($params)` | DELETE |

Si un campo marcado `true` llega vacío, `DLRequest` responde **400** y devuelve `false`.

## Fuentes de datos

`DLRequest` lee, en orden:

1. `$_POST` en POST
2. `$_GET` en GET
3. Cuerpo JSON en `php://input` si los anteriores están vacíos

```php
// POST application/json
// { "name": "DLRoute" }

$this->request->post(['name' => true]);
```

## `DLValidates` — reglas reutilizables

Métodos **protected** en el controlador:

```php
if (!$this->is_email($email)) {
    http_response_code(422);
    return ['error' => 'Email inválido'];
}

if (!$this->is_password($password)) {
    return ['error' => 'Contraseña débil'];
}
```

`is_password()` exige longitud mínima, mayúsculas y caracteres especiales del conjunto documentado en el trait.

## DLCore / DLUnire — lectura avanzada

`DLCore\Core\BaseController` extiende `Controller` y añade `get_required()`, `get_post()`, `get_password()`, validación CSRF, etc. ([16-integracion-dlcore.md](16-integracion-dlcore.md), [tutorial DLCore — controladores](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/04-controladores.md)).

Con DLRoute puro, lee `$_POST`/`php://input` manualmente o usa `DLRequest::post()`.

## Parámetros de URI vs cuerpo

| Origen | Acceso | Validación previa |
|--------|--------|-------------------|
| URI `{id}` | `$params->id` en el método | `filter_by_type()` |
| Query string | `$_GET` o `QueryParamComposer` | [10-querystring-automata.md](10-querystring-automata.md) |
| Cuerpo POST/JSON | `DLRequest` / `BaseController` | Esquema en controlador |

No mezcles validación de URI en el cuerpo: usa `filter_by_type()` para segmentos.

## Ejemplo API JSON

```php
final class ItemsController extends Controller {

    public function store(): array {
        if (!$this->request->post(['title' => true, 'qty' => true])) {
            return ['error' => 'Campos requeridos'];
        }

        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        return [
            'created' => true,
            'title'   => $body['title'] ?? null,
        ];
    }
}
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| `post()` siempre false en GET | Método HTTP incorrecto | Usa `get()` o cambia el verbo |
| JSON no leído | Content-Type o cuerpo vacío | Verifica `php://input` |
| 400 sin mensaje claro | Campo requerido vacío | Revisa mapa `true`/`false` |
| Validación duplicada | URI + cuerpo con mismo dato | Separa responsabilidades |

## Siguiente paso

Parser de querystring por autómata finito en [10-querystring-automata.md](10-querystring-automata.md).