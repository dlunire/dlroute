<?php

namespace DLRoute\Interfaces;

/**
 * Establece el sistema de ruta hacia los recursos críticos del sistema.
 * 
 * @package DLRoute\Interface
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
interface ResourceInterface {

    /**
     * Incorpora directamente, el contenido CSS en código CSS, a menos que, `$external` valga `true`
     *
     * @link [`ResourceManager::css`](https://github.com/dlunamontilla/dlroute/blob/master/docs/ResourceManager.md#m%C3%A9todo-resourcemanagercss "DLRoute - Ejemplo de uso de ResourceManager::css")
     * @param string $path Ruta relativa del archivo CSS.
     * @param bool $external Opcional. Permite indicar si se necesita para uso externo.
     * 
     * Por defecto, su valor es `false` y eso significa que se tomará el contenido de un
     * archivo y los devolverá contenido CSS entre las etiquetas HTML `<style>...</style>`.
     * Sin embargo, cuando es `true`, entonces, su contenido se devolverá directamente, es
     * decir, sin las etiquetas antes mencionadas.
     * 
     * @return string
     */
    public static function css(string $path, bool $external = false): string;

    /**
     * Incorpora código JavaScript, por defecto, entre las etiquetas `<script...>...</script>`,
     * a menos, que se indique lo contrario en el segundo parámetro.
     * 
     * @link [`ResourceManager::js`](https://github.com/dlunamontilla/dlroute/blob/master/docs/ResourceManager.md#m%C3%A9todo-resourcemanagerjs "DLRoute - Ejemplo de uso de ResourceManager::js")
     *
     * @param string $path Ruta relativa del archivo.
     * @param ?array $options Es un array asociativo que solo permiten las siguientes claves:
     * 
     * ### Claves del segundo parámetro
     * 
     * - **`external`:** Para indicar con `true` si queremos que una salida para uso externo
     *  o si el código JavaScript se incorpora directamente entre las etiquetas `<script...>...</script>`
     *  con el valor `false` (valor por defecto).
     * 
     * - **`behavior_attributes`:** Permite cualquiera de los siguientes valores: `defer` o `async`.
     *  El primero es para indicar si deseamos que nuestro _script_ cargue diferido o 
     *  asíncrono con la segunda.
     * 
     * - **`type`:** Permite establer si el _script_ será tratado como un módulo o no, por ejemplo,
     *  `type="module"` o `type="text/javascript"`.
     * 
     * - **`token`:** Permite establecer el token de seguridad, por lo tanto, haría que las
     *  etiquetas `<script...>...</script>` tengan el siguiente atributo con su valor:  
     *  `nonce="69c3b8278585e68071b5ce7035ea52a80d76408be7f04949ee1b0fd7b5927898"`
     * 
     * > **Importante:** si `external` vale `true`, es decir, `external => true`, las demás
     * > claves quedarán sin efecto.
     * 
     * @return string
     */
    public static function js(string $path, ?array $options = []): string;

    /**
     * Procesa las imágenes. Este método permite definir si la imagen se cargará directamente
     * como archivo binario o `base64` en el código HTML.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $output = ResourceManager::image('ruta/a/la/imagen.jpg', [
     *  "title" => "Título de la imagen",
     *  "html" => true
     * ]);
     *```
     * 
     * Si `html` es `true`, entonces, el contenido de la imagen se colocará directamente en el código en formato
     * base 64, en lugar de su ruta.
     * 
     * @link [`ResourceManager::image`](https://github.com/dlunamontilla/dlroute/blob/master/docs/ResourceManager.md#m%C3%A9todo-resourcemanagerimage "DLRoute - Ejemplo de uso de ResourceManager::image")
     * 
     * @param string $path Ruta de la imagen
     * @param object|array|null $config Configuración de la imagen.
     * @return string
     */
    public static function image(string $path, object|array|null $config = null): string|false;

    /**
     * Establece URL completa al recurso al que se apunta.
     * @link [`ResourceManager::asset`](https://github.com/dlunamontilla/dlroute/blob/master/docs/ResourceManager.md#m%C3%A9todo-resourcemanagerasset "DLRoute - Ejemplo de uso de ResourceManager::asset")
     *
     * @param string $filename Archivo al que se apunta.
     * @return string
     */
    public static function asset(string $filename): string;
}
