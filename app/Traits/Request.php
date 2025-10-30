<?php

namespace DLRoute\Traits;

use CURLFile;
use CurlHandle;
use DLRoute\Requests\DLOutput;
use DLRoute\Requests\HeadersInit;
use DLRoute\Requests\RequestInit;
use InvalidArgumentException;
use RuntimeException;

/**
 * Copyright (c) 2025 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
 *
 * @package DLRoute\Traits
 * @version v0.0.1
 * @author David E Luna M
 * @license MIT
 * @copyright 2025 David E Luna M
 *
 * Trait Request
 *
 * Proporciona una capa de abstracción sobre `cURL` para realizar solicitudes HTTP 
 * consistentes, gestionando cabeceras, cookies persistentes y configuraciones SSL.
 *
 * Está diseñado para integrarse dentro del framework **DLUnire**, ofreciendo un control 
 * de bajo nivel sobre el flujo de peticiones, redirecciones, tiempos de espera y 
 * transferencia de datos, con una sintaxis uniforme y extensible.
 *
 * ## Características principales:
 * - Manejo unificado de métodos HTTP: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
 * - Soporte para envío de datos `application/x-www-form-urlencoded`, `application/json`
 *   y `multipart/form-data` (incluyendo `CURLFile`).
 * - Gestión automática del archivo de cookies en formato Netscape (`dlunire-cookie.txt`).
 * - Control configurable de redirecciones, SSL, tiempos de conexión y usuario agente.
 * - Integración con clases `HeadersInit` y `RequestInit` para estructurar peticiones
 *   de alto nivel dentro del ecosistema DLRoute.
 *
 * ## Uso típico:
 * ```php
 * use DLRoute\Traits\Request;
 *
 * class MyService {
 *     use Request;
 *
 *     public function send() {
 *         $this->set_cookies('/tmp/session.txt');
 *         $response = $this->request(
 *             url: 'https://api.example.com/data',
 *             method: self::POST,
 *             headers: $headers,
 *             data: ['name' => 'DLUnire']
 *         );
 *         return $response;
 *     }
 * }
 * ```
 *
 * Actúa como base de todas las clases del sistema que necesiten comunicarse
 * con servicios externos o internos mediante HTTP, garantizando la reutilización de la 
 * lógica de conexión y manteniendo la coherencia entre las distintas capas de red del framework.
 */
trait Request {

    /**
     * Instancia de CurlHandle
     *
     * @var CurlHandle|false
     */
    private CurlHandle|false $ch = false;

    /** @var bool Indica si debe seguir redirecciones HTTP */
    private bool $follow_location = false;

    /** @var int Máximo de redirecciones HTTP permitidas */
    private int $max_redirect = 10;

    /** @var bool Si debe retornar el resultado de la transferencia */
    private bool $return_transfer = true;

    /** @var string Agente de usuario (User-Agent) predeterminado */
    private string $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        . "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3";

    /** @var bool Verificar la validez del certificado SSL */
    private bool $verify_peer = false;

    /** @var int Verificar coincidencia entre host y certificado */
    private int $verify_host = 0;

    /** @var int Tiempo máximo de conexión */
    private int $connect_timeout = 10;

    /** @var int Tiempo máximo de espera de respuesta */
    private int $timeout = 30;

    /** @var string Ruta del archivo de cookies */
    private string $cookies;

    // Métodos HTTP
    public const GET    = 'GET';
    public const POST   = 'POST';
    public const PUT    = 'PUT';
    public const PATCH  = 'PATCH';
    public const DELETE = 'DELETE';

    // Verificación SSL
    public const VERIFY_HOST      = 2;
    public const NOT_VERIFY_HOST  = 0;

    /** @var string[] Métodos que envían cuerpo de datos */
    public const METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Destructor del trait `Request`.
     *
     * Libera los recursos asociados a la sesión cURL activa al finalizar
     * la instancia del objeto que utilice este trait.  
     * 
     * Si el manejador `$this->ch` es una instancia válida de `CurlHandle`,
     * se invoca internamente `curl_close()` para cerrar la conexión y 
     * liberar memoria del sistema.
     *
     * Esta acción es automática y no requiere intervención del desarrollador,
     * asegurando una correcta gestión de recursos.
     *
     * @return void
     */
    public function __destruct() {
        if ($this->ch instanceof CurlHandle) {
            curl_close($this->ch);
        }
    }

