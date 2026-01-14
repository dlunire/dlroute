<?php

namespace DLRoute\Interfaces;

/**
 * Procesa la salida del controlador para determinar el tipo de contenido
 * 
 * @package Trading\Interfaces
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 */
interface OutputInterface {

    /**
     * Devuelve en pantalla los datos de la respuesta.
     *
     * @return string
     */
    public function print_response_data(?string $mime_type = null): void;

    /**
     * Establece el contenido a ser analizado
     *
     * @return void
     */
    public function set_content(mixed $content): void;

    /**
     * Convierte un objeto o un array en una cadena de texto en formato JSON y la devuelve.
     *
     * Esta función toma un objeto o array y lo convierte en una cadena de texto en formato JSON.
     *
     * @param object|array $content El contenido que se va a parsear.
     * @param bool $pretty Indica si la salida en formato JSON debe tener formato legible o no.
     * @return string La cadena de texto en formato JSON resultante.
     */
    public static function get_json(object|array $content, bool $pretty = false): string;

    /**
     * Envía una respuesta HTTP 404 "Not Found" al cliente.
     *
     * Este método genera automáticamente la respuesta cuando la aplicación determina
     * que la ruta solicitada no existe. La respuesta se envía en formato JSON y
     * contiene información contextual útil tanto para el desarrollador como para
     * fines de depuración y análisis de peticiones.
     *
     * Características principales:
     * - El código HTTP siempre será 404.
     * - Si previamente se configuró una salida personalizada mediante
     *   `set_error_404()`, se usará dicha información como contenido de la respuesta.
     * - Si no hay personalización, se devuelve un JSON con campos estándar:
     *   - `message`: Mensaje indicando que la ruta solicitada no existe.
     *   - `code`: Código HTTP (404).
     *   - `route`: Ruta solicitada que no se encuentra registrada.
     *   - `uri`: URI completa de la petición.
     *   - `dir`: Directorio base calculado de la aplicación.
     *   - `base_url`: URL base de la aplicación.
     *   - `timestamp`: Fecha y hora de la respuesta en formato ISO 8601.
     *   - `client_ip`: Dirección IP del cliente.
     *   - `method`: Método HTTP usado en la petición.
     *   - `hint`: Sugerencia para el desarrollador o usuario sobre cómo resolver el error.
     *
     * Uso:
     * ```php
     * <?php
     * DLOutput::not_found();
     * ```
     *
     * Importante:
     * - Este método **termina la ejecución** de la aplicación tras enviar la respuesta,
     *   mediante `exit`, por lo que no se deben ejecutar instrucciones adicionales
     *   después de llamarlo.
     * - Está diseñado para ser llamado de manera intencional desde el flujo del
     *   framework o desde controladores cuando se detecta que la ruta no existe.
     * - La personalización de la respuesta 404 se activa únicamente si previamente
     *   se llamó a `DLOutput::set_error_404()`.
     *
     * @return void
     */
    public static function not_found(): void;

}
