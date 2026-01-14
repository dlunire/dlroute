<?php

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
            $this->content = self::get_json($this->content, true);
        }

        if ($mime_type !== null) {
            $mime = $mime_type;
        }

        header("Content-Type: {$mime}; charset=utf-8");
        print_r($this->content);
    }

    public function set_content(mixed $content): void {
        $this->content = is_string($content) ? trim($content) : $content;
    }

    public static function get_json(object|array $content, bool $pretty = false): string {
        $stringData = $pretty
            ? json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)
            : json_encode($content, JSON_NUMERIC_CHECK);


        return trim($stringData);
    }

    public static function not_found(): void {
        header("Content-Type: application/json; charset=utf-8", true, 404);

        if (self::$personalize) {
            echo self::get_json(self::$error_404, true);
            exit;
        }

        echo self::get_json([
            "message" => "La ruta solicitada no existe",
            "code" => 404,
            "route" => DLServer::get_route(),
            "uri" => DLServer::get_uri(),
            "dir" => DLServer::get_dir(),
            "base_url" => DLServer::get_base_url(),
            "timestamp" => date(DATE_ATOM),
            "client_ip" => DLServer::get_ipaddress(),
            "method" => DLServer::get_method(),
            "hint" => "Verifica que la ruta sea correcta y esté registrada en el servidor"
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
        return is_numeric($this->content);
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