    // -------------------------
    // CONFIGURACIÓN DE PETICIÓN
    // -------------------------

    /**
     * Define si la petición HTTP debe seguir automáticamente las redirecciones.
     *
     * Este método configura la propiedad interna `$follow_location`, la cual
     * controla el comportamiento de `CURLOPT_FOLLOWLOCATION` en cURL.
     * 
     * Cuando se establece en `true`, cURL seguirá automáticamente las respuestas
     * HTTP con códigos de redirección (3xx), actualizando la URL de destino según
     * las cabeceras `Location` recibidas.
     * 
     * Si se establece en `false`, cURL devolverá la respuesta sin seguir la redirección.
     *
     * @param bool $follow_location Determina si deben seguirse las redirecciones HTTP.  
     *                              Por defecto `true`.
     *
     * @return void
     * 
     * @see CURLOPT_FOLLOWLOCATION
     */
    public function set_follow_location(bool $follow_location = true): void {
        $this->follow_location = $follow_location;
    }

    /**
     * Define el número máximo de redirecciones HTTP que la petición puede seguir.
     *
     * Este método configura el límite de redirecciones consecutivas que cURL
     * permitirá al procesar respuestas con códigos de estado 3xx cuando
     * `CURLOPT_FOLLOWLOCATION` está habilitado.
     * 
     * Un valor demasiado alto puede generar ciclos de redirección o ralentizar
     * la ejecución de la solicitud, mientras que un valor bajo podría impedir
     * el acceso a recursos legítimamente redirigidos.
     *
     * @param int $max_redirect Número máximo de redirecciones permitidas.  
     *                          Valor por defecto: `10`.
     *
     * @return void
     * 
     * @see CURLOPT_MAXREDIRS
     * @see set_follow_location()
     * @since v0.0.1
     * @package DLRoute\Traits
     */
    public function set_max_redirect(int $max_redirect = 10): void {
        $this->max_redirect = $max_redirect;
    }


    /**
     * Establece si la transferencia debe ser retornada como cadena.
     *
     * @param bool $return_transfer Indica si se debe retornar el resultado.
     * @return void
     */
    public function set_return_transfer(bool $return_transfer = true): void {
        $this->return_transfer = $return_transfer;
    }


    /**
     * Establece el agente de usuario (User-Agent) para la solicitud HTTP.
     *
     * Este método define o actualiza el valor del encabezado `User-Agent` que se enviará con la
     * solicitud. Si no se proporciona ningún valor, conserva el agente de usuario previamente
     * configurado en la instancia. El valor se limpia automáticamente usando `trim()`.
     *
     * @param string $user_agent Cadena que representa el agente de usuario. Si se pasa una
     * cadena vacía, se mantendrá el valor existente del agente.
     * 
     * @return void
     */
    public function set_user_agent(string $user_agent = ''): void {
        $this->user_agent = trim($user_agent ?: $this->user_agent);
    }

    /**
     * Define si se debe verificar la validez del certificado SSL del servidor.
     *
     * Este método controla la verificación de la autenticidad del certificado SSL durante
     * una conexión HTTPS. Cuando la verificación está habilitada (`true`), cURL comprobará
     * que el certificado presentado por el servidor sea válido y confiable. Si está deshabilitada
     * (`false`), se omite dicha comprobación, lo cual puede ser útil en entornos de desarrollo,
     * pero no se recomienda en producción por razones de seguridad.
     *
     * @param bool $verify_peer Indica si se debe verificar el certificado SSL del servidor.
     * Por defecto, `false` (no verificar).
     * 
     * @return void
     */
    public function set_verify_peer(bool $verify_peer = false): void {
        $this->verify_peer = $verify_peer;
    }

