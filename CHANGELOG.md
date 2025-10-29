# Changelog

Todas las modificaciones importantes a este proyecto se documentarán en este archivo.

Este proyecto sigue el formato de [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y utiliza [SemVer](https://semver.org/lang/es/) para el control de versiones.

## [1.0.2] - 2025-10-29

### Added

* Nuevos parámetros de configuración para cURL:

  * `CURLOPT_SSL_VERIFYPEER`: permite verificar la validez del certificado SSL del servidor.
  * `CURLOPT_SSL_VERIFYHOST`: permite verificar si el nombre del certificado coincide con el dominio remoto.
  * `CURLOPT_FOLLOWLOCATION`: habilita el seguimiento automático de redirecciones HTTP (`301`, `302`, etc.).
  * `CURLOPT_MAXREDIRS`: define el número máximo de redirecciones permitidas.
  * `CURLOPT_CONNECTTIMEOUT` y `CURLOPT_TIMEOUT`: controlan los tiempos máximos de conexión y ejecución.
* Manejo persistente de cookies con archivos temporales generados automáticamente (`dlroute_cookies.txt`).
* Nuevo comportamiento inteligente en el envío de datos:

  * Si no se especifica `Content-Type`, los datos se envían automáticamente como formulario estándar (`application/x-www-form-urlencoded`).
  * Si se especifica `Content-Type: application/json`, los datos se envían como JSON codificado.
* Integración del método `set_verify_host()` con validación estricta de argumentos y manejo de errores con `InvalidArgumentException`.
* Validaciones robustas y respuesta estructurada en caso de errores de conexión cURL.

### Changed

* Mejora del método `request()` para soportar distintos tipos de contenido sin intervención manual del desarrollador.
* Consolidación de configuraciones de cURL mediante `curl_setopt_array()` para mayor legibilidad y mantenimiento.
* Documentación ampliada del método `set_verify_host()` para reflejar los valores válidos (`0` y `2`).

### Documentation

* Actualización de la documentación PHPDoc de la clase `Request` para reflejar los nuevos parámetros y comportamientos.
* Inclusión de ejemplos sobre cómo enviar datos como formulario HTML o JSON según el encabezado `Content-Type`.