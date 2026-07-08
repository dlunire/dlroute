# Tutorial de uso — DLRoute

Guía progresiva para el motor de enrutamiento HTTP de DLUnire. Cada capítulo es independiente, pero se recomienda seguir el orden indicado.

| # | Tema | Archivo |
|---|------|---------|
| 1 | Inicio rápido | [01-inicio-rapido.md](01-inicio-rapido.md) |
| 2 | Ciclo de despacho | [02-ciclo-despacho.md](02-ciclo-despacho.md) |
| 3 | `DLServer` y contexto de ejecución | [03-dlserver-contexto.md](03-dlserver-contexto.md) |
| 4 | Registro de rutas y controladores | [04-registro-controladores.md](04-registro-controladores.md) |
| 5 | Parámetros dinámicos en la URI | [05-parametros-dinamicos.md](05-parametros-dinamicos.md) |
| 6 | `filter_by_type()` y validación | [06-filter-by-type.md](06-filter-by-type.md) |
| 7 | `match()` y `RouteHandler` | [07-match-routehandler.md](07-match-routehandler.md) |
| 8 | `DLOutput` y respuestas HTTP | [08-dloutput-respuestas.md](08-dloutput-respuestas.md) |
| 9 | Controladores y peticiones entrantes | [09-controladores-peticiones.md](09-controladores-peticiones.md) |
| 10 | Query string y autómata finito | [10-querystring-automata.md](10-querystring-automata.md) |
| 11 | `Router` y telemetría | [11-router-telemetria.md](11-router-telemetria.md) |
| 12 | Subida de archivos (`DLUpload`) | [12-subida-archivos.md](12-subida-archivos.md) |
| 13 | Peticiones HTTP salientes | [13-peticiones-salientes.md](13-peticiones-salientes.md) |
| 14 | Errores y diagnósticos | [14-errores-diagnosticos.md](14-errores-diagnosticos.md) |
| 15 | Despliegue en producción | [15-despliegue-produccion.md](15-despliegue-produccion.md) |
| 16 | Integración con DLCore / DLUnire | [16-integracion-dlcore.md](16-integracion-dlcore.md) |

## Convención de nombres

En el ecosistema DLUnire se usa **snake_case** para métodos, funciones, variables y claves de array en código de aplicación (`show_login`, `$user_id`, `'order_id'`). Las **clases** siguen PascalCase (`ApiController`, `QueryParamComposer`), alineado con PSR-4.

Los ejemplos del tutorial respetan esta convención. No uses camelCase (`showLogin`, `getUserId`) en código nuevo del proyecto.

## Requisitos

- PHP **8.2+**
- Composer
- Opcional: [`dlunire/dlcore`](https://packagist.org/packages/dlunire/dlcore) para ORM, plantillas y autenticación ([tutorial DLCore](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/README.md))

## Instalación

```bash
composer require dlunire/dlroute
```

DLRoute no requiere framework. En proyectos DLUnire, el skeleton ya lo integra vía `Boot\Project::run()`.

## Documentación de referencia

| Tema | Enlace |
|------|--------|
| README del paquete | [README.md](../../README.md) |
| Referencia de módulos | [docs/README.md](../README.md) |
| `DLRoute` (API) | [Documentation/DLRoute.md](../../documentation/Documentation/DLRoute.md) |
| Router (ES) | [Router-ES.md](../../documentation/Router/Router-ES.md) |
| Peticiones HTTP (ES) | [Request-ES.md](../../documentation/Request/Request-ES.md) |
| Subida de archivos | [DLUpload-ES.md](../../documentation/Request/DLUpload-ES.md) |