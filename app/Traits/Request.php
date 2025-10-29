<?php

namespace DLRoute\Traits;

use CurlHandle;
use DLRoute\Requests\DLOutput;
use DLRoute\Requests\HeadersInit;
use DLRoute\Requests\RequestInit;
use InvalidArgumentException;

trait Request {

    /**
     * Indica si debe seguir o no redirecciones HTTP
     * 
     * @var boolean $follow_location
     */
    private bool $follow_location = false;

    /**
     * Indica la cantidad máxima de redirecciones que puede seguir. Por defecto es 10.
     * 
     * @var int $max_redirect
     */
    private int $max_redirect = 10;

    /**
     * Indica si el valor debe devolver algún valor
     * 
     * @var boolean $return_transfer
     */
    private bool $return_transfer = true;

    /**
     * Establece el agente de usuario del cliente HTTP. El agente de usuario
     * predeterminado es:
     * 
     * ```bash
     *   Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3
     * ```
     */
    private string $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3";

    /**
     * Indica si debe verificarse el certificado SSL enviado por el servidor.
     * 
     * @var boolean $verify_peer
     */
    private bool $verify_peer = false;

    /**
     * Verifica que el nombre del host coincidan con el nombre del certificado. Los alores permitidos son:
     * 
     * - Request::VERIFY_HOST
     * - Request::NOT_VERIFY_HOST
     * 
     * Si no incluyen estos valores, entonces, se lanzará una excepción de tipo `InvalidArgumentException`
     * 
     * @var integer $verify_host
     */
    private int $verify_host = 0;

    /** 
     * Tiempo máximo de espera de la conexión. El valor por defecto es 10
     * 
     * @var int $connect_timeout
     */
    private int $connect_timeout = 10;

    /**
     * Tiempo máximo de espera de respuesta del servidor. El valor por defecto es 30
     * 
     * @var int $timeout
     */
    private int $timeout = 30;

    /**
     * Ruta de las cookies
     * 
     * @var string $cookies
     */
    private string $cookies;

    /**
     * Método de envío HTTP GET
     * 
     * @var string
     */
    public const GET = 'GET';

    /**
     * Método HTTP POST
     * 
     * @var string
     */
    public const POST = 'POST';

    /**
     * Método HTTP PUT
     * 
     * @var string
     */
    public const PUT = 'PUT';

    /**
     * Método HTTP PATCH
     * 
     * @var string
     */
    public const PATCH = 'PATCH';

    /**
     * Método HTTP DELETE
     * 
     * @var string
     */
    public const DELETE = 'DELETE';

    /**
     * Indica que debe verificarse el nombre del certificado SSL con el nombre de host
     * 
     * @var int
     */
    public const VERIFY_HOST = 2;

    /**
     * Indica que no debe verificarse el nombre del certificado SSL con el nombre de host.
     */
    public const NOT_VERIFY_HOST = 0;

    /**
     * Establece si debe seguir o no las redirecciones HTTP.
     * 
     * @param boolean $follow_location [Opcional] Indica si deben seguir o no las redirecciones. El valor 
     *                                 por defecto es `true`.
     * 
     * @return void
     */
    public function set_follow_location(bool $follow_location = true): void {
        $this->follow_location = $follow_location;
    }

    /**
     * Indica la cantidad máxima de redirecciones que puede o debe seguir.
     * 
     * @param int $max_redirect [Opcional] Indica la cantidad máxima de redirecciones que puede seguir.
     *                          El valor por defecto ese 10.
     * @return void
     */
    public function set_max_redirect(int $max_redirect = 10): void {
        $this->max_redirect = $max_redirect;
    }

    /**
     * Indica si la transferencia de datos debe retornarse
     * 
     * @param boolean $return_transfer [Opcional] Indica si deben retornarse la transferencia de datos o no.
     *                                 El valor por defecto `true`.
     * 
     * @return void
     */
    public function set_return_transfer(bool $return_transfer = true): void {
        $this->return_transfer = $return_transfer;
    }

    /**
     * Permite establecer el agente de usuario personalizado.
     * 
     * @param string $user_agent [Opcional] Define el agente del usuario que se enviará al servidor.
     *                          El valor por defecto es `Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3`
     * 
     * @return void
     */
    public function set_user_agent(string $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'): void {
        $this->user_agent = trim($user_agent);
    }

    /**
     * Verifica la validez del certificado entregado por el servidor al que se le hace la petición HTTP.
     * 
     * @param boolean $verify_peer [Opcional] Indica si debe verificarse la validez del cerficado SSL. El valor por defecto es false
     * @return void
     * 
     */
    public function set_verify_peer(bool $verify_peer = false): void {
        $this->verify_peer = $verify_peer;
    }

    /**
     * Permite establecer si debe verificarse el nombre del certificado con el nombre de dominio. Los valores permitidos
     * son los siguientes:
     * 
     * - Request::VERIFY_HOST
     * - Request::NOT_VERIFY_HOST
     * 
     * @param int $verify_host [Opcional] Indica si debe verificarse el nombre del certificado SSL con el nombre del host que lo envía.
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    public function set_verify_host(int $verify_host = self::NOT_VERIFY_HOST): void {

        if ($verify_host !== self::VERIFY_HOST && $verify_host !== self::NOT_VERIFY_HOST) {
            throw new InvalidArgumentException("Los valos permitidos son 0: Request::NOT_VERIFY_HOST y 2: Request::VERIFY_HOST", 500);
        }

        $this->verify_host = $verify_host;
    }

    /**
     * Establece el tiempo máximo de tiempo de espera de la conexión
     * 
     * @param int $connect_timeout [Opcional] Establece el tiempo máximo de espera de la
     *                             conexión que debe o puede esperarse.
     * 
     * @return void
     */
    public function set_connect_timeout(int $connect_timeout = 10): void {
        $this->connect_timeout = $connect_timeout;
    }

