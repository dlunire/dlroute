# DLRoute – PHP Routing System

**DLRoute** is a simple, flexible, and efficient routing system designed for web applications in PHP. It provides advanced support for data filtering, parameter types, and clean integration with your application.

Desde la versión **v1.0.4**, DLRoute no solo gestiona rutas: **expone y normaliza el contexto completo de ejecución HTTP**, incluso en entornos no deterministas.

---

## 🌐 Descripción en Español

**DLRoute** es un sistema de enrutamiento diseñado para facilitar la gestión de rutas y direcciones URL en aplicaciones web PHP, manteniendo un bajo acoplamiento con el entorno de ejecución.

Permite filtrar parámetros por tipo o expresiones regulares, admite contenido JSON enviado directamente en el cuerpo (`body`) de la petición y, desde la **v1.0.4**, proporciona información detallada y coherente del **host, esquema, puertos e IP**, incluso detrás de *reverse proxies*, túneles o en ejecución por CLI.

---

## 🆕 Novedades destacadas (v1.0.4)

### Resolución robusta del contexto del servidor

DLRoute ahora determina de forma explícita y modular:

* Dominio y hostname real o impuesto.
* Esquema HTTP (`http` / `https`) de forma determinista.
* Dirección IP del cliente desde múltiples fuentes.
* Diferenciación entre:

  * Puerto local (donde corre el script).
  * Puerto remoto (expuesto al cliente).
* Detección de ejecución:

  * En local.
  * Detrás de reverse proxy.
  * En túneles (por ejemplo, ngrok).
  * En CLI (tests automatizados).

Todo esto se implementa mediante **traits especializados**, no lógica implícita.

---

## ✅ Características

* Definición de rutas simples y complejas.
* Manejo de métodos HTTP: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
* Soporte para parámetros dinámicos y tipados.
* Validación por tipo o expresión regular.
* Uso de controladores o funciones anónimas (*callbacks*).
* Integración flexible con proyectos PHP nativos o con el framework **DLUnire**.
* Exposición del contexto completo de ejecución HTTP (v1.0.4).

---

## 🧠 Contexto del servidor (v1.0.4)

DLRoute puede exponer información como:

```json
{
  "dir": "/subdir",
  "route": "/ruta/registrada",
  "uri": "/subdir/ruta/registrada",
  "base_url": "https://example.com/subdir",
  "domain": "example.com",
  "hostname": "example.com:443", // Solo se muestra el puerto aquí si éste es diferente de 443 u 80
  "is_https": true,
  "IP": "{ip-del-cliente-http}", // La obtiene de forma automática
  "port": 443,
  "local_port": 4000,
  "method": "GET",
  "proxy": true
}
```

Esta información es consistente incluso en entornos mal configurados o no estándar.

---

## 🌐 Control explícito del host externo

Para escenarios específicos (tests, simulaciones, entornos incompletos), DLRoute permite imponer un host externo:

```php
DLServer::set_external_host('example.test', false);
```

* Si el segundo parámetro es `false`, el host se usa solo si no se pudo detectar uno válido.
* Si es `true`, el host impuesto será el único permitido.

Esto puede configurarse:

* Globalmente (al inicio de la aplicación).
* De forma puntual para contextos específicos.

---

## 💾 Instalación

```bash
composer require dlunire/dlroute
```

Ubica tu archivo principal en una carpeta pública (`public/`, `html_public`, etc.).
Define tus rutas y ejecuta:

```php
DLRoute::execute();
```

---

## ✏️ Sintaxis

```php
DLRoute::get(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::post(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::put(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::patch(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::delete(string $uri, callable|array|string $controller): DLParamValueType;
```

---

## 📌 Ejemplos

### Rutas básicas con controlador

```php
use DLRoute\Requests\DLRoute as Route;
use DLRoute\Test\TestController;

Route::get('/ruta', [TestController::class, 'method']);
Route::get('/ruta/{parametro}', [TestController::class, 'method']);
```

### Definición del controlador

```php
final class TestController extends Controller {
    public function tu_metodo(object $params): object|string {
        return $params;
    }
}
```

### Rutas con tipos

```php
Route::get('/ruta/{id}', [TestController::class, 'method'])
  ->filter_by_type(['id' => 'numeric']);
```

### Con expresión regular

```php
->filter_by_type(['token' => '/[a-f0-9]+/']);
```

### Tipos admitidos

```text
integer, float, numeric, boolean, string, email, uuid
```

### Uso de callbacks

```php
Route::get('/ruta/{parametro}', function (object $params) {
    return $params;
});
```

---

## 🌍 English Description

**DLRoute** is a PHP routing system designed to simplify URL management while remaining resilient to non-standard execution environments.

Since **v1.0.4**, DLRoute exposes a normalized and deterministic HTTP context, even when running behind reverse proxies, tunnels, or in CLI-based automated tests.

---

## 🆕 Highlights (v1.0.4)

* Deterministic HTTP scheme resolution.
* Explicit domain and hostname detection or enforcement.
* Local vs remote port differentiation.
* Multi-source client IP detection.
* CLI-safe defaults for automated testing.
* Modular design based on specialized traits.

---

## ✅ Features

* Simple and complex route definitions.
* Supports `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
* Dynamic route parameters with type filtering.
* Regular expression-based parameter validation.
* Supports controllers and callbacks.
* Seamless integration with native PHP or **DLUnire**.
* Full execution context exposure (v1.0.4).

---

## 📌 Callback example

```php
Route::get('/info', function (object $params) {
    return ['status' => 'ok'];
});
```

> If an array or object is returned, DLRoute automatically sends a JSON response.

---

Si quieres, el siguiente paso natural sería:

* Separar este contenido en **README + Wiki técnica**.
* O crear una sección específica: **“Execution Context & Server Detection”** con ejemplos avanzados (proxy, ngrok, CLI).
