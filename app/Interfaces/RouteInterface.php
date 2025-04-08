<?php

namespace DLRoute\Interfaces;

use DLRoute\Requests\DLParamValueType;

/**
 * Sistema de enrutamiento.
 * 
 * @package DLRoute\Interfaces
 * 
 * @version 1.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
interface RouteInterface {

    /**
     * Define una ruta para manejar solicitudes HTTP GET.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP GET.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP GET.
     *
     * @param string $uri El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array|object $data Opcional. Permite implementar datos adicionales al controlador.
     * @param ?string $mime_type Opcional. Permite establecer el tipo MIME de respueta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::get('/user/{id}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::get('/user/{id}', "Ruta\Al\Controlador@metodo);
     * 
     * # O directamente, ejecuta la función:
     * Route::get('/user/{id}', function(object $data) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud GET a '/usuario/123', se invocará el método 'mostrar'
     * de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function get(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType;

    
    /**
     * Define una ruta para manejar solicitudes HTTP POST.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP POST.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP POST.
     *
     * @param string $uri El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array|object $data Opcional. Permite implementar datos adicionales al controlador.
     * @param ?string $mime_type Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::post('/user/create', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::post('/user/create', "Ruta\Al\Controlador@metodo);
     * 
     * # O directamente, ejecuta la función:
     * Route::post('/user/create', function(object $data) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud GET a '/usuario/123', se invocará el método 'mostrar'
     * de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function post(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP PUT.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP PUT.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP PUT.
     *
     * @param string $uri El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array|object $data Opcional. Permite implementar datos adicionales al controlador.
     * @param ?string $mime_type Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::put('/user/update/{uuid}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::put('/user/update/{uuid}', "Ruta\Al\Controlador@metodo);
     * 
     * # O directamente, ejecuta la función:
     * Route::put('/user/update/{uuid}', function(object $data) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud GET a '/usuario/123', se invocará el método 'mostrar'
     * de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function put(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP PATCH. El objeto es llevar a cabo las actualizaciones
     * parciales utilizando este método.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP PATCH.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP PATCH.
     *
     * @param string $uri El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array|object $data Opcional. Permite implementar datos adicionales al controlador.
     * @param ?string $mime_type Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::put('/user/update/{uuid}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::put('/user/update/{uuid}', "Ruta\Al\Controlador@metodo);
     * 
     * # O directamente, ejecuta la función:
     * Route::put('/user/update/{uuid}', function(object $data) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud GET a '/usuario/123', se invocará el método 'mostrar'
     * de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function patch(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP DELETE.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP DELETE.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP DELETE.
     *
     * @param string $uri El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array|object $data Opcional. Permite implementar datos adicionales al controlador.
     * @param ?string $mime_type Opcional. Permite establecer el tipo MIME que será devuelto al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::delete('/user/delete/{uuid}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::delete('/user/delete/{uuid}', "Ruta\Al\Controlador@metodo);
     * 
     * # O directamente, ejecuta la función:
     * Route::delete('/user/delete/{uuid}', function(object $data) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud GET a '/usuario/123', se invocará el método 'mostrar'
     * de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function delete(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType;
}
