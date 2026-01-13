<?php

namespace DLRoute\Interfaces;

/**
 * Procesa los datos de `$_SERVER`
 * 
 * @package DLRoute\Interfaces;
 * 
 * @version v0.0.1
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
interface ServerInterface {

    /**
     * Devuelve la URI de la aplicación. Es decir, la URI más la ruta registrada
     * en el caso de que se esté navegando por ella.
     *
     * @return string
     */
    public static function get_uri(): string;

    /**
     * Devuelve el nombre de host, bien sea, el que se ha determinado o el que se 
     * impuso por el desarrollador en el entorno de ejecución.
     *
     * @return string
     */
    public static function get_hostname(): string;

    /**
     * Devuelve el método HTTP.
     *
     * @return string
     */
    public static function get_method(): string;

    /**
     * Devuelve la dirección IP del cliente, siempre que sea posible
     *
     * @return string
     */
    public static function get_ipaddress(): string;

    /**
     * Devuelve el nombre del script que se está ejecutando.
     *
     * @return string
     */
    public static function get_script_filename(): string;

    /**
     * Devuelve el agente de usuario del cliente de la petición.
     *
     * @return string
     */
    public static function get_user_agent(): string;

    /**
     * Devuelve el documento raíz de ejecución de la aplicación
     *
     * @return string
     */
    public static function get_document_root(): string;

    /**
     * Devuelve el hombre de host con puerto incluido en formato HTTP, es decir,
     * de una forma similar a esta: `http://localhost:3000/` o la que impone el 
     * proxy inverso o similar.
     *
     * @return string
     */
    public static function get_http_host(): string;

    /**
     * Determina si el método de envío HTTP es GET.
     *
     * @return boolean
     */
    public static function is_get(): bool;
    /**
     * Indica si el método HTTP es POST
     *
     * @return boolean
     */
    public static function is_post(): bool;

    /**
     * Determina si el método de envío HTTP es PUT.
     *
     * @return boolean
     */
    public static function is_put(): bool;

    /**
     * Determina si el método de envío HTTP es PATCH.
     *
     * @return boolean
     */
    public static function is_patch(): bool;

    /**
     * Determina si el método de envío HTTP es DELETE.
     *
     * @return boolean
     */
    public static function is_delete(): bool;

    /**
     * Devuelve la ruta lógica registrada por el desarrollador. No la que devuelve el
     * sistema por medio de $_SERVER. 
     *
     * @return string
     */
    public static function get_route(): string;

    /**
     * Devuelve el script actual de ejecución.
     *
     * @return string
     */
    public static function get_script_name(): string;
}