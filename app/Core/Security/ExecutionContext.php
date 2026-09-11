<?php

declare(strict_types=1);

namespace DLRoute\Core\Security;

/**
 * Discriminador determinista de entorno de ejecución para DLRoute.
 *
 * Analiza la firma de las cabeceras HTTP presentes en la superglobal `$_SERVER`
 * para clasificar el origen de la petición en un tiempo de ejecución $O(1)$.
 * 
 * Permite diferenciar entre un cliente de navegador web (*Browser Context*)
 * y un cliente de servidor a servidor (*Server-to-Server Context*), garantizando
 * la restricción de claves secretas (`sk_...`) e impidiendo su consumo vía frontend.
 *
 * @package DLRoute\Core\Security
 * @author  Álvaro
 * @final
 */
final class ExecutionContext {

    /**
     * Determina si la petición actual fue iniciada por un navegador web.
     *
     * Evalúa firmas de cabeceras inmutables que los motores Chromium, Gecko y WebKit
     * inyectan obligatoriamente a nivel de protocolo, tanto en entornos TLS (HTTPS)
     * como no cifrados (HTTP).
     *
     * Criterios de evaluación:
     * - Presencia de metadatos de Fetch (`Sec-Fetch-Mode`) o peticiones de actualización (`Upgrade-Insecure-Requests`).
     * - Presencia de *Client Hints* (`Sec-CH-UA`) o cabecera de origen dinámico (`Origin`).
     * - Coincidencia de tipo MIME con renderizado de documentos HTML (`text/html` en `Accept`).
     *
     * @return bool `true` si la petición se origina en un navegador web; `false` si proviene de un cliente HTTP puro (cURL, CLI, backend).
     */
    public static function is_browser(): bool {
        // 1. Cabeceras inmutables inyectadas por el motor del navegador (HTTPS / HTTP plano)
        if (isset($_SERVER['HTTP_SEC_FETCH_MODE']) || isset($_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'])) {
            return true;
        }

        // 2. Client Hints de User-Agent (Chromium / WebKit) o cabecera de origen
        if (isset($_SERVER['HTTP_SEC_CH_UA']) || isset($_SERVER['HTTP_ORIGIN'])) {
            return true;
        }

        // 3. Negociación de contenido con soporte para renderizado de documentos visuales
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (\str_contains($accept, 'text/html')) {
            return true;
        }

        return false;
    }

    /**
     * Determina si la petición se ejecutó asíncronamente (Fetch API / AJAX / XHR) dentro de un navegador.
     *
     * Descarta navegaciones directas de documento a nivel superior (como la barra de direcciones o enlaces `<a>`)
     * e identifica solicitudes disparadas por JavaScript (`fetch()`, `XMLHttpRequest`, Axios, etc.).
     *
     * Criterios de evaluación:
     * - Requiere que `self::is_browser()` sea `true`.
     * - Evalúa si `Sec-Fetch-Mode` difiere de `navigate`.
     * - Identifica la cabecera tradicional de peticiones asíncronas `X-Requested-With: XMLHttpRequest`.
     *
     * @return bool `true` si la petición fue disparada por JavaScript desde un navegador; `false` en caso contrario.
     */
    public static function is_browser_fetch(): bool {
        if (!self::is_browser()) {
            return false;
        }

        // 1. Metadatos Fetch: Cualquier modo distinto a 'navigate' representa una llamada programática en JS
        if (isset($_SERVER['HTTP_SEC_FETCH_MODE'])) {
            return $_SERVER['HTTP_SEC_FETCH_MODE'] !== 'navigate';
        }

        // 2. Firma clásica de librerías AJAX (Axios, jQuery, XMLHttpRequest)
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return \strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }

        return false;
    }
}
