# Changelog

Todas las modificaciones importantes a este proyecto se documentarán en este archivo.

Este proyecto sigue el formato de [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y utiliza [SemVer](https://semver.org/lang/es/) para el control de versiones.

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