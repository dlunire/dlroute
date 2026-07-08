# 15 — Despliegue en producción

DLRoute detecta subdirectorios y construye `base_url` sin configuración manual, pero el servidor web, PHP-FPM y los proxies deben exponer correctamente `$_SERVER` para que rutas, HTTPS e IP sean fiables.

## Punto de entrada

El document root del vhost debe apuntar a `public/`:

```
/var/www/mi-app/public/index.php
```

```php
<?php
declare(strict_types=1);

define('DOCUMENT_ROOT', dirname(__DIR__));

use DLRoute\Requests\DLRoute;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/routes/web.php';

DLRoute::execute();
```

`define('DOCUMENT_ROOT', …)` evita ambigüedad cuando `getcwd()` no es la raíz del proyecto ([03-dlserver-contexto.md](03-dlserver-contexto.md)).

## Apache

```apache
<VirtualHost *:443>
    ServerName api.ejemplo.com
    DocumentRoot /var/www/mi-app/public

    <Directory /var/www/mi-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Reescritura hacia index.php
    FallbackResource /index.php
</VirtualHost>
```

### `Authorization` hacia PHP

Si `HTTP_AUTHORIZATION` no llega a PHP (común con Bearer tokens):

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

Relevante cuando DLCore valida `DL_TOKEN` encima de DLRoute ([tutorial DLCore — despliegue](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/22-despliegue-produccion.md)).

## Nginx

```nginx
server {
    listen 443 ssl;
    server_name api.ejemplo.com;
    root /var/www/mi-app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Proxy inverso y HTTPS

Detrás de Nginx, Cloudflare o un balanceador, configura:

```nginx
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
proxy_set_header Host $host;
```

`DLHost::is_https()` y la telemetría (`is_https`, `port`, `proxy`) dependen de estas cabeceras ([11-router-telemetria.md](11-router-telemetria.md)).

## Subdirectorios

App en `https://example.com/mi-app/public/`:

| Campo DLServer | Ejemplo |
|----------------|---------|
| `uri` | `mi-app/public/api/status` |
| `route` | `/api/status` |
| `base_url` | `https://example.com/mi-app/public` |

Usa `Router::to('/api/status')` para enlaces correctos sin hardcodear el prefijo.

## PHP.ini recomendado (producción)

```ini
display_errors = Off
log_errors = On
upload_max_filesize = 32M
post_max_size = 32M
session.cookie_httponly = 1
```

Para subidas: [12-subida-archivos.md](12-subida-archivos.md).

## Checklist pre-producción

| Ítem | Verificación |
|------|--------------|
| `execute()` al final de `index.php` | Una sola llamada |
| `DOCUMENT_ROOT` definido | Rutas a `storage/`, uploads |
| TLS terminado correctamente | `DLHost::is_https()` true |
| IP real del cliente | `X-Forwarded-For` o equivalente |
| Rutas debug deshabilitadas | Sin `/__debug/*` |
| 404 personalizado | `DLOutput` si aplica |
| CORS / auth | Capa DLCore si usas DLUnire |

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 404 en todas las rutas | `try_files` incorrecto | Redirige a `index.php` |
| URLs con `http` en HTTPS | Falta `X-Forwarded-Proto` | Configura proxy |
| `document_root` en `vendor/` | `getcwd()` en FPM | `define('DOCUMENT_ROOT')` |
| Subidas fallan | Permisos o límites PHP | `upload_max_filesize`, chmod `storage/` |

## Siguiente paso

Bootstrap DLUnire, `BaseController` y capas DLCore en [16-integracion-dlcore.md](16-integracion-dlcore.md).