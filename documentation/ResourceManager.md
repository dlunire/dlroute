# Clase ResourceManager

## Métodos de la clase | Estilos

### Método **`ResourceManager::css`**

**Sintaxis:**

```php
ResourceManager::css(string $path, bool $external = false): string
```

**Parámetros:**

- **`$path`:** Es la ruta relativa al archivo.

- **`$external`:** Si vale `false` (valor por defecto) significa que no se tomará como un archivo externo, por lo tanto, su contenido se incorpará directamente en una salida HTML con las etiquetas `<style>...</style>`. Si vale `true`, entonces, el contenido del archivo CSS será la salida, con el objeto de usarse en una ruta amigable.

Por ejemplo, las siguientes líneas:

```php
$css = ResourceManager::css('/ruta/al/archivo.css');
echo $css;
```

Devolverá una salida similar a la siguiente:

```html
<style>.clase {
    background-color: silver;
    color: black;
}</style>
```

Sin embargo, si se pasa como segundo argumento el valor `true`, por ejemplo:

```php
$css = ResourceManager::css('/ruta/al/archivo.css', true);
echo $css;
```

Devolverá la siguiente salida:

```css
.clase {
    background-color: silver;
    color: black;
}
```

Para que pueda incorporarse en una ruta amigable, como por ejemplo:

```html
<link rel="stylesheet" href="http://localhost/bundle/css/archivo?b64eaa1dbfbe0751d41b7746aad28ea34af155e3c844f51f68aeebab08989fb2" />
```

Esto está pensando para ser utilizando en el _mini-framework_ `DLUnire`.

Tome en cuenta que puede utilizar la ruta de esta forma también:

```php
$css = ResourceManager::css('ruta.al.archivo', true);
echo $css;
```

Dando el mismo resultado:

```css
.clase {
    background-color: silver;
    color: black;
}
```

Donde cada punto (`.`) será transformado automáticamente en una barra diagonal (`/`). Por otra parte, no necesita agregar la extensión al archivo, ya que será agregada automáticamente.

## Métodos de la clase ResourseManager | JavaScript

### Método **`ResourceManager::js`**

**Sintaxis:**

```php
ResourceManager::js(string $path, ?array $options = []): string
```

**Parámetros:**

- **`$path`:** Es la ruta relativa del archivo al archivo JavaScript.

- **`$options`:** Es un array asociativo que contiene las siguientes claves:

  - **`external`:** Si su valor es `false` se espera que se incorpore código JavaScript directamente en una salida HTML (comportamiento por defecto) con las etiquetas `<script...></script>` incluidas; o simplemente, si vale `true`, devuelve código JavaScript directamente sin usar las etiquetas antes mencionada con el objeto de que pueda utilizarse en una ruta amigable.
  
  - **`behavior_attributes`:** Permite indicar si el _script_ se carga en modo diferido `defer` o asíncrono `async`. Si `external` vale `true` esta opción no tendrá efecto.
  
  - **`type`:** Permite indicar si el archivo JavaScript es un módulo `type="module"` o no. NO tiene efecto si `external` es `true`.

  - **`token`:** Permite establecer un _token_ de seguridad para garantizar que solo se ejecuten _scripts_ que contenga dicho token. El _token_ debe ser aleatorio. De hecho, está pensado para usarse en el _mini-framework_ **DLUnire**. No tiene efecto si `external` es `true`.

Ejemplo de uso:

```php
$test = ResourceManager::js('tests.test', [
    "external" => true,
    "type" => "module",
    "token" => hash('sha256', 'Contenido del token'),
    "behavior_attributes" => 'defer'
]);
```

Por defecto, incorpora código JavaScript directamente en una salida HTML; por ejemplo:

```php
$js = ResourceManager::js('ruta.al.archivo');
echo $js;
```

Produciendo una salida similar a esto:

```html
<script>
console.log({ test: "Archivo con contenido de prueba" });
console.log({ test: "Esta es otra prueba" });
</script>
```

Pero si queremos incorporar código JavaScript directamente, sin etiquetas HTML, entonces, podríamos indicarlo en `$config`, como por ejemplo:

```php
$js = ResourceManager::js('ruta.al.archivo', [
    $external => true
]);

echo $js;
```

Dando como resultado, la siguiente salida:

```js
console.log({ test: "Archivo con contenido de prueba" });
console.log({ test: "Esta es otra prueba" });
```

Está pensado para implementarse en rutas amigables.

> Esto está pensado para usarse en el _micro-framework_ `DLUnire`

## Método `ResourceManager::image`

Procesa las imágenes directamente en base64.

**Sintaxis:**

```php
ResourceManager::image(string $filename, object|array|null $config = null): string|false;
```

**Parámetros:**

- **`$filename`:** Ruta de la imagen.
- **`$config`:** Permite indicar si la imagen se presenta como código HTML o directamente como un archivo binario.
  - **`title`:** Título de la imagen.
  - **`html`:** Permite indicar si la imagen debe presentarse como código HTML o no.

Por ejemplo, si la imagen la queremos obtener como código HTML:

```php
$test = ResourceManager::image('tests.test.test', [
    "html" => false,
    "title" => "Título de la imagen",
]);

echo $test;
```

O directamente, como archivo binario:

```php
$test = ResourceManager::image('tests.test.jpg', [
    "html" => true,
    "title" => "Título de la imagen",
]);

echo $test;
```

Si en el parámetron `$config` `html` es `false`, la clave o propiedad `title` no tendrá efecto, por lo tanto, usar de esta forma:

```php
$test = ResourceManager::image('tests.test.jpg', [
    "html" => true,
]);

echo $test;
```

O directamente:

```php
$test = ResourceManager::image('tests.test.jpg');
echo $test;
```

Si la imagen no tiene extensión, el método `ResourceManager::image` sabrá si es o no una imagen. Si el archivo de imagen no existe o no es una imagen, simplemente, devolverá `false`:

## Método `ResourceManager::asset`

Establece la URL completa del recurso a partir de la ruta dle archivo. En este caso, los recursos deben encontrarse en el directorio `public/` o cualquier otro directorio que pueda ser accedido desde el protocolo **HTTP**.

**Sintaxis:**

```php
ResourceManager::asset(string $filename): string;
```

**Parámetros:**

- **`$filename`:** Ruta del archivo o recurso.

<!-- vanna.ai -->