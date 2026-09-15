<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

use DLRoute\Server\DLServer;

/**
 * Representa el contexto resuelto de una petición HTTP contra la tabla de rutas registradas.
 *
 * A diferencia del registro de rutas —que almacena controladores indexados por una clave con
 * placeholders (por ejemplo, `/profile/{test}`)—, esta estructura representa el resultado de
 * resolver una petición concreta: el controlador y método a invocar, si la ruta exige sesión
 * válida, y los parámetros dinámicos ya extraídos con sus valores reales.
 *
 * Es una estructura efímera: se construye una única vez por petición y se descarta al finalizar
 * el despacho. No persiste entre peticiones ni se comparte entre instancias concurrentes del
 * enrutador.
 *
 * @package DLRoute\Core\Data\RouteData
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteController {

    /**
     * Método HTTP de la petición actual.
     *
     * Se resuelve automáticamente mediante {@see DLServer::get_method()} durante la
     * construcción de la instancia, sin necesidad de que el llamador lo proporcione.
     *
     * @var string
     */
    public readonly string $http_method;

    /**
     * Construye el contexto resuelto de una petición HTTP.
     *
     * @param string $controller Namespace completo de la clase controladora resuelta
     * para esta petición.
     * @param string $method Nombre del método del controlador a invocar.
     * @param bool $required_auth Indica si la ruta resuelta exigía una sesión válida
     * para llegar a este controlador.
     * @param RouteParam $params Parámetros dinámicos capturados de la ruta, con sus
     * valores ya extraídos de la URI de la petición.
     * 
     * @param array<string, mixed> $vars Variables que serán tomadas por el motor de plantillas, sean las DLUnire
     * o las de otro framework PHP.
     */
    public function __construct(
        public readonly mixed $controller,
        public readonly string $method,
        public readonly bool $required_auth,
        public readonly RouteParam $params,
        public readonly ?string $mimetype = null,
        public readonly array $vars = []
    ) {
        $this->http_method = DLServer::get_method();
    }
}
// {
//     "AUTH-POST-/profile/1200": {
//         "controller": "DLRoute\Test\AuthController",
//         "method": "profile_with_auth",
//         "http_method": "POST",
//         "required_auth": true,
//         "params": {
//             "test": 1200
//         }
//     }
// }