    /**
     * Define el nivel de verificación del nombre del host en conexiones SSL/TLS.
     *
     * Este método configura cómo cURL debe validar que el nombre del host del certificado SSL
     * coincida con el dominio del servidor al establecer una conexión segura.
     * 
     * - `Request::NOT_VERIFY_HOST (0)` desactiva la verificación del nombre del host.  
     * - `Request::VERIFY_HOST (2)` activa la verificación estricta del host.
     * 
     * Cualquier otro valor lanzará una excepción `InvalidArgumentException`, ya que
     * cURL solo acepta los valores `0` o `2` para la opción `CURLOPT_SSL_VERIFYHOST`.
     * 
     * Desactivar la verificación del host (`0`) puede ser útil para entornos de desarrollo,
     * pero **no se recomienda en producción**, ya que permite ataques de tipo *Man-in-the-Middle*.
     *
     * @param int $verify_host Nivel de verificación del host.  
     *                         Valores válidos:  
     *                         - `Request::NOT_VERIFY_HOST (0)`  
     *                         - `Request::VERIFY_HOST (2)`  
     *                         Por defecto: `Request::NOT_VERIFY_HOST`.
     * 
     * @throws InvalidArgumentException Si el valor proporcionado no es `0` ni `2`.
     * 
     * @return void
     * 
     * @see CURLOPT_SSL_VERIFYHOST
     * @see set_verify_peer()
     */
    public function set_verify_host(int $verify_host = self::NOT_VERIFY_HOST): void {
        if (!in_array($verify_host, [self::VERIFY_HOST, self::NOT_VERIFY_HOST], true)) {
            throw new InvalidArgumentException(
                "Los valores permitidos son 0: Request::NOT_VERIFY_HOST o 2: Request::VERIFY_HOST",
                500
            );
        }
        $this->verify_host = $verify_host;
    }

    /**
     * Define el tiempo máximo de espera para establecer una conexión inicial.
     *
     * Este método configura el tiempo (en segundos) que cURL esperará para establecer
     * la conexión TCP/IP con el servidor antes de abortar la solicitud.
     * 
     * Si el servidor no responde dentro del tiempo definido, cURL lanzará un error
     * de tipo *"Connection timed out"*.
     *
     * Es importante distinguir entre este parámetro y el tiempo de espera total de ejecución
     * (ver `set_timeout()`), ya que este únicamente aplica al **establecimiento de la conexión**.
     * 
     * @param int $connect_timeout Tiempo máximo en segundos para intentar conectar.  
     *                             Por defecto: `10` segundos.
     * 
     * @return void
     * 
     * @see CURLOPT_CONNECTTIMEOUT
     * @see set_timeout()
     */
    public function set_connect_timeout(int $connect_timeout = 10): void {
        $this->connect_timeout = $connect_timeout;
    }

    /**
     * Define el tiempo máximo total de ejecución de la solicitud HTTP.
     *
     * Este método establece el tiempo máximo (en segundos) que cURL permitirá para la
     * **transferencia completa** de la solicitud, incluyendo la conexión, el envío de datos
     * y la recepción de la respuesta.  
     * 
     * Si la operación excede este tiempo, cURL interrumpirá la ejecución y lanzará un error
     * de tipo *"Operation timed out"*.
     *
     * A diferencia de `set_connect_timeout()`, este parámetro controla el tiempo total de
     * la transacción, no solo la fase de conexión inicial.
     *
     * @param int $timeout Tiempo máximo total de ejecución en segundos.  
     *                     Por defecto: `30` segundos.
     * 
     * @return void
     * 
     * @see CURLOPT_TIMEOUT
     * @see set_connect_timeout()
     */
    public function set_timeout(int $timeout = 30): void {
        $this->timeout = $timeout;
    }

    // -------------------------
    // PETICIÓN HTTP
    // -------------------------

