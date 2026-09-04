<?php

namespace DLRoute\Test;

use DLRoute\Config\Controller;
use DLRoute\Core\Auth\AuthApps;

/**
 * Esta es una zona de prueba que se utiliza para probar las capacidades del enrutador
 * 
 * @package DLRoute\Test
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 DLUnire
 * @license AGPL-3.0-or-later
 */
final class AuthController extends Controller {

    public function __construct() {
        parent::__construct(auth: true);
    }

    public function auth(): array {
        /**
         * Datos del autenticador de DLUnire
         * 
         * @var AuthApps $auth
         */
        $auth = $this->get_auth();

        $auth->create_session_data([
            "name" => "David Eduardo",
            "lastname" => "Luna Montilla",
            "age" => 41
        ]);

        return [
            "status" => true,
            "success" => "Sessión iniciada correctamente"
        ];
    }

    /**
     * Permite verificar la sessión para el desarrollo del sistema de autenticación
     * para DLRoute.
     *
     * @return array
     */
    public function check(object $params): array {
        /** @var mixed $value */
        $value = null;

        return [
            "status" => $this->get_auth()->get_session_data(),
            "A" => \json_decode($this->to_json($value)),
            "B" => $this->to_json($value),
            "params" => $params
        ];
    }
}
