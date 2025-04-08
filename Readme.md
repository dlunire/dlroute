# Sistema de Enrutamiento

`DLRoute` es un sistema de enrutamiento diseñado para facilitar la gestión de rutas y direcciones URL en aplicaciones web.

Actualmente, permite filtrar por tipos de datos o expresiones regulares, tema que veremos más abajo.

Por otra parte, no solamente soporta el envío de formularios, también de contenido en formato JSON directamente en el cuerpo (`body`).

## Características

- Definición de rutas simples y complejas.
- Manejo de diferentes métodos `HTTP` como `GET`, `POST`, `PUT`, `PATCH` y `DELETE`, etc.
- Parámetros variables en las rutas.
- Permite establecer el tipo de datos que se espera en el parámetros, así como el uso de las expresiones regulares.
- Uso de controladores y `callbacks` para manejar las rutas.
- Integración flexible en proyectos web.

## Instalación

Para comenzar a utilizar `DLRoute`, sigue estos pasos:

1. Instala `DLRoute` utilizando `Composer`:

   ```bash
   composer require dlunamontilla/dlroute
    ```

2. Configura el sistema de enrutamiento en tu aplicación.
   > Recuerda que todas las peticiones deben hacerse a un archivo base (`index.php`) y esta debe estar ubicada en un subdirectorio, por ejemplo, `public/` o `html_public` o uno con un nombre personalizado.

3. Define las rutas utilizando el método adecuado para la petición.

### Sintaxis

Método GET:

```php
DLRoute::get(string $uri, callable|array|string $controller): DLParamValueType;
```

Método POST:

```php
DLRoute::post(string $uri, callable|array|string $controller): DLParamValueType;
```

Método PUT:

```php
DLRoute::put(string $uri, callable|array|string $controller): DLParamValueType;
```

Método PATCH:

```php
DLRoute::patch(string $uri, callable|array|string $controller): DLParamValueType;
```

Método DELETE:

```php
DLRoute::delete(string $uri, callable|array|string $controller): DLParamValueType;
```

> Tome en cuenta que para hacer funcionar las rutas, es decir, que ejecuten el controlador debe colocar al final de todas las rutas la siguiente línea:
>
> ```php
> DLRoute::execute();
> ```
>
> Puede usarse en un proyecto con PHP nativo, pero fue pensado para usarse en el _mini-framework_ **DLUnire** (actualmente en desarrollo).
>
### Ejemplos de definición de rutas

Ejemplo de definición de rutas utilizando Array, cadenas de texto y `callbacks`.

Definición de rutas utilizando el método de envío `HTTP GET` con un array como segundo argumento _array_:

```php
use DLRoute\Requests\DLRoute as Route;
use DLRoute\Test\TestController;

Route::get('/ruta', [TestController::class, 'method']);
```

De acuerdo al ejemplo anterior, lo que ocurre es que se está ejecutando el método `method` de la clase `TestController` en la _URI_ `/ruta`.

Podemos repetir el mismo ejemplo utilizando rutas parametrizadas; por ejemplo:

```php
use DLRoute\Requests\DLRoute as Route;
use DLRoute\Test\TestController;

Route::get('/ruta/{parametro}', [TestController::class, 'method']);
```

El ejemplo anterior es un ejemplo de rutas con parámetros que ejecuta el mismo método del mismo controlador.

Recuerda que debe definir el controlador de la siguiente forma:

```php
<?php

namespace TuNamespaces\CarpetaControladores;
use DLRoute\Config\Controller;

final class TestController extends Controller {
  
  public function tu_metodo(object $params): object {
    return $params;
  }
}
```

Por ejemplo, el método `public function tu_metodo(object $params): object` debe devolver el tipo de salida que deseamos que se visualice.

Si se desea devolver código HTML, solo tiene que definir el controlador de esta forma:

```php
<?php

namespace TuNamespaces\CarpetaControladores;
use DLRoute\Config\Controller;

final class TestController extends Controller {
  
  public function tu_metodo(object $params): string {
    return "Tu código HTML"
  }
}
```

Si estás utilizando el Framework **DLUnire**, puede utilizar la función `view()` que posee dos parámetros. El primero es la ruta de la vista y la segunda, las variables que serán accesibles en la plantilla `tu-plantilla.template.html`

Puedes definir una ruta utilizando tipos, por ejemplo:

```php
use DLRoute\Requests\DLRoute as Route;
use DLRoute\Test\TestController;

Route::get('/ruta/{parametro}', [TestController::class, 'method'])
  ->filter_by_type([
    "parametro" => "numeric"
  ]);
```

O también, se desea admitir correos electrónicos:

```php
use DLRoute\Requests\DLRoute as Route;
use DLRoute\Test\TestController;

Route::get('/ruta/{email}', [TestController::class, 'method'])
  ->filter_by_type([
    "email" => "email"
  ]);
```

Tome en cuenta que al método `filter_by_type` se le pasa como argumento un _array_ asociativo donde la clave es el parámetro y su valor el tipo que se espera.

Por ejemplo:

```php
->filter_by_type([
  "parametro" => "tipo"
]);
```

O también, mediante el uso de expresiones regulares:

```php
->filter_by_type([
  "parametro" => "/[a-f0-9]+/"
]);
```

Para capturar caracteres que van desde el `0` hasta la `f`.

#### Tipos admitidos

Los tipos admitidos por el momento que puede usar sin usar expresiones regulares son:

```php
integer, float, numeric, boolean, string, email uuid
```

### Definición de rutas por medio de `callbacks`

Anteriormente, habíamos visto que las rutas las podíamos definir de la siguiente manera:

```php
Route::get('/ruta/{parametro}', [TestController::class, 'method']);
```

También la puede definir mediante `callbacks`, por ejemplo:

```php
Route::get('/ruta/{parametro}', function (object $params) {
  return $params;
});
```

Recuerda, que `$params` se está retornando como ejemplo, pero puede retornar cualquier cosa allí. Lo que retorne allí será vista por el usuario final.

Si retorna un array u objeto (caso de `$params`) la salida devuelta será en formato JSON.

> **Importante:**
>
> El ejemplo que se hizo con el método `HTTP GET` es aplicable a todos los demás métodos de envío. Es exactamente igual. Lo único que cambia es el nombre del método de la clase `DLRoute` para indicar el método de envío.