    /**
     * Ejecuta una solicitud HTTP completa utilizando cURL, con soporte para cookies, redirecciones y distintos tipos de contenido.
     *
     * Este método constituye el núcleo del sistema de peticiones HTTP de la clase. 
     * Permite enviar solicitudes a un servidor remoto utilizando distintos métodos HTTP 
     * (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`), gestionando automáticamente:
     *
     * - Archivos de cookies (lectura/escritura en formato Netscape).
     * - Redirecciones y encabezados personalizados.
     * - Cuerpos de solicitud codificados como `application/json`, `application/x-www-form-urlencoded` o `multipart/form-data`.
     * - Archivos adjuntos mediante instancias de `CURLFile`.
     * 
     * Además, valida que el archivo de cookies sea accesible y escribible antes de la ejecución,
     * y gestiona internamente el cierre y la persistencia de la sesión HTTP.
     *
     * ---
     * ### Flujo interno:
     * 1. **Verificación de cookies:** crea el archivo si no existe y valida permisos.
     * 2. **Inicialización de cURL:** configura todas las opciones necesarias según los parámetros internos de la clase.
     * 3. **Detección de tipo de contenido:** el método determina automáticamente si los datos se enviarán como JSON, `multipart/form-data` o `x-www-form-urlencoded`.
     * 4. **Ejecución de la solicitud:** ejecuta la conexión con `curl_exec()`.
     * 5. **Gestión de errores:** lanza una excepción `RuntimeException` ante fallos en cURL.
     * 6. **Persistencia de cookies:** guarda las cookies obtenidas en formato Netscape.
     *
     * ---
     * ### Ejemplo de uso:
     * ```php
     * $request = new Request();
     * $request->set_cookies('/tmp/session.cookies');
     * $request->set_verify_peer(true);
     * 
     * $headers = new HeadersInit([
     *     'Content-Type: application/json',
     *     'Accept: application/json'
     * ]);
     * 
     * $response = $request->request(
     *     url: 'https://api.example.com/data',
     *     method: Request::POST,
     *     headers: $headers,
     *     data: ['name' => 'David', 'file' => new CURLFile('/path/to/file.png')]
     * );
     * ```
     *
     * ---
     * @param string $url       URL del recurso o endpoint remoto.
     * @param string $method    Método HTTP utilizado. Acepta los definidos en las constantes:
     *                          `Request::GET`, `Request::POST`, `Request::PUT`, `Request::PATCH`, `Request::DELETE`.
     * @param HeadersInit|null $headers Conjunto de encabezados HTTP a enviar con la solicitud.  
     *                                  Si es `null`, se aplicarán los valores por defecto definidos en la instancia.
     * @param array $data       Datos o carga útil a enviar.  
     *                          - En `application/json`, se codifica automáticamente con `DLOutput::get_json()`.  
     *                          - En `multipart/form-data`, se soporta el envío de instancias `CURLFile`.  
     *                          - En `x-www-form-urlencoded`, se serializa con `http_build_query()`.
     *
     * @return string|bool      Devuelve el contenido de la respuesta si `CURLOPT_RETURNTRANSFER` está habilitado, 
     *                          o `true`/`false` según el resultado de la operación.
     *
     * @throws RuntimeException Si ocurre un error durante la ejecución de cURL o si el archivo de cookies no es accesible.
     *
     * @see DLOutput::get_json()
     * @see CURLFile
     * @see HeadersInit
     * @see set_cookies()
     * @see set_timeout()
     * @see set_connect_timeout()
     */
    public function request(string $url, string $method = self::GET, ?HeadersInit $headers = null, array $data = []): string|bool {
        if (empty($this->cookies)) {
            $this->set_cookies();
        }

        // Asegura que el archivo de cookies exista
        if (!file_exists($this->cookies)) {
            file_put_contents($this->cookies, "# Netscape HTTP Cookie File" . PHP_EOL);
        }

        if (!is_writable($this->cookies)) {
            throw new RuntimeException("El archivo de cookies no tiene permisos de escritura: {$this->cookies}");
        }

        if (!($this->ch instanceof CurlHandle)) {
            $this->ch = curl_init();
        }

        $current_headers = $headers instanceof HeadersInit ? $headers->get_headers() : [];

        curl_setopt_array($this->ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => $this->return_transfer,
            CURLOPT_HTTPHEADER     => $current_headers,
            CURLOPT_USERAGENT      => $this->user_agent,
            CURLOPT_SSL_VERIFYPEER => $this->verify_peer,
            CURLOPT_SSL_VERIFYHOST => $this->verify_host,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_FOLLOWLOCATION => $this->follow_location,
            CURLOPT_MAXREDIRS      => $this->max_redirect,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_COOKIEJAR      => $this->cookies,
            CURLOPT_COOKIEFILE     => $this->cookies,
            CURLOPT_COOKIESESSION  => false,
            CURLOPT_ENCODING       => '',
            CURLOPT_HEADER         => false,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
        ]);

