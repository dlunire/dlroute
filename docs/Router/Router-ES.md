# Documentación de la clase `Router`

**Namespace:** `DLRoute\Core\Routing`
**Clase final:** `Router`
**Versión:** v0.0.1 (release)
**Autor:** David E Luna M
**Licencia:** MIT

---

## Descripción

La clase `Router` es la **herramienta principal para gestionar rutas y generar URLs absolutas** dentro de la aplicación.
Proporciona métodos para:

* Generar URLs absolutas a partir de rutas relativas.
* Obtener telemetría detallada de la ruta actualmente visitada.

**Nota importante:**
`Router` **no valida la existencia de rutas ni controla permisos**. Su objetivo principal es construcción de URLs y obtención de información contextual de la petición.

---

## Métodos

### `Router::to(string $route = '/'): string`

Genera la URL absoluta completa hacia una ruta específica.

#### Parámetros

| Parámetro | Tipo   | Descripción                                               |
| --------- | ------ | --------------------------------------------------------- |
| `route`   | string | Ruta relativa dentro de la aplicación. Por defecto `'/'`. |

#### Retorno

* `non-empty-string` – URL absoluta generada.

#### Excepciones

* `DLRoute\Errors\RouteException` – Si la ruta tiene un formato inválido tras normalizarla.

#### Ejemplos

```php
use DLRoute\Core\Routing\Router;

// Generar URL absoluta hacia un recurso específico
$url = Router::to('/alguna/ruta');
echo $url;
// Ejemplo de salida: "https://mi-servidor.com/subdirectorio/alguna/ruta"

// Obtener URL base si la ruta es '/'
$base = Router::to();
echo $base;
// Ejemplo de salida: "https://mi-servidor.com/subdirectorio"
```

---

### `Router::from(): RouterData`

Devuelve telemetría completa de la ruta actualmente visitada.

#### Retorno

* `RouterData` – Objeto con información detallada de la petición, incluyendo:

  * `url` – URL completa de la petición actual.
  * `ip_client` – Dirección IP del cliente.
  * `remote_addr` – Dirección remota desde donde se hace la petición.
  * `user_agent` – Agente de usuario del visitante.
  * `scheme` – Protocolo HTTP (`http` o `https`).
  * `host` – Nombre de host o dominio.
  * `port` – Puerto de la ruta.
  * `local_port` – Puerto local de ejecución de la aplicación.
  * `dir` – Directorio de ejecución de la aplicación.
  * `route` – Ruta relativa de la aplicación.
  * `uri` – Ruta completa incluyendo el directorio de ejecución.
  * `method` – Método HTTP de la petición.
  * `time` – Marca temporal de la petición.

#### Ejemplos

```php
use DLRoute\Core\Routing\Router;

$data = Router::from();

echo $data->url;       // URL completa de la petición actual
echo $data->method;    // Método HTTP (GET, POST, etc.)
echo $data->ip_client; // Dirección IP del cliente
```

---

## Normalización de rutas

Internamente, `Router` normaliza la ruta antes de generar la URL:

* Elimina espacios en blanco al inicio y final de la cadena.
* Elimina barras repetidas o inconsistentes (`/` y `\`).
* Garantiza que la ruta quede en un formato seguro para concatenar con la URL base.

Si la ruta no es una cadena válida después de normalizarla, se lanza `RouteException`.

---

## Buenas prácticas

1. Siempre pasar rutas relativas válidas y consistentes con tu estructura de aplicación.
2. Usar `Router::to()` para generar URLs en plantillas y enlaces, evitando hardcodear URLs absolutas.
3. Utilizar `Router::from()` para obtener telemetría y depurar rutas en desarrollo o pruebas.
4. Evitar modificar directamente propiedades de `Router`; toda interacción debe hacerse mediante los métodos públicos.

---

## Ejemplo completo

```php
use DLRoute\Core\Routing\Router;

# Generar URL absoluta
$url = Router::to('/productos/123');
echo $url;

# Obtener telemetría de la petición actual
$info = Router::from();
echo "Ruta actual: {$info->route}\n";
echo "URL completa: {$info->url}\n";
echo "IP del cliente: {$info->ip_client}\n";
```