    /**
     * Estalece el tiempo máximo de espera de la respueta del servidor al que se le envió
     * la petición HTTP.
     * 
     * @param int $timeout [Opcional] Establece el tiempo máximo de espera de la respuesta del servidor.
     * @return void
     */
    public function set_timeout(int $timeout = 30): void {
        $this->timeout = $timeout;
    }

    /**
     * Realiza una solicitud HTTP mediante cURL.
     *
     * @param string $url URL de destino.
     * @param string $method Método HTTP (GET, POST, PUT, DELETE, etc.).
     * @param HeadersInit|null $headers Cabeceras personalizadas.
     * @param array $data Datos del cuerpo de la petición (para POST, PUT, etc.).
     * @return string|bool Respuesta del servidor o false en caso de error.
     */
    public function request(string $url, string $method = 'GET', ?HeadersInit $headers = null, array $data = []): string|bool {
        $this->set_cookies();

        /**
         * @var CurlHandle|false $ch
         */
        $ch = curl_init();

        if (!($ch instanceof CurlHandle)) {
            return false;
        }

        /**
         * Cabeceras actuales
         *
         * @var array $current_headers
         */
        $current_headers = [];

        if ($headers instanceof HeadersInit) {
            $current_headers = $headers->get_headers();
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => $this->return_transfer, // Retornar respuesta como string
            CURLOPT_HTTPHEADER => $current_headers,           // Cabeceras personalizadas
            CURLOPT_USERAGENT => $this->user_agent,           // Agente de usuario

            // Seguridad
            CURLOPT_SSL_VERIFYPEER => $this->verify_peer,     // Verificación de certificado SSL
            CURLOPT_SSL_VERIFYHOST => $this->verify_host,     // Verificación del nombre del host
            CURLOPT_CUSTOMREQUEST => strtoupper($method),     // Método HTTP (POST, GET, etc.)

            // Redirecciones
            CURLOPT_FOLLOWLOCATION => $this->follow_location, // 🔹 Seguir redirecciones 3xx
            CURLOPT_MAXREDIRS => $this->max_redirect,         // 🔹 Límite de redirecciones
            CURLOPT_AUTOREFERER => true,                      // 🆕 Actualiza el Referer en redirecciones
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS, // 🆕 Protocolos permitidos en redirecciones

            // Cookies
            CURLOPT_COOKIEJAR => $this->cookies,              // 🆕 Donde se guardarán las cookies
            CURLOPT_COOKIEFILE => $this->cookies,             // 🆕 De dónde se leerán las cookies
            CURLOPT_COOKIESESSION => false,                   // 🆕 No reiniciar la sesión al abrir handle nuevo

            // Compresión y headers
            CURLOPT_ENCODING => '',                           // 🆕 Aceptar gzip, deflate, br (auto)
            CURLOPT_HEADER => false,                          // No incluir headers en el body
            CURLINFO_HEADER_OUT => true,                      // 🆕 Permitir inspección de headers enviados

            // Tiempo y conexión
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout, // Timeout de conexión
            CURLOPT_TIMEOUT => $this->timeout,                // Timeout total

            // Depuración opcional (puede comentarse en producción)
            // CURLOPT_VERBOSE => true,                       // 🆕 Mostrar detalles del tráfico
        ]);

        // Manejo de cookies (sesiones persistentes)
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookies);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookies);

        // Si el método permite cuerpo, lo enviamos
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            // Detectar si el encabezado Content-Type indica JSON
            $is_json = false;

            foreach ($current_headers as $h) {
                if (stripos($h, 'Content-Type: application/json') !== false) {
                    $is_json = true;
                    break;
                }
            }

            $payload = $is_json ? json_encode($data) : http_build_query($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        /**
         * @var string|bool $response
         */
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            http_response_code(500);
            DLOutput::get_json([
                "status" => false,
                "error" => "Error en cURL: {$error}"
            ]);

            exit;
        }

        /**
         * @var int $response_code
         */
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        http_response_code($response_code);
        return $response;
    }


    /**
     * Envía una petición al servidor remoto
     *
     * @param string $action URL base del servidor
     * @return string
     */
    public function fetch(string $action, RequestInit $init): string {
        /**
         * @var string $response
         */
        $response = $this->request($action, $init->method, $init->headers, $init->body);

        return $response;
    }

    /**
     * Establece las cookies para mantener la sesión activa
     * 
     * @param string|null $path [Opcional] Indica la ruta de las cookies
     * @return void
     */
    public function set_cookies(?string $path = null): void {
        /** @var string $separator */
        $separator = DIRECTORY_SEPARATOR;

        if (!is_string($path)) {
            $this->cookies = sys_get_temp_dir() . "{$separator}dlroute_cookies.txt";
            return;
        }

        $this->cookies = $path;
    }

    /**
     * Devuelve la ruta de la cookie, en el caso de que sea posible.
     * 
     * @return string|null
     */
    public function get_cookies_path(): ?string {
        return $this->cookies ?? null;
    }

    /**
     * Elimina las cookies
     * 
     * @return boolean
     */
    public function delete_cookies(): bool {
        if (is_string($this->get_cookies_path()) && file_exists($this->get_cookies_path())) {
            return @unlink($this->get_cookies_path());
        }

        return false;
    }
}
