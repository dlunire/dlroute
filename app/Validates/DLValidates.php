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

namespace DLRoute\Validates;

trait DLValidates {

    /**
     * Valida si la entrada es un correo electrónico válido.
     *
     * @param mixed $input Entrada de texto
     * @return boolean
     */
    protected function is_email(mixed $input): bool {

        if (!($this->is_string($input))) {
            return false;
        }

        $input = trim($input);

        if (strlen($input) < 5) {
            return false;
        }

        $email_pattern = '/^[a-z][a-z0-9-_.]{1,63}\@[a-z][a-z0-9-_]+\.[a-z0-9-]{1,10}$/';

        /**
         * Resultado de un análisis previo hecho para validar un correo electrónico.
         * 
         * @var boolean
         */
        $found = preg_match($email_pattern, $input);

        if (!$found) {
            return false;
        }

        /**
         * Si es `true` significa que hay más de 1 punto segundos.
         * 
         * @var boolean
         */
        $points = preg_match('/([-_.]{2,})/', $input);

        if ($points) {
            return false;
        }

        return true;
    }

    /**
     * Valida si la entrada en un entero.
     *
     * @param string $input Entrada
     * @return boolean
     */
    protected function is_integer(mixed $input): bool {
        return is_int($input);
    }

    /**
     * Valida si la entrada es un booleano.
     *
     * @param mixed $value Entrada
     * @return boolean
     */
    protected function is_boolean(mixed $value): bool {
        return is_bool($value);
    }

    /**
     * Valida si la entrada es un número de punto flotante.
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    protected function is_float(mixed $input): bool {
        return is_float($input);
    }

    /**
     * Valida si la entrada es un número
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    protected function is_numeric(mixed $input): bool {
        return $this->is_integer($input) || $this->is_float($input);
    }

    /**
     * Valida si la entrada es un número entero sin signo.
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    protected function is_unsigned_integer(mixed $input): bool {
        return $this->is_integer($input) && $input >= 0;
    }

    /**
     * Valida si la entrada es un número de punto flotante sin signo.
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    protected function is_unsigned_float(mixed $input): bool {
        return $this->is_float($input) && $input >= 0;
    }

    /**
     * Valida si la entrada es un número sin signo.
     *
     * @param mixed $input Entrada a ser analizada
     * @return boolean
     */
    protected function is_unsigned_numeric(mixed $input): bool {
        return $this->is_numeric($input) && $input >= 0;
    }

    /**
     * Valida si la entrada es una cadena de texto
     *
     * @param mixed $input Entrada a ser validada.
     * @return boolean
     */
    protected function is_string(mixed $input): bool {
        return is_string($input);
    }

    /**
     * Analiza si la entrada del usuario es una contraseña válida, empezando, porque
     * la contraseña debe tener una longitud mínima de 8 caracteres.
     * 
     * Los caracteres especiales admitidos son:
     * 
     * ```
     * @$*|#+{}[].-_/\&%!
     * ```
     * Y al menos, una letra mayúscula.
     * 
     * @param mixed $input Entrada a ser analizada.
     * @param integer $length Longitud permitida de la entrada
     * @return boolean
     */
    protected function is_password(mixed $input, int $length = 8): bool {
        if (!is_string($input)) {
            return false;
        }

        /**
         * Longitud de la contraseña.
         * 
         * @var integer
         */
        $length = strlen($input);

        if ($length < $length) {
            return false;
        }

        /**
         * Patrón de búsqueda de caracteres especiales.
         * 
         * @var string
         */
        $special_char = "/[@\$\*\|#+{|}\[\].-_\/\\&%!]+/";

        if (!preg_match($special_char, $input)) {
            return false;
        }

        if (!preg_match('/[a-z]+/', $input)) {
            return false;
        }

        return true;
    }
}