<?php

namespace DLRoute\Interfaces;

/**
 * Debe implementarse de forma obligatoria los métodos `get` y `post`
 * en las clases donde se utilicen esta interface.
 * 
 * @package Trading\Interfaces
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 */
interface RequestInterface {

    /**
     * Valida si las peticiones hechas por el método GET son correctas1
     * 
     * Es decir, lo puede hacer de esta forma:
     * 
     * ```
     * $params = [
     *  "campo1" => true,
     *  "campo2" => false
     * ];
     * 
     * if ($request->get($params)) {
     *  # Instrucciones a ejecutar si son válidas.
     * }
     * ```
     * Donde `"campo1" => true` significa que el campo es requerido, y `false`, lo contrario.
     * @param array $params
     * @return boolean
     */
    public function get(array $params): bool;

    /**
     * Valida si las peticiones hechas por el método POST son correctas
     * 
     * Es decir, lo puede hacer de esta forma:
     * 
     * ```
     * $params = [
     *  "campo1" => true,
     *  "campo2" => false
     * ];
     * 
     * if ($request->post($params)) {
     *  # Instrucciones a ejecutar si son válidas.
     * }
     * ```
     * Donde `"campo1" => true` significa que el campo es requerido, y `false`, lo contrario.
     * @param array $params
     * @return boolean
     */
    public function post(array $params): bool;

    /**
     * Valida si los parámetros de la petición hecha por el método HTTP PUT son válidas.
     * 
     * Puede validar de la siguiente manera:
     * 
     * ```
     * $params = [
     *  "campo1" => true,
     *  "campo2" => false
     * ];
     * 
     * if ($request->put($params)) {
     *  # Lógica a ejecutar si los parámetros son válidos.
     * }
     * ```
     * 
     * ### Importante
     * 
     * Tome en cuenta que si cualquiera de los campos vale `true` significa que es requerido. En el caso contrario,
     * no se considera requerido, es decir, puede ir sin contenido.
     * 
     * @param array $params
     * @return boolean
     */
    public function put(array $params): bool;

    /**
     * Valida si los parámetros de la petición hecha por el método HTTP DELETE son válidas.
     * 
     * Puede validar de la siguiente manera:
     * 
     * ```
     * $params = [
     *  "campo1" => true,
     *  "campo2" => false
     * ];
     * ```
     *
     * ### Importante
     * 
     * Tome en cuenta que si cualquiera de los campos vale `true`, es requerido. En el caso contrario,
     * no se considera requerido, es decir, puede ir vacío.
     * 
     * @param array $params
     * @return boolean
     */
    public function delete(array $params): bool;

    /**
     * Ejecuta el controlador asociado al método HTTP GET.
     *
     * Esta función ejecuta el controlador proporcionado cuando se recibe una solicitud GET.
     *
     * @param array $params Los parámetros de la solicitud.
     * @param callable|array $controller El controlador que se ejecutará.
     * @param string|null $mime_type (Opcional) El tipo MIME de la respuesta.
     * @return void
     */
    public function execute_get_method(array $params, callable|array $controller, ?string $mime_type = null): void;


    /**
     * Ejecuta el controlador asociado al método HTTP POST.
     *
     * Esta función ejecuta el controlador proporcionado cuando se recibe una solicitud POST.
     *
     * @param array $params Los parámetros de la solicitud.
     * @param callable|array $controller El controlador que se ejecutará.
     * @param string|null $mime_type (Opcional) El tipo MIME de la respuesta.
     * @return void
     */
    public function execute_post_method(array $params, callable|array $controller, ?string $mime_type = null): void;

    /**
     * Ejecuta el controlador asociado al método HTTP PUT.
     *
     * Esta función ejecuta el controlador proporcionado cuando se recibe una solicitud PUT.
     *
     * @param array $params Los parámetros de la solicitud.
     * @param callable|array $controller El controlador que se ejecutará.
     * @param string|null $mime_type (Opcional) El tipo MIME de la respuesta.
     * @return void
     */
    public function execute_put_method(array $params, callable|array $controller, ?string $mime_type = null): void;

    /**
     * Ejecuta el controlador asociado al método HTTP DELETE.
     *
     * Esta función ejecuta el controlador proporcionado cuando se recibe una solicitud DELETE.
     *
     * @param array $params Los parámetros de la solicitud.
     * @param callable|array $controller El controlador que se ejecutará.
     * @param string|null $mime_type (Opcional) El tipo MIME de la respuesta.
     * @return void
     */
    public function execute_delete_method(array $params, callable|array $controller, ?string $mime_type = null): void;

    /**
     * Devuelve las entradas del usuario.
     *
     * @return array
     */
    public function get_values(): array|string;
}