        $current_method = strtoupper($method);
        if (in_array($current_method, self::METHODS, true)) {
            $is_json = false;
            $is_multipart = false;

            foreach ($current_headers as $h) {
                if (stripos($h, 'Content-Type: application/json') !== false) {
                    $is_json = true;
                    break;
                }
                if (stripos($h, 'Content-Type: multipart/form-data') !== false) {
                    $is_multipart = true;
                    break;
                }
            }

            // Detección automática si hay CURLFile
            foreach ($data as $value) {
                if ($value instanceof CURLFile) {
                    $is_multipart = true;
                    break;
                }
            }

            if ($is_json) {
                $payload = DLOutput::get_json(content: $data, pretty: true);
            } elseif ($is_multipart) {
                // multipart: cURL lo maneja automáticamente si pasas un array con CURLFile
                $payload = $data;
            } else {
                $payload = http_build_query($data);
            }

            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $payload);
        }


        $response = curl_exec($this->ch);

        if ($response === false) {
            throw new RuntimeException("Error en cURL: " . curl_error($this->ch));
        }

        // Guardar cookies si se obtuvieron
        $cookies = curl_getinfo($this->ch, CURLINFO_COOKIELIST);
        if (is_array($cookies) && count($cookies) > 0) {
            $cookie_dump = "# Netscape HTTP Cookie File" . PHP_EOL;
            foreach ($cookies as $line) {
                $cookie_dump .= $line . PHP_EOL;
            }
            file_put_contents($this->cookies, $cookie_dump, LOCK_EX);
        }

        $response_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        http_response_code($response_code);

        return $response;
    }

    /**
     * Ejecuta una solicitud HTTP de alto nivel utilizando la configuración definida en un objeto `RequestInit`.
     *
     * Este método actúa como una capa intermedia que simplifica la ejecución de peticiones HTTP,
     * delegando el trabajo principal al método interno {@see request()}.
     * 
     * A diferencia de `request()`, que requiere parámetros individuales,
     * `fetch()` acepta una instancia de {@see RequestInit}, la cual encapsula:
     * - El método HTTP (`GET`, `POST`, `PUT`, etc.).
     * - Los encabezados HTTP personalizados.
     * - El cuerpo o carga útil (`body`) de la solicitud.
     *
     * Este diseño sigue la convención utilizada en entornos modernos como el estándar **Fetch API** de JavaScript,
     * proporcionando una forma más declarativa y estructurada de inicializar solicitudes HTTP.
     *
     * ---
     * ### Flujo interno:
     * 1. Extrae los valores de `$init` (método, encabezados y cuerpo).
     * 2. Invoca internamente a {@see request()} con dichos parámetros.
     * 3. Devuelve la respuesta obtenida del servidor como una cadena.
     *
     * ---
     * ### Ejemplo de uso:
     * ```php
     * $headers = new HeadersInit([
     *     'Content-Type: application/x-www-form-urlencoded',
     *     'Accept: text/html'
     * ]);
     *
     * $init = new RequestInit();
     * $init->set_method(Request::POST);
     * $init->set_headers($headers);
     * $init->set_body([
     *     'username' => 'david',
     *     'password' => '12345'
     * ]);
     *
     * $response = $this->fetch('https://example.com/login', $init);
     * echo $response;
     * ```
     *
     * ---
     * @param string $action URL o endpoint de destino donde se enviará la solicitud.
     * @param RequestInit $init Objeto que define los parámetros de inicialización de la petición:
     *                          método HTTP, encabezados y cuerpo.
     *
     * @return string Devuelve la respuesta completa del servidor remoto como una cadena.
     *
     * @throws RuntimeException Si ocurre un error durante la ejecución interna del método {@see request()}.
     *
     * @see request()
     * @see RequestInit
     * @see HeadersInit
     */
    public function fetch(string $action, RequestInit $init): string {
        return (string) $this->request($action, $init->method, $init->headers, $init->body);
    }

    // -------------------------
    // MANEJO DE COOKIES
    // -------------------------

    /**
     * Define la ruta del archivo de almacenamiento de cookies para las solicitudes HTTP.
     *
     * Este método establece la ubicación donde se guardarán y leerán las cookies utilizadas por cURL.
     * Si no se especifica un parámetro de ruta, se asigna por defecto un archivo temporal llamado
     * `dlunire-cookie.txt` en el directorio del sistema definido por `sys_get_temp_dir()`.
     *
     * ---
     * ### Detalles de funcionamiento:
     * - Si `$path` es proporcionado, se utiliza dicha ruta explícitamente.
     * - Si `$path` es `null`, se genera una ruta temporal automática:
     *   ```
     *   {directorio_temporal_del_sistema}/dlunire-cookie.txt
     *   ```
     * - La ruta establecida se almacena internamente en la propiedad `$this->cookies`.
     *
     * Este archivo de cookies sigue el formato **Netscape HTTP Cookie File**, lo que garantiza
     * compatibilidad con las operaciones nativas de cURL para persistencia de sesión entre múltiples solicitudes.
     *
     * ---
     * ### Ejemplo de uso:
     * ```php
     * // Ruta personalizada
     * $this->set_cookies('/var/www/cache/session_cookies.txt');
     *
     * // O bien, usar la ruta temporal predeterminada
     * $this->set_cookies();
     * ```
     *
     * ---
     * @param string|null $path Ruta absoluta o relativa del archivo donde se almacenarán las cookies.
     *                          Si es `null`, se utilizará una ruta temporal del sistema.
     *
     * @return void
     *
     * @see get_cookies_path()
     * @see delete_cookies()
     */
    public function set_cookies(?string $path = null): void {
        $separator = DIRECTORY_SEPARATOR;
        $default = sys_get_temp_dir() . "{$separator}dlunire-cookie.txt";
        $this->cookies = $path ? $path : $default;
    }

    /**
     * Obtiene la ruta actual del archivo de cookies utilizado por la instancia.
     *
     * Este método devuelve la ubicación del archivo donde se almacenan las cookies
     * de sesión gestionadas por cURL.  
     * 
     * Si no se ha definido previamente una ruta mediante `set_cookies()`, el valor retornado será `null`.
     * 
     * ---
     * ### Detalles de funcionamiento:
     * - La ruta retornada corresponde al valor actual de la propiedad interna `$this->cookies`.
     * - Puede utilizarse para depurar o verificar la persistencia de cookies entre solicitudes HTTP.
     * - Si se desea regenerar o cambiar la ruta, puede usarse `set_cookies()` nuevamente.
     *
     * ---
     * ### Ejemplo de uso:
     * ```php
     * $request = new MyHttpClient();
     * $request->set_cookies('/tmp/mis_cookies.txt');
     *
     * echo $request->get_cookies_path();
     * // Salida: /tmp/mis_cookies.txt
     * ```
     *
     * ---
     * @return string|null Ruta absoluta o relativa del archivo de cookies actualmente en uso,
     *                     o `null` si no ha sido definida.
     *
     * @see set_cookies()
     * @see delete_cookies()
     */
    public function get_cookies_path(): ?string {
        return $this->cookies ?? null;
    }

    /**
     * Elimina el archivo de cookies asociado a la instancia actual.
     *
     * Este método borra el archivo físico donde se almacenan las cookies
     * utilizadas por las solicitudes HTTP gestionadas mediante cURL.
     * Si el archivo no existe o la ruta no es válida, la operación se ignora
     * y se devuelve `false`.
     *
     * ---
     * ### Detalles de funcionamiento:
     * - Recupera la ruta del archivo de cookies mediante `get_cookies_path()`.
     * - Verifica que dicha ruta sea una cadena válida y que el archivo exista.
     * - Utiliza `unlink()` para eliminar el archivo, suprimiendo posibles advertencias.
     * - No lanza excepciones si el archivo no puede eliminarse; simplemente retorna `false`.
     *
     * ---
     * ### Nota de seguridad:
     * - Se recomienda invocar este método al finalizar una sesión HTTP para evitar
     *   fugas de información sensible en entornos compartidos.
     * - El archivo de cookies puede contener tokens de sesión o encabezados de autenticación.
     *
     * ---
     * ### Ejemplo de uso:
     * ```php
     * $client = new HttpClient();
     * $client->set_cookies('/tmp/sesion.txt');
     *
     * // ... se realizan solicitudes HTTP ...
     *
     * if ($client->delete_cookies()) {
     *     echo "Archivo de cookies eliminado correctamente.";
     * } else {
     *     echo "No se pudo eliminar el archivo de cookies.";
     * }
     * ```
     *
     * ---
     * @return bool `true` si el archivo de cookies fue eliminado correctamente,
     *              `false` en caso contrario o si el archivo no existía.
     *
     * @see set_cookies()
     * @see get_cookies_path()
     */
    public function delete_cookies(): bool {
        $path = $this->get_cookies_path();
        return is_string($path) && file_exists($path) ? @unlink($path) : false;
    }
}
