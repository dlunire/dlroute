<?php

namespace DLRoute\Traits;

use DLRoute\Server\DLServer;

trait Path {

    /**
     * Devuelve la ruta en función del sistema operativo de ejecucion
     *
     * @param ?string $path Ruta relativa o absoluta
     * @return string
     */
    private function get_path(?string $path): string {

        if (is_null($path)) {
            $path = "";
        }

        /**
         * Patrón de búsqueda de letras de unidad
         * 
         * @var string $pattern_unit
         */
        $pattern_unit = "/[a-z]+:/i";

        $path = preg_replace($pattern_unit, '', $path);

        /**
         * Patrón de búsqueda de barras diagonales
         * 
         * @var string $pattern_path
         */
        $pattern_path = "/[\/\\\]+/";

        $path = preg_replace($pattern_path, DIRECTORY_SEPARATOR, $path);

        return $path;
    }

    /**
     * Devuelve una ruta absoluta a partir de una ruta relativa.
     * **Importante:** Cualquier ruta que se pase como argumento se considerará ruta relativa.
     *
     * @param string|null $path Ruta relativa
     * @return string
     */
    public function get_absolute_path(?string $path): string {

        if (is_null($path)) {
            $path = "";
        }

        /**
         * Directorio raíz del sistema
         * 
         * @var string $root
         */
        $root = DLServer::get_document_root();

        /**
         * Ruta absoluta del archivo enviado al servidor
         * 
         * @var string $absolute_path
         */
        $absolute_path = "{$root}/{$path}";

        return $this->get_path($absolute_path);
    }
}
