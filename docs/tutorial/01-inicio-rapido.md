# 01 — Inicio rápido

DLRoute es el **motor de enrutamiento HTTP** de DLUnire. Resuelve el contexto de ejecución (document root, URI, método, IP), registra rutas con contratos tipados y despacha peticiones a controladores o callbacks. Funciona en cualquier proyecto PHP 8.2+ — con o sin framework.

## Relación con DLCore

| Capa | Paquete | Responsabilidad |
|------|---------|-----------------|
| Infraestructura | `dlunire/dlroute` | Rutas, peticiones HTTP, respuestas, subida de archivos, telemetría |
| Kernel | `dlunire/dlcore` | ORM, plantillas, autenticación, `.env.type` |

DLCore **consume** DLRoute; no lo reemplaza. Integración completa en [16-integracion-dlcore.md](16-integracion-dlcore.md).

## Instalación

```bash
composer require dlunire/dlroute
```

## Estructura mínima

```
mi-api/
├── public/
│   └── index.php       ← punto de entrada
├── app/
│   └── Controllers/
│       └── HealthController.php
├── routes/
│   └── web.php         ← opcional
└── vendor/
```

## Punto de entrada

```php
<?php
declare(strict_types=1);

use DLRoute\Requests\DLRoute;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/routes/web.php';

DLRoute::execute();
```

`DLRoute::execute()` **debe** ser la última llamada: despacha la petición actual contra todas las rutas registradas.

## Primera ruta

`routes/web.php`:

```php
<?php

use DLRoute\Requests\DLRoute;

DLRoute::get('/', fn () => ['status' => 'ok', 'service' => 'dlroute']);
```

Petición `GET /` → respuesta JSON automática:

```json
{"status":"ok","service":"dlroute"}
```

DLRoute serializa arrays y objetos con `Content-Type: application/json` sin código adicional.

## Primer controlador

```php
<?php
namespace App\Controllers;

use DLRoute\Config\Controller;

final class HealthController extends Controller {

    public function ping(): array {
        return [
            'pong'   => true,
            'ip'     => $this->get_ip(),
            'method' => 'GET',
        ];
    }
}
```

Registro:

```php
use App\Controllers\HealthController;
use DLRoute\Requests\DLRoute;

DLRoute::get('/health', [HealthController::class, 'ping']);
```

Convención DLUnire: clase `final` en `DLUnire\Controllers`, métodos en **snake_case** (`show_login`, `ping`).

## Qué hace DLRoute por ti

| Tarea | Sin DLRoute | Con DLRoute |
|-------|-------------|-------------|
| Detectar subdirectorio de despliegue | Config manual | Automático vía aritmética de bytes |
| Validar sintaxis de rutas `{id?}` | Regex frágiles | Lexer con posición exacta del error |
| Serializar JSON | `json_encode` + headers | Retorno `array` desde el controlador |
| Filtrar `{id}` no numérico | Código en cada ruta | `->filter_by_type(['id' => 'integer'])` |

## Siguiente paso

Registro frente a ejecución, `get_routes()` y organización de archivos en [02-ciclo-despacho.md](02-ciclo-despacho.md).