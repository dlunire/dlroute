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