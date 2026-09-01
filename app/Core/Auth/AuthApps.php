<?php

declare(strict_types=1);

namespace DLRoute\Core\Auth;

use DLAuth\Auth\DLAuth;
use DLAuth\Data\SessionData;
use Override;

final class AuthApps extends DLAuth {
    /**
     * Marca las rutas que deben ser autenticadas.
     *
     * @var boolean $mark_routes
     */
    private bool $mark_routes = false;

    public function __construct(string $field = '__DLUNIRE__') {
        parent::__construct(field: $field);
    }

    #[Override]
    public function authenticated(callable $fn): void {
        parent::authenticated($fn);
    }

    #[Override]
    public function unauthenticated(callable $fn): void {
        parent::unauthenticated($fn);
    }

    /**
     * Si la ruta no se encuentra autenticada, pero se desea dejar un mensaje al
     * programador o al usuario final sobre el estado de la autenticación.
     *
     * @param callable $fn
     * @return void
     */
    public function authorized(callable $fn): void {
        /** @var SessionData $session */
        $session = $this->get_session_data();

        if ($session->is_valid_session) {
            $fn($session);
        }
    }

    public function __get(string $field): string {
        return "Valor: {$field}";
    }
}
