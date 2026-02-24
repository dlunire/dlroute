# Sending Requests to a Remote Server

Classes and methods related to configuring and sending HTTP requests from a controller in a PHP application.

---

## Class `RequestInit`

This class is used to configure the details of an HTTP request, such as the body, method, and headers.

### Method `RequestInit::set_body(array $body): void`

This method is used to set the body or data of the HTTP request.

**Parameters:**

* `$body`: An array containing the data to be sent in the request body.

**Return value:**
This method does not return any value (`void`).

**Example usage:**

```php
$request = new RequestInit();
$request->set_body(['key' => 'value']);
```

---

### Method `RequestInit::set_method(string $method): void`

This method is used to set the HTTP method for the request (GET, POST, PUT, etc.).

**Parameters:**

* `$method`: The HTTP method to be used in the request.

**Return value:**
This method does not return any value (`void`).

**Example usage:**

```php
$request = new RequestInit();
$request->set_method(self::GET);
```

---

## Class `HeadersInit`

This class is used to configure the headers of an HTTP request.

### Method `RequestInit::set_headers(HeadersInit $headers): void`

This method is used to set the headers for the HTTP request.

**Parameters:**

* `$headers`: An instance of the `HeadersInit` class containing the headers to be applied to the request.

**Return value:**
This method does not return any value (`void`).

**Example usage:**

```php
$request = new RequestInit();
$headers = new HeadersInit();
$headers->set('Accept', '*/*');
$request->set_headers($headers);
```

---

## Method `self::fetch(string $url, RequestInit $request): string|bool`

This method is used to send an HTTP request to the specified server and obtain the response.

**Parameters:**

* `$url`: The URL of the server to which the request will be sent.
* `$request`: An instance of the `RequestInit` class containing the request configuration.

**Return value:**

* Returns the server response as a string if the request was successful.
* Returns `false` if there was any error during the request process.

**Example usage:**

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

---

These classes and methods provide a convenient way to configure and send HTTP requests from a controller in a PHP application, allowing effective interaction with remote servers.