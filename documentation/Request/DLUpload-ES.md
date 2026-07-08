# Subida de archivos — `DLUpload`

El trait `DLRoute\Requests\DLUpload` forma parte del controlador base de DLRoute (`DLRoute\Config\Controller`). Los controladores de DLCore (`DLCore\Core\BaseController`) lo heredan automáticamente.

> Tutorial progresivo: [docs/tutorial/12-subida-archivos.md](../../docs/tutorial/12-subida-archivos.md)

## Uso básico

```php
<?php
namespace App\Controllers;

use DLCore\Core\BaseController;

final class AvatarController extends BaseController {
    public function store(): array {
        $this->set_basedir('./uploads/avatars');
        $files = $this->upload_file('avatar', 'image/*');

        return ['uploaded' => $files];
    }
}
```

## Métodos principales

| Método | Descripción |
|--------|-------------|
| `upload_file(string $field, string $type = '*/*')` | Procesa el archivo del campo `$_FILES` |
| `set_basedir(string $path)` | Directorio destino de la subida |
| `set_thumbnail_width(int $width)` | Ancho del thumbnail (imágenes raster) |
| `get_filenames(): array` | Nombres generados en la última subida |
| `get_absolute_path(string $relative)` | Ruta absoluta a partir de una relativa |

## Saneamiento de SVG

Cuando el MIME es `image/svg+xml`, DLRoute **no delega** el saneamiento a librerías externas: lee el archivo en el servidor y lo depura con `sanitize_svg()` antes de guardarlo.

Se eliminan o neutralizan, entre otros:

- Bloques `<script>`
- Atributos de eventos (`onclick`, `onload`, …)
- Atributos inseguros (`eval`, `href` con javascript, etc.)

Esto cubre el flujo estándar de subida en DLUnire y hace redundante declarar `enshrined/svg-sanitize` en DLCore.

## Referencias

- Código fuente: `app/Requests/DLUpload.php`
- Tipos MIME permitidos: `app/Config/FileInfo.php`
- [Petición HTTP (ES)](Request-ES.md)
- [Router (ES)](../Router/Router-ES.md)