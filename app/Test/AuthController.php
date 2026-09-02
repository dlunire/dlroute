<?php

namespace DLRoute\Test;

use DLRoute\Config\Controller;
use DLRoute\Core\Auth\AuthApps;

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
            "success" => "Sessión iniciada correctamente",
            // "auth" => $auth
        ];
    }

    public function check(): array {
        return [
            "status" => $this->get_auth()->get_session_data(),
            "test" => $this->to_json(555)
        ];
    }
}
