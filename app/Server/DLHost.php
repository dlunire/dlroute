<?php

namespace DLRoute\Server;

/**
 * @package DLRoute
 * @version 1.0.1
 * @author David E Luna <davidlunamontilla@gmail.com>
 * @copyright (c) 2020 - David E Luna M
 * @license MIT
 * 
 * @method static string get_hostname() Devuelve el nombre del HOST
 * @method static string get_domain() Devuelve el nombre de dominio actual
 * @method static boolean is_https() Indica si se está utilizando el protocolo HTTPs.
 * @method void https() Obliga a la aplicación a utilizar HTTPs
 */
final class DLHost {
    private array $hostnames = [];

    /**
     * Ingrese los nombres de hosts a los que se le obligarán a usar HTTPS
     *
     * @param array $hostnames
     */
    public function __construct(array $hostnames = []) {
        if (count($hostnames) > 0) {
            foreach ($hostnames as $host) {
                array_push($this->hostnames, $host);
            }
        }
    }

    /**
     * Devuelve el nombre actual de host
     * 
     * @return string
     */
    public static function get_hostname(): string {
        return DLServer::get_hostname();
    }

    /**
     * Devuelve el dominio del sitio Web
     *
     * @return string
     */
    public static function get_domain(): string {
        /** @var string $host */
        $host = self::get_hostname();
        $host = preg_replace("/:{1}[0-9]+$/", "", $host);

        return $host ?? '';
    }

    /**
     * Determina si el usuario está accediendo al sitio web con el protocolo HTTPS activado o no.
     *
     * @return bool
     */
    public static function is_https(): bool {
        /** @var bool $isHTTPS */
        $isHTTPS = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on';

        /** @var string|null $protocol */
        $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;

        if (!$isHTTPS) {
            $isHTTPS = $protocol && strtolower($protocol) === 'https';
        }

        // Revisa otras cabeceras comunes para proxies inversos
        if (!$isHTTPS) {
            $isHTTPS = isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on';
        }

        if (!$isHTTPS) {
            $isHTTPS = isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) === 'on';
        }

        return boolval($isHTTPS);
    }

    /**
     * Obliga a redigir una URL con el protocolo HTTPs
     *
     * @return void
     */
    public function https(): void {
        $server_name = (string) strtolower($_SERVER['SERVER_NAME']);
        $https = self::is_https();
        $url = (string) $_SERVER['REQUEST_URI'];

        if (!count($this->hostnames) > 0)
            return;

        foreach ($this->hostnames as $host) {
            if ($server_name === $host && !$https) {
                $url = "https://{$server_name}{$url}";
                header("Location: $url");
            }
        }
    }
}
