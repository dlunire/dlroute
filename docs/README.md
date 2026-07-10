# Documentación DLRoute

## Tutorial de uso (recomendado)

Guía progresiva en español: [tutorial/README.md](tutorial/README.md)

DLRoute es la **capa de infraestructura HTTP** del ecosistema DLUnire: enrutamiento, peticiones entrantes y salientes, telemetría y subida de archivos. Funciona con o sin DLCore.

| Capítulo | Tema |
|----------|------|
| 1 | [Inicio rápido](tutorial/01-inicio-rapido.md) |
| 2 | [Ciclo de despacho](tutorial/02-ciclo-despacho.md) |
| 3 | [`DLServer` y contexto de ejecución](tutorial/03-dlserver-contexto.md) |
| 4 | [Registro de rutas y controladores](tutorial/04-registro-controladores.md) |
| 5 | [Parámetros dinámicos en la URI](tutorial/05-parametros-dinamicos.md) |
| 6 | [`filter_by_type()` y validación](tutorial/06-filter-by-type.md) |
| 7 | [`match()` y `RouteHandler`](tutorial/07-match-routehandler.md) |
| 8 | [`DLOutput` y respuestas HTTP](tutorial/08-dloutput-respuestas.md) |
| 9 | [Controladores y peticiones entrantes](tutorial/09-controladores-peticiones.md) |
| 10 | [Query string y autómata finito](tutorial/10-querystring-automata.md) |
| 11 | [`Router` y telemetría](tutorial/11-router-telemetria.md) |
| 12 | [Subida de archivos (`DLUpload`)](tutorial/12-subida-archivos.md) |
| 13 | [Peticiones HTTP salientes](tutorial/13-peticiones-salientes.md) |
| 14 | [Errores y diagnósticos](tutorial/14-errores-diagnosticos.md) |
| 15 | [Despliegue en producción](tutorial/15-despliegue-produccion.md) |
| 16 | [Integración con DLCore / DLUnire](tutorial/16-integracion-dlcore.md) |

---

## Referencia por módulo

Documentación técnica existente en `documentation/`:

| Tema | Archivo |
|------|---------|
| Clase `DLRoute` | [Documentation/DLRoute.md](../documentation/Documentation/DLRoute.md) |
| Router (ES) | [Router/Router-ES.md](../documentation/Router/Router-ES.md) |
| Peticiones HTTP (ES) | [Request/Request-ES.md](../documentation/Request/Request-ES.md) |
| Subida de archivos | [Request/DLUpload-ES.md](../documentation/Request/DLUpload-ES.md) |
| `RouteHandler` | [Documentation/RouteHandler.md](../documentation/Documentation/RouteHandler.md) |
| `QueryParamComposer` | [Documentation/QueryParamComposer.md](../documentation/Documentation/QueryParamComposer.md) |
| `ResourceManager` | [ResourceManager.md](../documentation/ResourceManager.md) |
| HTTP Request (v2) | [v2/HTTP/Request.md](../documentation/v2/HTTP/Request.md) |

## Ecosistema DLUnire

| Capa | Paquete | Tutorial |
|------|---------|----------|
| Infraestructura HTTP (servidor) | `dlunire/dlroute` | Este tutorial |
| UI en el navegador (cliente) | `@dlunire/front-dlroute` | [Mini-tutorial front](../../front/parsing/docs/TUTORIAL.md) · [índice](../../front/parsing/docs/README.md) |
| Kernel | `dlunire/dlcore` | [DLCore tutorial](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/README.md) |
| Mapa del monorepo | — | [README raíz](../../../README.md) |

> **front-dlroute** no sustituye este paquete: enruta **vistas/UI** en el cliente. HTTP, controladores PHP y SSR siguen aquí.