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

namespace DLRoute\Config;

use DLRoute\Interfaces\InstanceInterface;
use DLRoute\Interfaces\RealPathInterface;

class DLRealPath implements RealPathInterface, InstanceInterface{

    /**
     * Ruta raíz del proyecto.
     *
     * @var string
     */
    private string $document_root;

    private static ?self $instance = null;

    private function __construct() {
        $this->set_path();
    }

    public static function get_instance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }

    public function get_document_root(): string {
        return $this->document_root;
    }

    /**
     * Establece la ruta raíz del proyecto.
     *
     * @return void
     */
    private function set_path(): void {
        if (defined('DOCUMENT_ROOT')) {
            $this->document_root = constant('DOCUMENT_ROOT');
            return;
        }

        /**
         * Directorio raíz de la aplicación
         * 
         * @var string
         */
        $dir = getcwd();
        $dir = dirname($dir);
        $dir = realpath($dir);

        $this->document_root = trim($dir);
    }

    public function get_workdir(): string {
        /**
         * Directorio real de trabajo.
         * 
         * @var string
         */
        $workdir = "";

        if (array_key_exists('SCRIPT_NAME', $_SERVER)) {
            $workdir = $_SERVER['SCRIPT_NAME'];
        }

        $workdir = dirname($workdir, 1);
        $workdir = basename($workdir);

        return $workdir;
    }

    public function get_uri_from_workdir(): string {
        $self = $_SERVER['SCRIPT_NAME'];
        $uri = dirname($self);

        return $uri;
    }
}