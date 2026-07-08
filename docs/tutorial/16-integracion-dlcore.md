# 16 — Integración con DLCore / DLUnire

En el ecosistema DLUnire, DLRoute es la **capa inferior obligatoria** y DLCore el **kernel** que añade ORM, plantillas, autenticación y variables de entorno tipadas. Este capítulo cierra el tutorial mostrando cómo encajan ambos paquetes.

## Arquitectura

```
public/index.php
    └── Boot\Project::run()          ← DLCore / skeleton
            ├── Authorizations       ← CORS + DL_TOKEN (DLCore)
            ├── SystemCredentials    ← sesión (skeleton)
            ├── app/Helpers, Constants
            ├── routes/*.php         ← DLRoute::get/post/...
            └── DLRoute::execute()   ← despacho HTTP
```

DLCore **no** reemplaza a DLRoute: lo invoca al final del bootstrap.

## Dependencia en Composer

```json
{
    "require": {
        "dlunire/dlroute": "^2.0",
        "dlunire/dlcore": "^2.0"
    }
}
```

El skeleton DLUnire ya declara ambos.

## Controladores — herencia

```php
<?php
namespace DLUnire\Controllers;

use DLCore\Core\BaseController;

final class ProductsController extends BaseController {

    public function index(): array {
        return ['products' => \DLUnire\Models\Products::paginate()];
    }
}
```

`BaseController` extiende `DLRoute\Config\Controller` y añade:

- Lectura tipada de entradas (`get_required`, `get_post`, …)
- Integración con plantillas `view()`
- CSRF y cookies
- Todo lo de `DLUpload` y peticiones salientes

## Rutas en el skeleton

```php
<?php
// routes/web.php
use DLRoute\Requests\DLRoute;
use DLUnire\Auth\Auth;
use DLUnire\Controllers\ProductsController;
use DLUnire\Controllers\AuthController;

$auth = Auth::get_instance();

$auth->logged(function () {
    DLRoute::get('/dashboard', [DashboardController::class, 'index']);
    DLRoute::get('/api/products', [ProductsController::class, 'index']);
});

$auth->not_logged(function () {
    DLRoute::get('/login', [AuthController::class, 'show_login']);
    DLRoute::post('/login', [AuthController::class, 'login']);
});
```

Protección de rutas: [tutorial DLCore — cap. 27](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/27-dlauth-rutas.md).

## Mapa tutorial DLCore ↔ DLRoute

| Tema DLRoute (este tutorial) | Profundiza en DLCore |
|------------------------------|----------------------|
| Registro básico (cap. 04) | [04-controladores](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/04-controladores.md) |
| `filter_by_type`, `match` (06–07) | [26-dlroute-avanzado](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/26-dlroute-avanzado.md) |
| Subida archivos (12) | [12-subida-archivos](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/12-subida-archivos.md) |
| Despliegue (15) | [22-despliegue-produccion](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/22-despliegue-produccion.md) |
| APIs + CORS | [23-cors-dl-token-orm](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/23-cors-dl-token-orm.md) |

## DLRoute sin DLCore

Proyectos API mínimos pueden usar solo DLRoute:

```php
require 'vendor/autoload.php';

DLRoute::get('/api/health', fn () => ['ok' => true]);
DLRoute::execute();
```

Sin ORM ni plantillas; controladores extienden `DLRoute\Config\Controller` directamente.

## `ResourceManager` (assets en DLUnire)

Para empaquetar CSS/JS en rutas amigables con hash, el skeleton usa `DLRoute\Routes\ResourceManager` ([ResourceManager.md](../../documentation/ResourceManager.md)). Es opcional y orientado a vistas HTML con DLCore.

## Qué va en cada paquete

| Responsabilidad | Paquete |
|-----------------|---------|
| Enrutar HTTP, MIME, telemetría | DLRoute |
| Subida de archivos, SVG | DLRoute |
| Peticiones salientes cURL | DLRoute |
| ORM, plantillas, auth, mail | DLCore |
| Bootstrap `Project::run()` | Skeleton / DLCore |

## Fin del tutorial

Con los **16 capítulos** cubres DLRoute de forma autónoma: despacho, parámetros, filtros, respuestas, querystring, telemetría, subidas, cliente HTTP, errores, despliegue e integración con DLUnire.

- Tutorial DLCore: [github.com/dlunire/dlcore/docs/tutorial](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/README.md)
- Referencia de módulos: [docs/README.md](../README.md)
- CHANGELOG: [CHANGELOG.md](../../CHANGELOG.md)