<?php

/**
 * DLUnire
 * Copyright (C) 2026 David E Luna M
 *
 * Operando bajo el establecimiento de comercio "DLUnire",
 * NIT 700551569-1, matrícula mercantil Nº 10007069
 * (matrícula mercantil personal Nº 10007068).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

namespace DLRoute\Interfaces;

use DLRoute\Core\Data\RouteData\RouteParam;
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Enums\Methods;
use DLRoute\Errors\RouteException;
use DLRoute\Requests\DLParamValueType;

/**
 * Sistema de enrutamiento.
 * 
 * @package DLRoute\Interfaces
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2023 David E Luna M
 * @license AGPL-3.0 license
 */
interface RouteInterface {

    /**
     * Define la ruta para manejar solicitudes HTTP utilizando el método `QUERY`.
     * 
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida utilizando
     * el método HTTP QUERY.
     * 
     * A diferencia de `GET` y `HEAD`, el método `QUERY` sí admite `body` en la petición —tanto en el
     * navegador mediante `fetch()` como en el backend, que acepta formulario, texto crudo o JSON sin
     * importar el método HTTP utilizado.
     * 
     * @example
     * 
     * ```
     * <?php
     * # Apunta a un controlador usando un array:
     * Route::query('/user/{id}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::query('/user/{id}', "Ruta\Al\Controlador@metodo");
     * 
     * # O directamente, ejecuta la función:
     * Route::query('/user/{id}', function(RouteParam $params) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * @param string $route Patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller Controlador encargado de manejar la solicitud. Puede ser un callback o controlador.
     * @param array<string,mixed> $varnames Opcional. Permite implementar variables en el motor de plantillas.
     * @param string|null $mimetype Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * @return DLParamValueType
     */
    public static function query(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP GET.
     *
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP GET.
     *
     * El navegador no permite enviar `body` junto con el método `GET` mediante `fetch()` (lanza
     * `TypeError: Request with GET/HEAD method cannot have body`); los datos de una solicitud `GET`
     * deben viajar en la propia URI, mediante parámetros dinámicos o cadena de consulta.
     *
     * @param string $route El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array<string,mixed> $varnames Opcional. Permite implementar variables en el motor de plantillas.
     * @param ?string $mimetype Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::get('/user/{id}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::get('/user/{id}', "Ruta\Al\Controlador@metodo");
     * 
     * # O directamente, ejecuta la función:
     * Route::get('/user/{id}', function(RouteParam $params) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud GET a '/usuario/123', se invocará el método 'mostrar'
     * de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function get(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes `HTTP HEAD`.
     * 
     * Permite definir una ruta para manejar solicitudes `HTTP HEAD`. El callback o controlador
     * proporcionado se ejecutará cuando la URI definida sea accedida utilizando
     * el método `HTTP HEAD`.
     * 
     * Al igual que `GET`, el navegador no permite enviar `body` junto con el método `HEAD` mediante
     * `fetch()` — la misma restricción del navegador aplica a ambos métodos.
     * 
     * @param string $route Patrón URI que se comparará con las solicitudes entrantes
     * @param callable|array|string $controller `callback` o controlador encargado de manejar la solicitud
     * @param array<string,mixed> $varnames Permite implementar datos adicionales al controlador.
     * @param mixed $mimetype Permite establecer el tipo MIME de respuesta al cliente.
     * @return DLParamValueType
     */
    public static function head(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;


    /**
     * Define una ruta para manejar solicitudes HTTP POST.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP POST.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP POST.
     *
     * A diferencia de `GET` y `HEAD`, el método `POST` sí admite `body` en la petición —tanto en el
     * navegador mediante `fetch()` como en el backend, que acepta formulario, texto crudo o JSON.
     *
     * @param string $route El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array<string,mixed> $varnames Opcional. Permite implementar variables en el motor de plantillas.
     * @param ?string $mimetype Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::post('/user/create', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::post('/user/create', "Ruta\Al\Controlador@metodo");
     * 
     * # O directamente, ejecuta la función:
     * Route::post('/user/create', function(RouteParam $params) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud POST a '/user/create', se invocará el método
     * 'mostrar' de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function post(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP PUT.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP PUT.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP PUT.
     *
     * A diferencia de `GET` y `HEAD`, el método `PUT` sí admite `body` en la petición —tanto en el
     * navegador mediante `fetch()` como en el backend, que acepta formulario, texto crudo o JSON.
     *
     * @param string $route El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array<string,mixed> $varnames Opcional. Permite implementar variables en el motor de plantillas.
     * @param ?string $mimetype Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::put('/user/update/{uuid}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::put('/user/update/{uuid}', "Ruta\Al\Controlador@metodo");
     * 
     * # O directamente, ejecuta la función:
     * Route::put('/user/update/{uuid}', function(RouteParam $params) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud PUT a '/user/update/{uuid}', se invocará el método
     * 'mostrar' de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function put(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP PATCH. El objetivo es llevar a cabo actualizaciones
     * parciales utilizando este método.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP PATCH.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP PATCH.
     *
     * A diferencia de `GET` y `HEAD`, el método `PATCH` sí admite `body` en la petición —tanto en el
     * navegador mediante `fetch()` como en el backend, que acepta formulario, texto crudo o JSON.
     *
     * @param string $route El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array<string,mixed> $varnames Opcional. Permite implementar variables en el motor de plantillas.
     * @param ?string $mimetype Opcional. Permite establecer el tipo MIME de respuesta al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::patch('/user/update/{uuid}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::patch('/user/update/{uuid}', "Ruta\Al\Controlador@metodo");
     * 
     * # O directamente, ejecuta la función:
     * Route::patch('/user/update/{uuid}', function(RouteParam $params) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud PATCH a '/user/update/{uuid}', se invocará el
     * método 'mostrar' de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function patch(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes HTTP DELETE.
     *
     * Este método te permite definir una ruta para manejar solicitudes HTTP DELETE.
     * El callback o controlador proporcionado se ejecutará cuando la URI definida sea accedida
     * utilizando el método HTTP DELETE.
     *
     * A diferencia de `GET` y `HEAD`, el método `DELETE` sí admite `body` en la petición —tanto en el
     * navegador mediante `fetch()` como en el backend, que acepta formulario, texto crudo o JSON.
     *
     * @param string $route El patrón de URI que se comparará con las solicitudes entrantes.
     * @param callable|array|string $controller El callback o controlador encargado de manejar la solicitud.
     * @param array<string,mixed> $varnames Opcional. Permite implementar variables en el motor de plantillas.
     * @param ?string $mimetype Opcional. Permite establecer el tipo MIME que será devuelto al cliente.
     * 
     * @return DLParamValueType
     *
     * @example
     * ```
     * # Apunta a un controlador usando un array:
     * Route::delete('/user/delete/{uuid}', [ControladorUsuario::class, 'mostrar']);
     * 
     * # Apunta al controlador utilizando una cadena de texto:
     * Route::delete('/user/delete/{uuid}', "Ruta\Al\Controlador@metodo");
     * 
     * # O directamente, ejecuta la función:
     * Route::delete('/user/delete/{uuid}', function(RouteParam $params) {
     *  // Lógica para el usuario.
     * });
     * ```
     *
     * En el ejemplo anterior, cuando se realiza una solicitud DELETE a '/user/delete/{uuid}', se invocará el
     * método 'mostrar' de la clase 'ControladorUsuario' para manejar la solicitud.
     */
    public static function delete(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Define una ruta para manejar solicitudes `HTTP OPTIONS`.
     * 
     * Permite definir una ruta para manejar solicitudes `HTTP OPTIONS`. El callback o controlador
     * proporcionado se ejecutará cuando la URI definida sea accedida utilizando
     * el método `HTTP OPTIONS`.
     * 
     * A diferencia de `GET` y `HEAD`, el método `OPTIONS` sí admite `body` en la petición mediante
     * `fetch()`, aunque en la práctica su uso habitual es de preflight de CORS, sin cuerpo relevante.
     * 
     * @param string $route Patrón URI que se comparará con las solicitudes entrantes
     * @param callable|array|string $controller `callback` o controlador encargado de manejar la solicitud
     * @param array<string,mixed> $varnames Opcional. Permite implementar nombre de variables en el motor de plantillas.
     * @param mixed $mimetype Permite establecer el tipo MIME de respuesta al cliente.
     * @return DLParamValueType
     */
    public static function options(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType;

    /**
     * Registra de forma masiva múltiples métodos HTTP para una misma ruta de petición.
     *
     * Este método permite vincular una colección de verbos HTTP (definidos a través del enum Methods)
     * a una única configuración de ruta (`RouteHandler`). Valida la integridad de los datos de entrada,
     * resuelve dinámicamente el método de registro correspondiente de la clase e integra de manera fluida
     * los filtros definidos si la ruta requiere validaciones por tipo de datos.
     *
     * @param Methods[]    $methods Lista de métodos HTTP (instancias de `DLRoute\Enums\Methods`) a registrar.
     * @param RouteHandler $route   Objeto contenedor con la configuración de la URI, controlador, tipos MIME y filtros.
     * @return void
     * 
     * @throws RouteException Si el array `$methods` está vacío (Código 500).
     * @throws RouteException Si alguno de los elementos del array `$methods` no es una instancia válida de `Methods`.
     * @see \DLRoute\Enums\Methods
     * @see \DLRoute\Core\Data\RouteHandler
     */
    public static function match(array $methods, RouteHandler $route): void;
}