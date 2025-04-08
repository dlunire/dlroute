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
    public static function get_json(object | array $content, bool $pretty = false): string;

    /**
     * Undocumented function
     *
     * @param integer $code
     * @return void
     */
    public static function not_found(): void;
}
