# DLRoute – PHP Routing System

**DLRoute** is a simple, flexible, and efficient routing system designed for web applications in PHP. It provides advanced support for data filtering, parameter types, and clean integration with your application.

---

## 🌐 Descripción en Español

**DLRoute** es un sistema de enrutamiento diseñado para facilitar la gestión de rutas y direcciones URL en aplicaciones web.

Actualmente, permite filtrar por tipos de datos o expresiones regulares. También admite contenido enviado en formato JSON directamente en el cuerpo (`body`) de la petición.

### ✅ Características

- Definición de rutas simples y complejas.
- Manejo de métodos `HTTP`: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
- Soporte para parámetros dinámicos y tipados.
- Validación por tipo o expresión regular.
- Uso de controladores o funciones anónimas (`callbacks`).
- Integración flexible con proyectos PHP nativos o con el framework DLUnire.

### 💾 Instalación

```bash
composer require dlunire/dlroute
```

Ubica tu archivo principal en una carpeta pública (como `public/` o `html_public`). Define las rutas y al final, ejecuta:

```php
DLRoute::execute();
```

### ✏️ Sintaxis

```php
DLRoute::get(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::post(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::put(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::patch(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::delete(string $uri, callable|array|string $controller): DLParamValueType;
```

### 📌 Ejemplos

#### Rutas básicas con controlador:

```php
use DLRoute\Requests\DLRoute as Route;
use DLRoute\Test\TestController;

Route::get('/ruta', [TestController::class, 'method']);
Route::get('/ruta/{parametro}', [TestController::class, 'method']);
```

#### Definición del controlador:

```php
final class TestController extends Controller {
    public function tu_metodo(object $params): object|string {
        return $params; // o HTML si deseas
    }
}
```

#### Rutas con tipos:

```php
Route::get('/ruta/{id}', [TestController::class, 'method'])
  ->filter_by_type(['id' => 'numeric']);
```

O con expresión regular:

```php
->filter_by_type(['token' => '/[a-f0-9]+/']);
```

#### Tipos admitidos

```text
integer, float, numeric, boolean, string, email, uuid
```

#### Uso de callbacks:

```php
Route::get('/ruta/{parametro}', function (object $params) {
  return $params;
});
```

---

## 🌍 English Description

**DLRoute** is a routing system designed to simplify URL management in modern PHP applications. It supports type filtering and regular expressions, and accepts JSON payloads directly from the body.

### ✅ Features

- Simple and complex route definitions.
- Supports `GET`, `POST`, `PUT`, `PATCH`, `DELETE` HTTP methods.
- Dynamic route parameters with type filtering.
- Regular expression-based parameter validation.
- Supports controllers and callbacks.
- Easily integrates into native PHP or the **DLUnire** framework.

### 💾 Installation

```bash
composer require dlunire/dlroute
```

Your `index.php` should be placed in a public folder (e.g., `public/`). Remember to execute all defined routes:

```php
DLRoute::execute();
```

### ✏️ Syntax

```php
DLRoute::get(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::post(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::put(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::patch(string $uri, callable|array|string $controller): DLParamValueType;
DLRoute::delete(string $uri, callable|array|string $controller): DLParamValueType;
```

### 📌 Examples

#### Controller usage:

```php
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
```

#### Controller structure:

```php
final class UserController extends Controller {
    public function index(object $params): string {
        return view('users.index', ['users' => []]);
    }
}
```

#### With parameter type filtering:

```php
Route::get('/users/{id}', [UserController::class, 'show'])
  ->filter_by_type(['id' => 'integer']);
```

#### Callback usage:

```php
Route::get('/info', function (object $params) {
  return ['status' => 'ok'];
});
```

> If an array or object is returned, DLRoute will automatically send a JSON response.

