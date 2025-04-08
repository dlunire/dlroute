<?php

namespace DLRoute\Interfaces;

interface ParamTypeInterface {

    /**
     * Filtra los parámetros con expresiones regulares o nombres de tipos.
     *
     * Por ejemplo, se puede usar de la siguiente forma:
     * 
     * ```
     *  use DLRoute\Requests\DLRoute as Route;
     *  
     *  Route::get('/ruta/con/{parametro}', [TestController::class, 'method'])
     *      ->filter_by_type([
     *          "parametro" => "/^[a-f0-9]+$/i"
     *      ]);
     * ``` 
     * 
     * Donde la expresión regular anterior valida un hash alfanumérico de 0 a f de cualquier
     * longitud a partir de 1 carácter.
     * 
     * También puede indicar el tipo de datos, por ejemplo:
     * 
     * ```
     *  use DLRoute\Requests\DLRoute as Route;
     *  
     *  Route::get('/ruta/con/{parametro}', [TestController::class, 'method'])
     *      ->filter_by_type([
     *          "parametro" => "integer | float"
     *      ]);
     * ``` 
     * 
     * @param array $params Parámetros a ser filtrados con expresiones regulares.
     * @return void
     */
    public function filter_by_type(array $params): void;

    /**
     * Devuelve los filtros establecidos por el desarrollador.
     *
     * @return array
     */
    public function get_filters(): array;
}