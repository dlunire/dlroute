<?php

namespace DLRoute\Requests;

use DLRoute\Interfaces\ParamTypeInterface;
use DLRoute\Server\DLServer;

/**
 * Define el tipo de datos que se espera en los parámetros de la petición
 * 
 * @package DLRoute\Requests
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
abstract class DLParamValueType implements ParamTypeInterface {

    /**
     * Filtro de búsqueda.
     *
     * @var array
     */
    private array $filters = [];

    /**
     * Ruta actual de registro.
     *
     * @var string
     */
    protected static string $route = "";

    /**
     * Filtra el valor de los campos
     *
     * @param array $fields Campos a ser filtrado.
     * @return void
     */
    public function filter_by_type(array $fields): void {
        $method = DLServer::get_method();
        $route = self::$route;

        foreach ($fields as $key => $value) {
            if (!is_string($key)) {
                $this->message("Formato de filtro inválido", 500);
                exit;
            }

            $this->filters[$method][$route][$key] = trim($value);
        }
    }

    /**
     * Filtra el valor de los parámetrols
     *
     * @param array $filters
     * @param object $params
     * @return void
     */
    protected function filter_param(array $filters, object $params): void {

        foreach ($params as $key => $value) {
            if (!array_key_exists($key, $filters)) {
                continue;
            }

            /**
             * Patrón de búsqueda o tipo.
             * 
             * @var string
             */
            $pattern = $filters[$key];

            /**
             * Nombre del método a ejecutar.
             * 
             * @var string
             */
            $method = "is_{$pattern}";

            if (method_exists($this, $method)) {

                /**
                 * Indicador de tipos.
                 * 
                 * @var boolean
                 */
                $is_valid_type = $this->{$method}($value);

                if (!$is_valid_type) {
                    DLOutput::not_found();
                }

                continue;
            }

            if (@preg_match($pattern, $value) === FALSE) {
                DLOutput::not_found();
            }

            if (!preg_match($pattern, $value)) {
                DLOutput::not_found();
            }
        }
    }

    public function get_filters(): array {
        return $this->filters;
    }

    /**
     * Valida si una cadena es una `UUID`.
     *
     * @param mixed $value
     * @return bool
     */
    private function is_uuid(mixed $value): bool {
        if (!is_string($value)) {
            return false;
        }

        /**
         * Patrón de búsqueda de una cadena `UUID`
         * 
         * @var string
         */
        $pattern = '/^[a-f0-9]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

        return preg_match($pattern, $value);
    }

    /**
     * Valida si la entrada es un correo electrónico.
     *
     * @param string $input Entrada a ser analizada.
     * @return boolean
     */
    private function is_email(string $input): bool {
        $input = trim($input);

        if (strlen($input) < 5) {
            return false;
        }

        $email_pattern = '/^[a-z][a-z0-9-_.]{1,63}\@[a-z][a-z0-9-_.]+\.[a-z0-9-]{1,10}$/';

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
     * Valida si la entrada es un entero.
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    private function is_integer(mixed $input): bool {
        return is_int($input);
    }

    /**
     * Valida si la entrada es booleana.
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    private function is_boolean(mixed $input): bool {
        return is_bool($input);
    }

    /**
     * Valida si la entrada es un númoro real.
     *
     * @param mixed $input Entrada a ser analizada
     * @return boolean
     */
    private function is_float(mixed $input): bool {
        return is_float($input);
    }

    /**
     * Analiza si la entrada del usuario es una cadena de texto.
     *
     * @param mixed $input Entrada a ser analizada.
     * @return boolean
     */
    private function is_string(mixed $input): bool {
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
     * @param mixed $input Entrada a ser procesada.
     * @return boolean
     */
    private function is_password(mixed $input): bool {
        if (!is_string($input)) {
            return false;
        }

        /**
         * Longitud de la contraseña.
         * 
         * @var integer
         */
        $length = strlen($input);

        if ($length < 8) {
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

    /**
     * Verifica si la entrada es un número.
     *
     * @param mixed $input Entrada a ser procesada.
     * @return boolean
     */
    private function is_numeric(mixed $input): bool {
        if (!is_numeric($input)) {
            return false;
        }

        /**
         * Patrón de búsqueda de un número con o sin decimal.
         * 
         * @var string
         */
        $numeric_pattern = "/^[0-9]+\.?[0-9]*$/";

        return preg_match($numeric_pattern, $input);
    }

    /**
     * Devuelve un mensaje de error donde se indica que el tipo no 
     * es admitido.
     *
     * @param mixed $value Valor a ser analizado
     * @param bool $type Indica si es un error de tipo o de formato. `true` para indicar si el error es de tipo.
     * @return void
     */
    private function error(mixed $value, bool $type = true): void {
        /**
         * Devuelve el nombre del tipo de datos de `$value`
         * 
         * @var string
         */
        $type = gettype($value);

        header("Content-Type: application/json; charset=utf-8", true, 500);

        /**
         * Mensaje de error.
         * 
         * @var string
         */
        $error = "";

        if (!$type) {
            $error = DLOutput::get_json([
                "status" => false,
                "error" => "El formato de la cadena «{$value}» es inválido"
            ], true);
        }

        if ($type) {
            $error = DLOutput::get_json([
                "status" => false,
                "error" => "Tipo «{$type}» no admitido"
            ], true);
        }

        echo $error;

        exit;
    }

    /**
     * Permite establecer un mensaje personalizado.
     *
     * @param string $message Mensaje personalizado
     * @param integer $code Código HTTP
     * @return void
     */
    private function message(string $message, int $code = 200): void {
        header("Content-Type: application/json; charset=utf-8", true, $code);

        $message = DLOutput::get_json([
            "status" => false,
            "error" => trim($message)
        ], true);

        echo $message;

        exit;
    }

    /**
     * Devuelve una instancia de esta clase.
     *
     * @return self
     */
    protected function get_param_instance(): self {
        return $this;
    }
}
