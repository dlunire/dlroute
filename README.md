# DLRoute – PHP Routing System

## Project Documentation

This repository contains documentation for the main components of the project, including HTTP request handling, routing, and resource management.

---

## Table of Contents

### Request
| Language | Documentation                            |
| -------- | ---------------------------------------- |
| English  | [Request-EN](docs/Request/Request-EN.md) |
| Spanish  | [Request-ES](docs/Request/Request-ES.md) |

### Router
| Language | Documentation                         |
| -------- | ------------------------------------- |
| English  | [Router-EN](docs/Router/Router-EN.md) |
| Spanish  | [Router-ES](docs/Router/Router-ES.md) |

### Resource Manager
| Language          | Documentation                              |
| ----------------- | ------------------------------------------ |
| English / Spanish | [ResourceManager](docs/ResourceManager.md) |

---

> Each document provides detailed explanations, examples, and best practices for using the respective classes and methods in your PHP application.

**DLRoute** is a simple, flexible, and efficient routing system designed for web applications in PHP. It provides advanced support for data filtering, parameter types, and clean integration with your application.

Since version **v1.0.4**, DLRoute not only manages routes: it **exposes and normalizes the full HTTP execution context**, even in non-deterministic environments.

---

## 🌐 Descripción en Español

**DLRoute** es un sistema de enrutamiento diseñado para facilitar la gestión de rutas y URLs en aplicaciones web PHP, manteniendo bajo acoplamiento con el entorno de ejecución.

Permite filtrar parámetros por tipo o expresiones regulares, admite contenido JSON enviado directamente en el cuerpo (`body`) de la petición y, desde la versión **v1.0.4**, proporciona información detallada y coherente del **host, esquema, puertos e IP**, incluso detrás de *reverse proxies*, túneles o en ejecución por CLI.

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

  * Local
  * Detrás de reverse proxy
  * Túneles (por ejemplo, ngrok)
  * CLI (tests automatizados)

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
  "hostname": "example.com:443",
  "is_https": true,
  "IP": "{ip-del-cliente-http}",
  "port": 443,
  "local_port": 4000,
  "method": "GET",
  "proxy": true
}
````

Esta información es consistente incluso en entornos mal configurados o no estándar.

---

## 🌐 Control explícito del host externo

Para escenarios específicos (tests, simulaciones, entornos incompletos), DLRoute permite imponer un host externo:

```php
DLServer::set_external_host('example.test', false);
```

* Si el segundo parámetro es `false`, el host se usa solo si no se pudo detectar uno válido.
* Si es `true`, el host impuesto será el único permitido.

Se puede configurar:

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

## ✏️ Sintaxis de rutas

```php
DLRoute::get(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::post(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::put(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::patch(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::delete(string $uri, callable|array|string $controller): DLParamValueType;
```

---

## 📌 Ejemplos de uso

### Rutas básicas con controlador

```php
use DLRoute\Requests\DLRoutes;
use DLRoute\Test\TestController;

DLRoute::get('/ruta', [TestController::class, 'method']);
DLRoute::get('/ruta/{parametro}', [TestController::class, 'method']);
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

Puedes definir los tipos en las rutas parametrizadas:

```php
DLRoute::get('/ruta/{id}', [TestController::class, 'method'])
  ->filter_by_type(['id' => 'numeric']);
```

### Con expresión regular

```php
->filter_by_type(['token' => '/[a-f0-9]+/']);
```

### Tipos admitidos

Los tipos admitidos son los siguientes:

- `string`, `uuid`, `email`, `integer`, `float`, `numeric` y `boolean`.
- Los tipos no soportados deben ser definidos utilizando expresiones regulares.

### Uso de callbacks

```php
DLRoute::get('/ruta/{parametro}', function (object $params) {
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
* Supports `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE` y `OPTIONS`.
* Dynamic route parameters with type filtering.
* Regular expression-based parameter validation.
* Supports controllers and callbacks.
* Seamless integration with native PHP or **DLUnire**.
* Full execution context exposure (v1.0.4).

---

## 📌 Callback example

```php
DLRoute::get(uri: '/info', controller: function () {
    return ['status' => 'ok'];
});
```

También permite definir rutas con parámetros opcionales:

```php
DLRoute::get('/products/{uuid?}/product-name', [ProductController::class, 'products']);
```

Donde un ruta como la siguiente:

```bash
/products/{uuid?}/product-name
```

Registra:

```bash
/products
/products/.../product-name
```

Porque sigue el mismo principio que los `nullable` de los lenguajes de programación, es decir, es como si estuvieras utilizando algo parecido a esto:

```php
$products->uuid?->product_name
```

> If an array or object is returned, DLRoute automatically sends a JSON response.

```

## 👤 Author

**David E Luna M**
Creator and lead developer of [DLUnire](https://github.com/dlunire) — a PHP ecosystem for building fast, API-oriented web applications.

- GitHub: [@dlunire](https://github.com/dlunire "Perfil en GitHub")
- Email: [dlunireframework@gmail.com](dlunireframework@gmail.com "Correo electrónico de contacto")
- License: MIT
- X: [@dlunire](https://x.com/dlunire "Perfil de DLUnire en X")
- Facebook: [DLUnire Framework](https://www.facebook.com/profile.php?id=61575156278078)