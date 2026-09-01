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
     * Crea la sesión del usuario.
     *
     * @param array $data
     * @return AuthApps
     * 
     * @throws RuntimeException
     * 
     * // TODO: Observación: luego se creará el error semántico para este caso.
     */
    protected function auth(array $data = []): AuthApps {
        if (!DLServer::is_post()) {
            throw new RuntimeException("auth(...): Debe utilizar el verbo HTTP POST para crear datos de sesión", 400);
        }

        /** @var AuthApps $auth */
        $auth = ($this->field !== null)
            ? new AuthApps($this->field)
            : new AuthApps();

        /** @var boolean $created_session */
        $created_session = $auth->create_session_data($data);

        if (!$created_session) {
            throw new RuntimeException("auth(...): Error desconocido al crear los datos de la sesión. Revise que tenga permiso de escritura.", 500);
        }

        return $auth;
    }
}
