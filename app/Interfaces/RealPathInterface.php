<?php

namespace DLRoute\Interfaces;

interface RealPathInterface {

    /**
     * Devuelve la ruta raíz real de la aplicación.
     *
     * @return string
     */
    public function get_document_root(): string;


    /**
     * Devuelve el nombre del directorio real de trabajo del proyecto.
     *
     * @return string
     */
    public function get_workdir(): string;

    /**
     * Devuelve la URI del directorio de trabajo.
     *
     * @return string
     */
    public function get_uri_from_workdir(): string;
}