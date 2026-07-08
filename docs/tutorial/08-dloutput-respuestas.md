# 08 — `DLOutput` y respuestas HTTP

Tras ejecutar el controlador, `DLRoute\Requests\DLOutput` decide cabeceras y cuerpo según el tipo de retorno y el MIME registrado en la ruta.

## Contrato de respuesta

| Retorno del controlador | Comportamiento típico |
|-------------------------|----------------------|
| `array` / `object` | JSON + `Content-Type: application/json` |
| `string` | Cuerpo literal; MIME de la ruta o `text/html` |
| `null` / vacío | Respuesta mínima según contexto |

```php
DLRoute::get('/api/status', fn () => ['ok' => true]);
// → {"ok":true}

DLRoute::get('/page', fn () => '<h1>Hola</h1>', mime_type: 'text/html; charset=utf-8');
// → <h1>Hola</h1>
```

El cuarto argumento de `get()`/`post()` o `mime_type` en `RouteHandler` fija el Content-Type para strings.

## JSON manual desde el controlador

`Controller` expone `get_json()`:

```php
final class ReportController extends Controller {

    public function index(): string {
        return $this->get_json(['rows' => 100], pretty: true);
    }
}
```

Delega en `DLOutput::get_json()` — útil cuando necesitas controlar el pretty-print sin cambiar el flujo de `execute()`.

## Códigos de estado HTTP

Desde el controlador:

```php
public function not_found(): array {
    http_response_code(404);
    return ['error' => 'Recurso no encontrado'];
}

public function created(): array {
    http_response_code(201);
    return ['id' => 42];
}
```

DLRoute no impone un código por defecto distinto de 200 salvo 404 del router o validación de `DLRequest`.

## 404 del enrutador

Cuando ninguna ruta coincide o `filter_by_type()` falla, DLRoute responde 404. Puedes personalizar el mensaje:

```php
use DLRoute\Requests\DLOutput;

DLOutput::set_error_404([
    'error'   => 'Ruta no encontrada',
    'hint'    => 'Consulta /api/docs',
]);
```

Consulta `DLOutput` en el código fuente para las APIs exactas de personalización disponibles en tu versión.

## HEAD y cuerpo vacío

`DLRoute::head()` ejecuta el handler pero las respuestas HEAD suelen omitir cuerpo según el contrato HTTP. Úsalo para probes y comprobaciones de existencia sin transferir payload.

## Descargas y binarios

Para archivos no JSON, devuelve `string` con MIME adecuado:

```php
DLRoute::get(
    '/export.csv',
    [ExportController::class, 'csv'],
    [],
    'text/csv; charset=utf-8'
);

public function csv(): string {
    return "id,nombre\n1,Producto A\n";
}
```

## Errores estructurados en desarrollo

`RouteException` y errores de registro devuelven JSON con `status`, `error` y `details` (archivo, línea). En producción, evita exponer rutas internas en respuestas personalizadas ([14-errores-diagnosticos.md](14-errores-diagnosticos.md)).

## Flujo resumido

```
Controlador retorna $result
    └── DLOutput::print_response_data($mime_type)
            ├── ¿array/object? → json_encode + application/json
            ├── ¿string? → echo + MIME de ruta
            └── cabeceras HTTP
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| HTML en API JSON | Retorno `string` accidental | Devuelve `array` |
| JSON sin charset | MIME por defecto | Fija `application/json; charset=utf-8` si lo necesitas |
| Cliente ve 200 con error | No llamaste `http_response_code()` | Establece el código antes del return |
| 404 genérico | Ruta o filtro incorrecto | Revisa `get_routes()` y `filter_by_type` |

## Siguiente paso

`Controller`, `DLRequest` y validación de peticiones en [09-controladores-peticiones.md](09-controladores-peticiones.md).