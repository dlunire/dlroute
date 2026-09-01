# Controladores en DLRoute

Los controladores permiten encapsular la lógica asociada a las rutas de la aplicación. Para crear un controlador, extienda la clase `Controller` de `DLRoute`.

## Crear un controlador

Un controlador puede utilizarse sin declarar un constructor propio. En ese caso, se utilizará automáticamente el constructor de `Controller` con sus valores predeterminados.

```php
<?php

use DLRoute\Config\Controller;

final class UserController extends Controller {

}
```

Por defecto, el sistema de autenticación no se habilita.

## Habilitar la autenticación

Cuando un controlador requiere autenticación, debe invocar el constructor de `Controller` indicando `auth: true`.

```php
<?php

use DLRoute\Config\Controller;

final class AuthController extends Controller {

    public function __construct() {
        parent::__construct(auth: true);
    }
}
```

También es posible especificar un campo personalizado para almacenar los datos de sesión:

```php
<?php

use DLRoute\Config\Controller;

final class AuthController extends Controller {

    public function __construct() {
        parent::__construct(
            auth: true,
            field: 'mi-campo-personalizado'
        );
    }
}
```

Si se especifica `field`, este debe contener una cadena no vacía.

## Acceder al autenticador

Una vez habilitada la autenticación, puede obtener la instancia de `AuthApps` mediante `get_auth()`:

```php
$auth = $this->get_auth();
```

El método valida que el sistema de autenticación haya sido configurado. Si no está habilitado, se genera una `RuntimeException`.

## Guardar datos de autenticación

Para almacenar datos en la sesión de autenticación, utilice `save_auth_data()`:

```php
$this->save_auth_data([
    'user_id' => 100,
    'username' => 'usuario'
]);
```

El método devuelve la instancia de `AuthApps`, por lo que también permite continuar trabajando directamente con el autenticador:

```php
$auth = $this->save_auth_data([
    'user_id' => 100
]);
```

De esta forma, un controlador puede mantenerse completamente simple cuando no requiere autenticación y habilitarla explícitamente únicamente cuando la aplicación lo necesita.
