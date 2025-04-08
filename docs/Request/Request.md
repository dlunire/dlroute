# Enviar peticiones a un servidor remoto

Clases y métodos relacionados con la configuración y envío de solicitudes HTTP desde un controlador en una aplicación PHP.

## Clase `RequestInit`

Esta clase se utiliza para configurar los detalles de una solicitud HTTP, como el cuerpo, el método y las cabeceras.

### Método `RequestInit::set_body(array $body): void`

Este método se utiliza para establecer el cuerpo o datos de la solicitud HTTP.

**Parámetros:**

- `$body`: Un array que contiene los datos que se enviarán en el cuerpo de la solicitud.

**Valor de retorno:**
Este método no retorna ningún valor (`void`).

**Ejemplo de uso:**

```php
$request = new RequestInit();
$request->set_body(['key' => 'value']);
```

### Método `RequestInit::set_method(string $method): void`

Este método se utiliza para establecer el método HTTP de la solicitud (GET, POST, PUT, etc.).

**Parámetros:**

- `$method`: El método HTTP que se utilizará en la solicitud.

**Valor de retorno:**
Este método no retorna ningún valor (`void`).

**Ejemplo de uso:**

```php
$request = new RequestInit();
$request->set_method(self::GET);
```

## Clase `HeadersInit`

Esta clase se utiliza para configurar las cabeceras de una solicitud HTTP.

### Método `RequestInit::set_headers(HeadersInit $headers): void`

Este método se utiliza para establecer las cabeceras de la solicitud HTTP.

**Parámetros:**

- `$headers`: Una instancia de la clase `HeadersInit` que contiene las cabeceras a ser configuradas en la solicitud.

**Valor de retorno:**
Este método no retorna ningún valor (`void`).

**Ejemplo de uso:**

```php
$request = new RequestInit();
$headers = new HeadersInit();
$headers->set('Accept', '*/*');
$request->set_headers($headers);
```

## Método `self::fetch(string $url, RequestInit $request): string|bool`

Este método se utiliza para enviar una solicitud HTTP al servidor especificado y obtener la respuesta.

**Parámetros:**

- `$url`: La URL del servidor al que se enviará la solicitud.
- `$request`: Una instancia de la clase `RequestInit` que contiene la configuración de la solicitud.

**Valor de retorno:**

- Retorna la respuesta del servidor como una cadena de caracteres si la solicitud fue exitosa.
- Retorna `false` si hubo algún error durante el proceso de la solicitud.

**Ejemplo de uso:**

```php
$request = new RequestInit();
$headers = new HeadersInit();

$headers->set('Accept', '*/*');
$headers->set('Authorization', "Bearer {$token}");

$request->set_headers($headers);
$request->set_method(Request::GET);

$action = 'https://api.example.com/data';
$response = $this->fetch($action, $request);
```

Estas clases y métodos proporcionan una manera conveniente de configurar y enviar solicitudes HTTP desde un controlador en una aplicación PHP, permitiendo así interactuar con servidores remotos de manera efectiva.
