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

declare(strict_types=1);

namespace DLRoute\Requests;

use DLRoute\Errors\OutputException;
use DLRoute\Interfaces\OutputInterface;
use DLRoute\Server\DLServer;

class DLOutput implements OutputInterface {

    /**
     * Permite personalizar el mensaje de error
     *
     * @var boolean $personalize
     */
    private static bool $personalize = false;

    /**
     * Carga los datos del error personalizado
     *
     * @var array
     */
    private static array $error_404 = [];

    /**
     * Instancia de clase
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Contenido a ser analizado
     *
     * @var mixed
     */
    private mixed $content = null;

    private function __construct() {
    }

    /**
     * Devuelve una instancia de Output
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function print_response_data(?string $mime_type = null): void {
        $mime = "blob";

        if ($this->is_string()) {
            $mime = "text/html";
        }

        if ($this->is_boolean() || $this->is_null() || $this->is_numeric()) {
            $mime = "text/plain";
        }

        if ($this->is_boolean()) {
            $this->content = $this->content ? "true" : "false";
        }

        if ($this->is_array() || $this->is_object()) {
            $mime = "application/json";
            $this->content = self::to_json($this->content, true);
        }

        if ($mime_type !== null) {
            $mime = $mime_type;
        }

        \header("Content-Type: {$mime}; charset=utf-8");
        \print_r($this->content);
    }

    public function set_content(mixed $content): void {
        $this->content =  \is_string($content) ? trim($content) : $content;
    }

    public static function to_json(mixed $content, bool $pretty = false): string {
        /** @var string|false */
        $string_data = $pretty
            ? \json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)
            : \json_encode($content, JSON_NUMERIC_CHECK);

        return self::validate_json_structure($string_data);
    }


    /**
     * Valida superficialmente la salida de serialización JSON y garantiza que el resultado
     * final represente siempre un objeto o array JSON.
     *
     * Comprueba que `$input` sea una cadena y que, tras recortarla con `trim()`, comience con
     * `{` o `[` — sin validar la estructura interna del JSON. Si ambas condiciones se cumplen,
     * la cadena se devuelve normalizada (recortada) tal cual.
     *
     * Si `$input` no es una cadena (por ejemplo, `false` cuando `json_encode()` falla) o no
     * comienza con `{` ni `[` (por ejemplo, la serialización válida de un escalar como `null`,
     * un booleano, un número o una cadena), se envuelve en un objeto JSON con la forma
     * `{"value": <valor>}`, usando {@see self::to_json_literal()} para obtener la representación
     * literal de `$input` a insertar dentro del objeto.
     *
     * @param mixed $input Salida generada por el proceso de serialización que será sometida a esta
     *                     validación superficial.
     * @return string Cadena JSON normalizada si comienza con `{` o `[`, o `{"value": <valor>}`
     *                cuando no cumple las condiciones mínimas.
     */
    private static function validate_json_structure(mixed $input): string {
        /** @var string $value */
        $value = "";

        if (\is_string($input)) {
            $value = \trim($input);
        }

        /** @var string $char */
        $char = $value[0] ?? '';

        if ($char === "\x7b" || $char === "\x5b") {
            return $value;
        }

        return "\x7b\"value\": " .  self::to_json_literal($input) . "\x7d";
    }

    /**
     * Convierte un valor escalar en su representación literal para insertarse como valor
     * dentro de un fragmento JSON.
     *
     * Normaliza los tipos que `print_r()` no representa de forma compatible con JSON:
     * `bool` se convierte en las cadenas literales `"true"`/`"false"`, y `null` en la cadena
     * literal `"null"`. Cualquier otro tipo —incluida una cadena que ya sea JSON válido, como
     * la salida previa de `json_encode()`— se devuelve mediante `print_r()` sin modificación
     * adicional.
     *
     * @param mixed $value Valor a convertir en su representación literal.
     * @return string Representación literal de `$value`, lista para insertarse directamente
     *                dentro de un fragmento JSON.
     */
    private static function to_json_literal(mixed $value): string {
        /** @var non-empty-string $type */
        $type = \gettype($value);

        if ($type === "boolean") {
            $value = $value === false
                ? "false" : "true";
        }

        if ($type === "NULL") {
            $value = "null";
        }

        return print_r($value, true);
    }

    public static function not_found(): void {
        header("Content-Type: application/json; charset=utf-8", true, 404);

        if (self::$personalize) {
            echo self::to_json(self::$error_404, true);
            exit;
        }

        echo self::to_json([
            "code" => 404,
            "method" => DLServer::get_method(),
            "message" => "La ruta solicitada no existe",
            "route" => DLServer::get_route(),
            "uri" => DLServer::get_uri(),
            "timestamp" => date(DATE_ATOM),
            "hint" => "Verifica si la ruta que ha vistado es correcta"
        ], true);

        exit;
    }

    /**
     * Valida si la salida es un array
     *
     * @return boolean
     */
    private function is_array(): bool {
        return \is_array($this->content);
    }

    /**
     * Valida si la salida es un objeto.
     *
     * @return boolean
     */
    private function is_object(): bool {
        return \is_object($this->content);
    }

    /**
     * Valida si es un booleano
     *
     * @return boolean
     */
    private function is_boolean(): bool {
        return \is_bool($this->content);
    }

    /**
     * Valida si es nulo
     *
     * @return boolean
     */
    private function is_null(): bool {
        return $this->content === null;
    }

    /**
     * Valida si es numérico
     *
     * @return boolean
     */
    private function is_numeric(): bool {
        return \is_numeric($this->content);
    }

    /**
     * Valida si es una cadena de texto.
     *
     * @return boolean
     */
    private function is_string(): bool {
        return \is_string($this->content);
    }

    /**
     * Establece una respuesta personalizada para errores 404.
     *
     * Este método permite al desarrollador definir un conjunto de datos que serán
     * serializados como JSON y enviados cuando la aplicación determine que la
     * ruta solicitada no existe. La personalización es útil para:
     * - Mostrar mensajes amigables al usuario.
     * - Incluir información adicional sobre la petición o contexto.
     * - Integrarse con sistemas de logging o frontend específicos.
     *
     * Comportamiento:
     * - Los datos proporcionados deben ser un array asociativo.
     * - Una vez configurados, la salida 404 personalizada se activará
     *   automáticamente en `not_found()`.
     * - El código HTTP de la respuesta seguirá siendo 404, independientemente
     *   del contenido del array.
     *
     * Validaciones:
     * - El array no puede estar vacío. Si se pasa un array vacío, se lanzará
     *   una excepción `OutputException` para advertir al desarrollador
     *   sobre la configuración incorrecta.
     *
     * Ejemplo de uso:
     * ```php
     * <?php
     * DLOutput::set_error_404([
     *     "message" => "Página no encontrada",
     *     "help" => "Verifica que la URL sea correcta o contacta soporte",
     *     "timestamp" => date(DATE_ATOM)
     * ]);
     * ```
     * 
     * **Nota:** debes llamar el método al principio de tu aplicación para que el error 404
     * personalizado tenga efecto en toda la aplicación. Sin embargo, también lo puedes utilizar en un
     * controlador para personalizar casos de error 404 específicos.
     *
     * @param array $data Array asociativo con la información a mostrar en la respuesta 404.
     *                    Debe contener al menos un elemento.
     *
     * @return void
     *
     * @throws OutputException Si el array está vacío, indicando un error de configuración
     *                        por parte del desarrollador.
     */
    public static function set_error_404(array $data): void {

        if (\count($data) < 1) {
            throw new OutputException("Error de configuración: el array de personalización 404 no puede estar vacío.");
        }

        self::$error_404 = $data;
        self::$personalize = true;
    }
}
