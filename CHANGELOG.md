# Changelog

Todas las modificaciones importantes a este proyecto se documentarán en este archivo.

Este proyecto sigue el formato de [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y utiliza [SemVer](https://semver.org/lang/es/) para el control de versiones.

## [1.0.0] - 2025-04-08

### Added
- Versión inicial del proyecto.
- Estructura base de la biblioteca con arquitectura modular.
- Sistema de enrutamiento HTTP con soporte para métodos `GET`, `POST`, `PUT`, `DELETE` y `OPTIONS`.
- Controlador base con manejo de respuestas HTTP estándar.
- Integración con sistema de carga de archivos mediante el trait `DLUpload`.
- Soporte para redirección condicional a HTTPS con la clase `DLHost`.
- Detección del protocolo actual (`http` o `https`) a través de `DLServer::get_protocol()`.

### Documentation
- Documentación PHPDoc profesional incluida en todas las clases, métodos y propiedades públicas.
- Licencia MIT incluida.
- Descripción de la estructura de carpetas y ejemplos básicos de uso en el archivo `README.md`.
