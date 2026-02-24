# Changelog

Todas las modificaciones importantes a este proyecto se documentarán en este archivo.

Este proyecto sigue el formato de [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y utiliza [SemVer](https://semver.org/lang/es/) para el control de versiones.

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