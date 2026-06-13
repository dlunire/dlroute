# Changelog

Todas las modificaciones importantes a este proyecto se documentarán en este archivo.

Este proyecto sigue el formato de [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y utiliza [SemVer](https://semver.org/lang/es/) para el control de versiones.

---

## [v1.0.9] - 2026-06-13

### Added

* **Nuevas propiedades en la clase `Telemetry` (`DLRoute\Core\Data\Telemetry`):**

  * `$domain` — dominio o nombre de host sin puerto (`DLHost::get_domain()`).
  * `$hostname` — hostname completo, incluyendo puerto cuando no es estándar (`DLHost::get_hostname()`).
  * `$is_https` — indica si la conexión utiliza HTTPS (`DLHost::is_https()`).
  * `$port` — puerto expuesto al cliente HTTP (`DLServer::get_port()`).
  * `$local_port` — puerto local donde se ejecuta la aplicación (`DLServer::get_local_port()`).
  * `$proxy` — indica si la petición proviene probablemente de un proxy inverso (`DLServer::is_likely_proxy()`).

* **Autor agregado al `README.md`:**

  * Se incluye sección de autoría con nombre, correo y perfiles de redes sociales de David E Luna M.

### Changed

* **Corrección de namespaces:**

  * `TelemetryRequest` se reubica de `DLRoute\Interfaces\Telemetry` a `DLRoute\Core\Telemetry`,
    alineando la clase con la convención de namespaces del ecosistema DLUnire.
  * El `@package` del docblock de `TelemetryRequest` se actualiza en consecuencia.

* **Eliminación de `$code` en `Telemetry`:**

  * Se elimina la propiedad `$code` y su asignación hardcodeada (`404`), que no correspondía
    al propósito de un objeto de telemetría de entorno.

### Fixed

* Comentario residual `// return new Telemetry($message);` eliminado de `TelemetryInterface`.
* Etiqueta `* *` duplicada corregida en el docblock de la clase `Telemetry`.

---

## [v1.0.8] - 2026-06-13

### Added

* **Nueva interfaz `TelemetryInterface` (`DLRoute\Interfaces\Telemetry\TelemetryInterface`):**

  * Define el contrato para la captura de telemetría en el ciclo de vida de una petición HTTP.
  * Declara el método estático `telemetry(string $message = ""): Telemetry`, que retorna una
    instantánea inmutable del entorno de ejecución, incluyendo metadatos de red, cabeceras HTTP
    y el mapa del enrutador.
  * Permite implementaciones alternativas o especializadas del sistema de diagnóstico.

* **Nueva clase `TelemetryRequest` (`DLRoute\Interfaces\Telemetry\TelemetryRequest`):**

  * Implementación concreta de `TelemetryInterface`.
  * Expone el método estático `TelemetryRequest::telemetry(string $message = ""): Telemetry`.
  * Delega la construcción del objeto de diagnóstico a `Telemetry`, encapsulando el estado
    inmutable del entorno en el momento de la llamada.

### Changed

* El método `telemetry()` fue removido de `DLOutput`, donde existía como método de instancia
  sin contrato formal.
* La funcionalidad se reubica en `TelemetryRequest` como método estático, bajo una interfaz
  dedicada, mejorando la cohesión, la testabilidad y la extensibilidad del sistema de telemetría.

### Documentation

* Documentación PHPDoc completa agregada a `TelemetryInterface`, incluyendo descripción del
  contrato, parámetros y valor de retorno.
* Documentación PHPDoc agregada a `TelemetryRequest` con `{@inheritdoc}` en el método
  implementado, delegando la documentación canónica a la interfaz.

---

## [v1.0.7] - 2026-06-07

### Changed

* Limpieza del archivo `index.php`: se eliminó código residual dejado durante el desarrollo de `v1.0.6` sin afectar ninguna funcionalidad existente.
  
---

## [v1.0.6] - 2026-06-07

### Added

* **Soporte para los métodos HTTP `HEAD` y `OPTIONS` en el router:**

  * El router ahora responde correctamente a peticiones `HEAD` en todas las rutas registradas.
  * Las respuestas `HEAD` devuelven los mismos headers que `GET` sin body, cumpliendo con el estándar HTTP/1.1 (RFC 7231).
  * El router ahora responde correctamente a peticiones `OPTIONS`, permitiendo la negociación de capacidades entre cliente y servidor (CORS preflight).
  * Mejora la compatibilidad con scrapers y crawlers externos (Facebook, Twitter, WhatsApp) que verifican la existencia de recursos mediante `HEAD` antes de realizar el `GET`.

* **Nuevo método estático `match()` en `DLRoute`:**

  * Permite registrar una misma ruta para múltiples métodos HTTP simultáneamente.
  * Acepta un array de casos del enum `Methods` como primer argumento.
  * Compatible con rutas paramétricas y el sistema de filtros por tipo (`filter_by_type()`).
  * La validación de tipos se delega a las definiciones individuales de cada ruta.
  * Elimina la necesidad de registrar la misma ruta varias veces para distintos verbos HTTP.

* **Nuevo enum `Methods` (`DLRoute\Enums\Methods`):**

  * Centraliza los métodos HTTP soportados como casos tipados: `GET`, `HEAD`, `OPTIONS`, `POST`, `PUT`, `PATCH`, `DELETE`.
  * Reemplaza el uso de strings literales en las llamadas internas a `self::request()`.
  * Mejora la seguridad de tipos y la detección temprana de errores en tiempo de desarrollo.

* **Soporte de parámetros opcionales en rutas mediante autómata finito:**

  * Se implementa el analizador léxico `RouterLexer` (`DLRoute\Core\Routing\Automaton\RouterLexer`) que tokeniza la URI byte a byte, clasificando cada segmento como texto literal (`TokenType::TEXT_PLAIN`) o parámetro dinámico (`TokenType::PARAM`).
  * Se implementa el generador de rutas `RouteGenerator` (`DLRoute\Core\Routing\Automaton\RouteGenerator`) que consume los tokens del lexer y produce todas las variantes válidas de una URI cuando contiene parámetros opcionales.
  * Cada parámetro opcional genera una variante de ruta adicional truncada en ese punto, siguiendo el modelo explícito de declaración de rutas.
  * Ejemplo de uso:

```php
    // Genera automáticamente: ["/blog", "/blog/{id}/comentarios"]
    DLRoute::get('/blog/{id?}/comentarios', ...);

    // Con validación de tipo sobre parámetro obligatorio:
    DLRoute::get('/ruta/{id}', function(object $params) {
        // ...
    })->filter_by_type(["id" => "integer"]);
```

  * Todos los métodos HTTP (`get`, `head`, `post`, `put`, `patch`, `delete`, `options`, `match`) integran `RouteGenerator` internamente mediante `load_routes()`.
  * Se agrega validación de sintaxis de rutas: el uso incorrecto del marcador `?` fuera de un parámetro lanza `RouteException` con la posición exacta del error.
  * Nuevo enum `TokenType` (`DLRoute\Core\Routing\Automaton\TokenType`) con los casos `TEXT_PLAIN` y `PARAM`.
  * Nueva interfaz `RouteLexerInterface` (`DLRoute\Interfaces\Routing\RouteLexerInterface`) con las constantes de tokens del autómata: `BRACKET_OPEN`, `BRACKET_CLOSE`, `OPTIONAL_PARAMETER`, `WHITE_SPACE` y `SLASH`.

* **Nueva telemetría en `DLOutput`:**

  * Nuevo método `telemetry(string $message): Telemetry` que captura una instantánea inmutable del estado del entorno de ejecución, incluyendo metadatos de red, cabeceras HTTP y el mapa del enrutador.
  * Retorna un objeto `Telemetry` de solo lectura para diagnóstico y observabilidad del servidor.

### Changed

* Los métodos `get()`, `head()`, `post()`, `put()`, `patch()`, `delete()` y `options()` ahora usan `Methods::*` en lugar de strings literales al invocar `self::request()`.
* Todos los métodos de registro de rutas integran `RouteGenerator` para soportar parámetros opcionales de forma transparente.

### Documentation

* Documentación PHPDoc agregada a los métodos `head()`, `options()` y `match()`.
* Documentación completa de `RouterLexer`, `RouteGenerator`, `TokenType` y `RouteLexerInterface`.
* La documentación pública de los métodos heredados permanece en `RouteInterface`.

---

## [v1.0.5] - 2026-02-24

### Added

* **Nueva clase `Router` (`DLRoute\Core\Routing\Router`):**

  * Genera URLs absolutas a partir de rutas relativas (`Router::to()`).
  * Proporciona telemetría completa de la ruta actualmente visitada (`Router::from()`).
  * Normaliza rutas y valida formato, lanzando `RouteException` en caso de ruta inválida.
  * Documentación profesional PHPDoc incluida, ejemplo de uso y licencia MIT.

* **Nueva clase `RouterData` (`DLRoute\Core\Data\RouterData`):**

  * Telemetría completa de la petición HTTP.
  * Propiedades principales:

    * `url` – URL absoluta de la petición.
    * `ip_client` – IP del cliente.
    * `remote_addr` – Dirección remota de la petición.
    * `user_agent` – Agente de usuario.
    * `scheme` – Protocolo HTTP (`http` o `https`).
    * `host` – Nombre de host o dominio.
    * `port` – Puerto de la ruta.
    * `local_port` – Puerto local de ejecución de la aplicación.
    * `dir` – Directorio de ejecución de la aplicación.
    * `route` – Ruta relativa de la aplicación.
    * `uri` – URI completa (incluye directorio de ejecución).
    * `method` – Método HTTP.
    * `time` – Marca temporal de la consulta.
  * Integra traits: `SchemeHTTP`, `PortCandidate`, `Domain`.
  * Documentación profesional PHPDoc incluida y licencia MIT.

* **Ampliación del trait `Domain` (`DLRoute\Server\Domain`):**

  * Nuevo método `set_external_host(string $host, bool $required = false): void`.

    * Permite establecer un dominio externo cuando no se puede determinar un host válido automáticamente.
    * Parámetro `$required = true` indica que el host impuesto será el único aceptado.
    * Valida que el host no esté vacío y lanza `DomainException` en caso de error.
    * Facilita configuraciones globales o específicas forzando el dominio deseado.

### Changed

* No hay cambios retrocompatibles; esta versión introduce nuevas funcionalidades sin modificar funcionalidades previas.

### Documentation

* Se agregaron ejemplos de uso en PHPDoc para `Router` y `RouterData`.
* Explicación de normalización de rutas, generación de URLs absolutas y telemetría de rutas.
* Licencia MIT y metadatos de autor integrados en todas las clases y traits actualizados.

---

## [v1.0.4] - 2026-01-15

### Added

* **Nuevo sistema modular para resolución de contexto de servidor (traits):**

  * **Trait `Domain` (`DLRoute\Server\Domain`):**

    * Resolución del dominio o nombre de host desde múltiples fuentes.
    * Capacidad de imponer un dominio externo de forma opcional u obligatoria.
    * Nuevo método:

      * `set_external_host(string $host, bool $required = false): void`
    * Soporte para configuración global (bootstrap) o contextual (tests, escenarios específicos).

  * **Trait `IPAddress` (`DLRoute\Server\IPAddress`):**

    * Deducción de la dirección IP del cliente desde múltiples fuentes posibles.
    * Retorno seguro de `null` cuando no es posible determinar una IP válida.

  * **Trait `PortCandidate` (`DLRoute\Server\PortCandidate`):**

    * Deducción de un puerto candidato durante la ejecución.
    * Diferenciación entre puerto local y puerto remoto.
    * Fallback controlado al puerto `80` en entornos no deterministas (CLI, pruebas automatizadas).
    * Nuevos métodos:

      * `get_local_port(): int`

  * **Trait `SchemeHTTP` (`DLRoute\Server\SchemeHTTP`):**

    * Determinación determinista del esquema HTTP (`http` o `https`).
    * Fallback explícito a `http` en entornos CLI.
    * Facilita la simulación de peticiones HTTP en pruebas automatizadas.

* **Ampliación significativa del contexto expuesto por el servidor:**

  * Inclusión de metadatos adicionales en respuestas y errores:

    * `uri`, `dir`, `base_url`
    * `domain`, `hostname`
    * `is_https`
    * Dirección IP del cliente
    * Puerto local y puerto remoto diferenciados
    * Método HTTP
    * Detección de proxy reverso
    * Marca temporal (`timestamp`)
    * Mensajes de ayuda (`hint`) en rutas no registradas

### Changed

* Refactorización interna del sistema de detección de dominio, esquema, IP y puertos:

  * Se reemplaza lógica implícita por traits especializados y reutilizables.
  * Mayor robustez en entornos con:

    * Reverse proxy
    * Túneles (por ejemplo, ngrok)
    * Subdirectorios
    * Ejecución desde CLI

* Las respuestas para rutas no registradas ahora incluyen contexto completo del entorno de ejecución, facilitando depuración, observabilidad y pruebas.

### Documentation

* Documentación PHPDoc profesional agregada a los nuevos traits:

  * `Domain`
  * `IPAddress`
  * `PortCandidate`
  * `SchemeHTTP`

* Documentación detallada de:

  * Comportamiento en entornos CLI.
  * Diferenciación entre host/puerto local y remoto.
  * Uso de `set_external_host()` en escenarios globales y específicos.

---

## [v1.0.3] - 2025-10-29

### Added

* **Soporte extendido para manejo de archivos y cookies persistentes:**

  * Nuevo método `set_cookies()` que permite establecer una ruta personalizada para almacenar cookies.
  * Nuevo método `get_cookies_path()` para obtener la ruta actual del archivo de cookies.
  * Nuevo método `delete_cookies()` que elimina de forma segura el archivo de cookies asociado a la sesión HTTP.
  * Creación automática de archivos temporales de cookies en el directorio del sistema (`sys_get_temp_dir()`).

* **Nueva clase abstracta `DLRoute\Http\HttpRequest`:**

  * Proporciona una estructura base para implementar clientes HTTP personalizados.
  * Integra el *trait* `Request`, unificando la gestión de cabeceras, redirecciones, verificación SSL y transferencia de datos.
  * Establece la base para la extensión de comportamientos en futuras versiones (por ejemplo, soporte para métodos asincrónicos o streams).

* **Mejoras de compatibilidad con archivos y contenido dinámico:**

  * Soporte preliminar para el envío de archivos mediante `multipart/form-data` (preparado para futuras expansiones).
  * Compatibilidad extendida con encabezados dinámicos al enviar archivos o formularios complejos.

### Changed

* El sistema de cookies ahora utiliza rutas absolutas y validación de existencia antes de lectura o eliminación.
* El manejo de redirecciones (`CURLOPT_FOLLOWLOCATION` y `CURLOPT_MAXREDIRS`) ahora se integra con el sistema de verificación SSL (`CURLOPT_SSL_VERIFY*`).
* Refactorización menor en el método `fetch()` para tipar explícitamente la salida (`string`) y delegar la solicitud principal a `request()`.

### Documentation

* Documentación profesional agregada a la clase abstracta `HttpRequest` con licencia MIT, autoría y metadatos de paquete.

* Actualización de comentarios PHPDoc en los métodos:

  * `set_cookies()`
  * `get_cookies_path()`
  * `delete_cookies()`
  * `fetch()`

* Detalles ampliados sobre el comportamiento del sistema de cookies y la nueva arquitectura modular basada en traits y clases abstractas.