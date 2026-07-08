# 12 — Subida de archivos (`DLUpload`)

El trait `DLRoute\Requests\DLUpload` procesa `$_FILES`, valida MIME, genera nombres seguros, crea thumbnails en imágenes raster y **sanea SVG** antes de guardar.

Está incluido en `DLRoute\Config\Controller`; `DLCore\Core\BaseController` lo hereda automáticamente.

## Uso básico

```php
<?php
namespace App\Controllers;

use DLRoute\Config\Controller;

final class AvatarController extends Controller {

    public function store(): array {
        $this->set_basedir('./uploads/avatars');
        $files = $this->upload_file('avatar', 'image/*');

        return [
            'uploaded' => array_map(fn ($f) => $f->get_filename(), $files),
        ];
    }
}
```

Formulario HTML:

```html
<form method="post" action="/avatars" enctype="multipart/form-data">
    <input type="file" name="avatar" accept="image/*">
    <button type="submit">Subir</button>
</form>
```

Ruta:

```php
DLRoute::post('/avatars', [AvatarController::class, 'store']);
```

## Métodos principales

| Método | Descripción |
|--------|-------------|
| `set_basedir(string $path)` | Directorio destino (relativo al proyecto) |
| `upload_file(string $field, string $type = '*/*')` | Procesa el campo de `$_FILES` |
| `set_thumbnail_width(int $width)` | Ancho de miniatura (raster) |
| `get_filenames(): array` | Nombres generados en la última subida |
| `get_absolute_path(string $relative)` | Ruta absoluta en disco |

## Filtro por tipo MIME

```php
$this->upload_file('document', 'application/pdf');
$this->upload_file('photo', 'image/jpeg');
$this->upload_file('any', '*/*');
```

`FileInfo` valida tipos permitidos. Tipos no admitidos se descartan en el flujo de filtrado.

## Saneamiento de SVG

Cuando el archivo es `image/svg+xml`, DLRoute ejecuta `sanitize_svg()` **en el servidor** antes de persistir:

- Elimina bloques `<script>`
- Neutraliza atributos de eventos (`onclick`, `onload`, …)
- Limpia atributos inseguros (`javascript:` en `href`, etc.)

No depende de `enshrined/svg-sanitize` ni de paquetes externos. Es el flujo estándar de DLUnire para subidas SVG.

```php
$this->set_basedir('./uploads/icons');
$this->upload_file('icon', 'image/svg+xml');
```

## Thumbnails

```php
$this->set_basedir('./uploads/products');
$this->set_thumbnail_width(400);
$this->upload_file('image', 'image/png');
```

Aplica a imágenes raster compatibles con GD; no genera thumbnail de SVG.

## Seguridad operativa

1. Registra la ruta POST dentro de bloques autenticados si aplica ([DLCore — DLAuth](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/27-dlauth-rutas.md)).
2. Limita MIME con el segundo argumento de `upload_file()`, no solo `accept` en HTML.
3. Guarda fuera de `public/` si no deben ser URLs directas; sirve vía controlador.
4. En producción, revisa permisos de `set_basedir` y cuotas de disco.

## Respuesta típica

```php
public function store(): array {
    $this->set_basedir('./storage/uploads');
    $files = $this->upload_file('file', 'image/*');

    if (count($files) === 0) {
        http_response_code(422);
        return ['error' => 'Archivo no válido o ausente'];
    }

    return ['files' => $this->get_filenames()];
}
```

## Referencias

- Código: `app/Requests/DLUpload.php`
- MIME: `app/Config/FileInfo.php`
- [DLUpload-ES.md](../../documentation/Request/DLUpload-ES.md)

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Array vacío tras subida | MIME rechazado o campo mal nombrado | Revisa `name` del input y tipo |
| SVG rechazado tras sanear | Contenido no recuperable | Valida en cliente antes de enviar |
| Archivo no en disco | `basedir` incorrecto | Usa `get_absolute_path()` para verificar |
| 413 / body truncado | Límite PHP/servidor | Aumenta `upload_max_filesize` en php.ini |

## Siguiente paso

Cliente HTTP saliente con el trait `Request` en [13-peticiones-salientes.md](13-peticiones-salientes.md).