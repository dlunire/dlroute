# `Router` Class Documentation

**Namespace:** `DLRoute\Core\Routing`
**Final Class:** `Router`
**Version:** v0.0.1 (release)
**Author:** David E Luna M
**License:** MIT

---

## Description

The `Router` class is the **primary tool for managing routes and generating absolute URLs** within the application.
It provides methods to:

* Generate absolute URLs from relative routes.
* Obtain detailed telemetry of the currently visited route.

**Important Note:**
`Router` **does not validate route existence or enforce permissions**. Its main purpose is URL construction and retrieving contextual information about the request.

---

## Methods

### `Router::to(string $route = '/'): string`

Generates the full absolute URL to a specific route.

#### Parameters

| Parameter | Type   | Description                                               |
| --------- | ------ | --------------------------------------------------------- |
| `route`   | string | Relative route within the application. Defaults to `'/'`. |

#### Return Value

* `non-empty-string` – The generated absolute URL.

#### Exceptions

* `DLRoute\Errors\RouteException` – Thrown if the route has an invalid format after normalization.

#### Examples

```php id="router-to-example"
use DLRoute\Core\Routing\Router;

// Generate absolute URL to a specific resource
$url = Router::to('/some/path');
echo $url;
// Example output: "https://my-server.com/subdirectory/some/path"

// Get base URL if the route is '/'
$base = Router::to();
echo $base;
// Example output: "https://my-server.com/subdirectory"
```

---

### `Router::from(): RouterData`

Returns full telemetry of the currently visited route.

#### Return Value

* `RouterData` – An object containing detailed information about the request, including:

  * `url` – Full URL of the current request.
  * `ip_client` – Client IP address.
  * `remote_addr` – Remote address from which the request originated.
  * `user_agent` – User agent of the visitor.
  * `scheme` – HTTP protocol (`http` or `https`).
  * `host` – Hostname or domain.
  * `port` – Route port.
  * `local_port` – Local port of the application runtime.
  * `dir` – Application execution directory.
  * `route` – Relative application route.
  * `uri` – Full route including the execution directory.
  * `method` – HTTP method of the request.
  * `time` – Timestamp of the request.

#### Examples

```php id="router-from-example"
use DLRoute\Core\Routing\Router;

$data = Router::from();

echo $data->url;       // Full URL of the current request
echo $data->method;    // HTTP method (GET, POST, etc.)
echo $data->ip_client; // Client IP address
```

---

## Route Normalization

Internally, `Router` normalizes the route before generating the URL:

* Trims whitespace from the start and end of the string.
* Removes repeated or inconsistent slashes (`/` and `\`).
* Ensures the route is in a safe format for concatenation with the base URL.

If the route is not a valid string after normalization, a `RouteException` is thrown.

---

## Best Practices

1. Always provide valid relative routes consistent with your application structure.
2. Use `Router::to()` to generate URLs in templates and links, avoiding hardcoded absolute URLs.
3. Use `Router::from()` to obtain telemetry and debug routes during development or testing.
4. Avoid modifying `Router` properties directly; all interaction should be through public methods.

---

## Full Example

```php id="router-full-example"
use DLRoute\Core\Routing\Router;

# Generate absolute URL
$url = Router::to('/products/123');
echo $url;

# Obtain telemetry of the current request
$info = Router::from();
echo "Current route: {$info->route}\n";
echo "Full URL: {$info->url}\n";
echo "Client IP: {$info->ip_client}\n";
```