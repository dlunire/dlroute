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

use DLAuth\Auth\Auth;
use DLRoute\Core\Auth\AuthApps;
use DLRoute\Requests\DLOutput;
use DLRoute\Requests\DLRequest;
use DLRoute\Requests\DLUpload;
use DLRoute\Server\DLServer;
use DLRoute\Traits\Request;
use DLRoute\Validates\DLValidates;
use RuntimeException;

/**
 * Controlador base
 * 
 * @package DLRoute\Config
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license AGPL-3.0 license
 */
abstract class Controller {

    use DLValidates, DLUpload, Request;

    /**
     * Nombre del campo de lo datos de la sesión
     *
     * @var string|null $field
     */
    private ?string $field = null;

    /**
     * Instancia del autenticador de aplicaciones.
     *
     * Contiene la instancia de {@see AuthApps} utilizada para gestionar
     * el estado de autenticación de la aplicación, o `null` cuando la
     * autenticación no se encuentra habilitada.
     *
     * @var AuthApps|null
     */
    private readonly ?AuthApps $auth;

    /**
     * Procesa las peticiones del usuario.
     *
     * @var DLRequest
     */
    protected DLRequest $request;

    /**
     * Crea una instancia del autenticador.
     *
     * Inicializa la instancia de {@see DLRequest} asociada a la solicitud actual
     * y configura opcionalmente el sistema de autenticación de la aplicación.
     *
     * @param bool $auth Indica si debe habilitarse la autenticación de la aplicación.
     * @param string|null $field Campo o clave donde se almacenarán los datos de la sesión.
     *
     * @throws RuntimeException Si {@see $field} es una cadena vacía o contiene únicamente
     *                          espacios en blanco.
     */
    public function __construct(bool $auth = false, ?string $field = null) {
        $this->request = DLRequest::get_instance();
        $this->set_auth($auth, $field);
    }

    /**
     * Configura el sistema de autenticación de la aplicación.
     *
     * Valida el campo de sesión proporcionado y, cuando la autenticación está habilitada, inicializa una
     * instancia de {@see AuthApps}. Si la autenticación no está habilitada, la propiedad {@see $auth} se
     * establece en `null`.
     *
     * Cuando no se especifica un campo, {@see AuthApps} utilizará su configuración predeterminada.
     *
     * @param bool $auth Indica si debe habilitarse la autenticación de la aplicación.
     * @param string|null $field Campo o clave donde se almacenarán los datos de la sesión.
     *
     * @return void
     *
     * @throws RuntimeException Si {@see $field} es una cadena vacía o contiene únicamente
     *                          espacios en blanco.
     */
    private function set_auth(bool $auth = false, ?string $field = null): void {
        if (\is_string($field) && \trim($field) === '') {
            throw new RuntimeException("__construct(...): el campo '\$field' no debe estar vacío. Puede optar por dejarlo nulo", 500);
        }

        if (!$auth) {
            $this->auth = null;
            return;
        }

        $this->auth = \is_string($field)
            ? new AuthApps($field)
            : new AuthApps();
    }

    /**

     * Devuelve la instancia del sistema de autenticación.
     *
     * Requiere que el sistema de autenticación haya sido habilitado previamente mediante el constructor.
     * Si la autenticación no fue configurada, el método no realiza ninguna inicialización implícita.
     *
     * @return AuthApps Instancia configurada del sistema de autenticación.
     *
     * @throws RuntimeException Si el sistema de autenticación no está configurado en el controlador.
     */
    protected function get_auth(): AuthApps {

        if (!($this->auth instanceof AuthApps)) {
            throw new RuntimeException(
                "Controller::get_auth(...): El sistema de autenticación no está configurado. "
                    . "Habilítelo en el constructor del controlador.",
                500
            );
        }

        return $this->auth;
    }


    /**
     * Devuelve una dirección IP candidata del cliente HTTP
     *
     * @return string
     */
    protected function get_ip(): string {
        return DLServer::get_ipaddress();
    }

    /**
     * Devuelve el hombre de host con puerto incluido en formato HTTP, es decir,
     * de una forma similar a esta: http://localhost:3000/
     *
     * @return string
     */
    protected function get_host(): string {
        return DLServer::get_http_host();
    }

    /**
     * Convierte un objeto o un array en una cadena de texto en formato JSON y la devuelve.
     *
     * Esta función toma un objeto o array y lo convierte en una cadena de texto en formato JSON.
     *
     * @param object|array $data El contenido que se va a parsear.
     * @param bool $pretty Indica si la salida en formato JSON debe tener formato legible o no.
     * @return string La cadena de texto en formato JSON resultante.
     */
    protected function get_json(array|object $data, bool $pretty = false): string {
        return DLOutput::get_json($data, $pretty);
    }

    /**
     * Carga los datos de autenticación de la sesión.
     *
     * Requiere que el sistema de autenticación haya sido habilitado previamente mediante el constructor. Los
     * datos proporcionados se cargan en la sesión mediante la instancia configurada de {@see AuthApps}.
     *
     * @param array $data Datos que serán cargados en la sesión de autenticación.
     *
     * @return AuthApps Instancia del autenticador con los datos de sesión cargados.
     *
     * @throws RuntimeException Si el sistema de autenticación no fue configurado o si los datos
     *                          de la sesión no pudieron ser cargados.
     */
    protected function save_auth_data(array $data = []): AuthApps {
        if (!($this->auth instanceof AuthApps)) {
            throw new RuntimeException(
                "load_auth_data(...): El sistema de autenticación no está configurado. "
                    . "Habilítelo en el constructor del controlador.",
                500
            );
        }

        /** @var bool $created */
        $created = $this->auth->create_session_data($data);

        if (!$created) {
            throw new RuntimeException(
                "load_auth_data(...): No fue posible cargar los datos de la sesión. "
                    . "Verifique la configuración y los permisos de almacenamiento de la sesión.",
                500
            );
        }

        return $this->auth;
    }
}
