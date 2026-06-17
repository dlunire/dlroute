# `RouteHandler`

**Namespace:** `DLRoute\Core\Data`

**Tipo:** `class`

**Estructura:** Data Transfer Object (DTO) / Contenedor de Configuración

**Modificador:** `final`

---

## 📝 Descripción

La clase `RouteHandler` es un objeto de transferencia de datos (DTO) diseñado bajo un enfoque de alta eficiencia y tipado estricto. Su propósito fundamental dentro del ecosistema **DLRoute** es encapsular y estructurar de manera limpia todos los metadatos de una ruta arquitectónica (URI, controlador, datos estáticos inyectados, tipos MIME y filtros) antes de su registro formal en el sistema de enrutamiento.

Esta abstracción es el pilar central que permite el funcionamiento del método de registro masivo `DLRoute::match()`. Al separar la definición conceptual de una ruta del proceso inmediato de registro dinámico, `RouteHandler` dota al framework de la flexibilidad necesaria para agrupar, procesar o heredar configuraciones de endpoints sin penalizar el rendimiento ni generar sobrecarga en tiempo de ejecución.

---

## 🏛️ Propiedades de la Clase

Todas las propiedades de la clase son públicas para permitir un acceso directo de baja latencia ($O(1)$) por parte de los componentes internos del despachador, eliminando por completo la sobrecarga en CPU asociada a las llamadas de métodos de envoltura (*getters* y *setters*).

| Propiedad | Tipo | Por Defecto | Descripción |
| --- | --- | --- | --- |
| **`$uri`** | `string` | *Requerido* | La URI de la petición o patrón de la ruta. Admite la sintaxis nativa de parámetros dinámicos (`{id}`) y opcionales (`{param?}`). |
| **`$controller`** | `callable|array|string` | *Requerido* | El componente encargado de procesar la petición. Admite funciones anónimas (callbacks), arrays de clase y método `[MiClase::class, 'metodo']`, o identificadores en cadena `MiClaseController@metodo`. |
| **`$data`** | `array|object` | `[]` | Colección de datos estáticos o variables de contexto inyectadas a la ruta, quedando disponibles para el controlador durante el ciclo de vida de la petición. |
| **`$mime_type`** | `?string` | `null` | Especifica de manera explícita la cabecera `Content-Type` de la respuesta HTTP asociada a este endpoint (por ejemplo, `application/json`, `text/html`). |
| **`$handler_filters`** | `array<string, string>` | `[]` | Un mapa asociativo donde las llaves representan los parámetros dinámicos definidos en la `$uri` y los valores corresponden al tipo de validación requerido (`integer`, `uuid`, `string`, etc.). |

---

## ⚙️ Constructor y Métodos

### `__construct()`

El constructor inicializa el DTO asignando las configuraciones base. Se recomienda el uso de **argumentos con nombre (Named Arguments)** de PHP 8.2+ para una legibilidad superior y para evitar la dependencia del orden posicional de los parámetros.

```php
public function __construct(
    string $uri,
    callable|array|string $controller,
    array|object $data = [],
    ?string $mime_type = null,
    array $handler_filters = []
) {
    $this->uri = $uri;
    $this->controller = $controller;
    $this->data = $data;
    $this->mime_type = $mime_type;
    $this->handler_filters = $handler_filters;
}

```

#### Parámetros

* **`string $uri`**: La URI o ruta relativa del endpoint.
* **`callable|array|string $controller`**: El manejador ejecutable asignado.
* **`array|object $data`** *(Opcional)*: Datos de contexto adicionales para la ruta.
* **`?string $mime_type`** *(Opcional)*: Tipo de contenido de la respuesta HTTP.
* **`array $handler_filters`** *(Opcional)*: Filtros de validación por tipo asociados a los parámetros dinámicos.

---

### `get_quantity()`

Devuelve la cantidad exacta de filtros de tipo registrados dentro del arreglo asociativo `$handler_filters`.

```php
public function get_quantity(): int {
    return \count($this->handler_filters);
}

```

* **Retorno:** `int` — El número total de restricciones aplicadas a los parámetros de la ruta.
* **Uso Interno:** Es invocado de forma crítica por `DLRoute::match()` para evaluar de manera determinista si la ruta en procesamiento requiere encadenar la ejecución de la interfaz fluida de filtrado (`filter_by_type`).

---

## 🛠️ Ejemplos de Uso Práctico

### 1. Inicialización estándar con Argumentos con Nombre

Esta sintaxis optimiza la legibilidad del código al declarar explícitamente el destino de cada metadato de la ruta.

```php
use DLRoute\Core\Data\RouteHandler;
use App\Controllers\ProductController;

$route = new RouteHandler(
    uri: '/api/v1/products/{id}',
    controller: [ProductController::class, 'show'],
    mime_type: 'application/json',
    handler_filters: ['id' => 'integer']
);

```

### 2. Registro masivo multipropósito usando `DLRoute::match()`

Mapeo de múltiples verbos HTTP a un mismo endpoint utilizando una única instancia de configuración.

```php
use DLRoute\Requests\DLRoute;
use DLRoute\Enums\Methods;
use DLRoute\Core\Data\RouteHandler;

$profileRoute = new RouteHandler(
    uri: '/dashboard/profile',
    controller: 'App\Controllers\UserController@profile',
    mime_type: 'text/html'
);

// Registra la configuración para peticiones GET y POST simultáneamente
DLRoute::match([Methods::GET, Methods::POST], $profileRoute);

```

### 3. Configuración avanzada con inyección de datos y filtros complejos

Definición de un endpoint preparado para actualizaciones parciales o totales (`PUT` y `PATCH`), forzando la validación del identificador bajo formato `uuid` e inyectando un contexto estático de auditoría.

```php
use DLRoute\Requests\DLRoute;
use DLRoute\Enums\Methods;
use DLRoute\Core\Data\RouteHandler;
use App\Controllers\OrderController;

DLRoute::match(
    methods: [Methods::PUT, Methods::PATCH],
    route: new RouteHandler(
        uri: '/api/orders/{uuid}/update',
        controller: [OrderController::class, 'update'],
        data: ['audit' => true, 'log_level' => 'debug'],
        mime_type: 'application/json',
        handler_filters: ['uuid' => 'uuid']
    )
);

```

---

## 🧠 Filosofía de Diseño: El enfoque "Glass Box"

El diseño directo de `RouteHandler` responde a tres directrices del desarrollo de alto rendimiento y observabilidad:

1. **Eficiencia de Memoria y CPU:** Al prescindir de métodos interceptores (*getters/setters*), el motor Zend de PHP accede directamente a las variables en un tiempo constante $O(1)$. Bajo flujos de tráfico masivo, eliminar miles de llamadas a funciones innecesarias reduce drásticamente la huella en el procesador.
2. **Encapsulamiento Desacoplado:** Al almacenar las restricciones dentro de `$handler_filters` en lugar de aplicarlas inmediatamente, permite que el sistema evalúe y valide la sintaxis de las URIs carácter a carácter con el `RouterLexer` antes de consolidar el árbol de ejecución definitivo.
3. **Coherencia con el Ecosistema:** Actúa como una estructura puramente de transporte de datos, garantizando un acoplamiento nulo con el entorno de ejecución y facilitando su uso tanto en proyectos PHP nativos como bajo la infraestructura del framework **DLUnire**.