# Capítulo 1 — Análisis léxico de la ruta de la petición

> **Estado: en desarrollo** (rama `dev-automaton`).
> Este capítulo cubre solo cómo DLRoute v3 **lee y descompone** la URI que llega por HTTP o por CLI.
> El registro y el despacho de rutas aún no están implementados; se documentarán en capítulos posteriores.

## 1. Qué problema resuelve

Antes de comparar una petición con cualquier ruta registrada, DLRoute necesita convertir la URI en datos
estructurados. Lo hace con un autómata finito que recorre la URI **byte a byte**, sin expresiones regulares, y
produce:

- una lista de **tokens** (uno por segmento de ruta),
- la URI **original** y la URI **decodificada**,
- la separación entre **directorio lógico** (`dir`) y **ruta** (`route`),
- la **cadena de consulta**, entregada sin procesar al autómata de query string.

## 2. Clases involucradas

| Clase | Namespace | Responsabilidad |
| --- | --- | --- |
| `RouterLexer` (abstracta) | `DLRoute\Core\Routing\Automaton\Route` | Autómata base: tokeniza, valida bytes, decodifica `%XX`, captura la cadena de consulta |
| `RequestURILexer` | `DLRoute\Core\Routing\Automaton\Route` | Extiende `RouterLexer` para la URI de la petición; resuelve `dir` y `route` |
| `ProcessedURI` | `DLRoute\Core\Data\RouteData` | URI procesada en su forma original y decodificada |
| `RouteResolution` | `DLRoute\Core\Data\RouteData` | Resultado: `uri`, `dir`, `route`, `query_string`, `query_param_values` |
| `LexerSymbolInterface` | `DLRoute\Interfaces\Routing` | Constantes y tablas de símbolos (`REJECTED_BYTES`, `PERCENT_ENCODING_MAP`, `ESCAPE_SEQUENCES`, `REJECTED_LEXEME`) |

## 3. De dónde sale la URI

`RequestURILexer` toma la URI según el entorno:

| Entorno | Fuente |
| --- | --- |
| Servidor web | `$_SERVER['REQUEST_URI']`, o `/` si no está definida |
| CLI (`PHP_SAPI === 'cli'`) | Opción `-r` o `--route`; si no se indica ninguna, `/` |

Formas válidas por CLI: `-r <ruta>`, `-r=<ruta>`, `--route <ruta>`, `--route=<ruta>`. Si se indican ambas,
tiene prioridad `-r`. La ruta se pasa **sin decodificar**: un espacio debe enviarse como `%20`.

```bash
php public/index.php -r /ciencias%20computacionales
php public/index.php --route=/api/v1/users/42
```

## 4. Cómo se obtiene la instancia

```php
use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;

$lexer = RequestURILexer::from(); // instancia única (Singleton con inicialización diferida)
$lexer = RequestURILexer::new();  // instancia nueva; vuelve a analizar la URI y no reemplaza la de from()
```

El constructor es privado. Para el uso habitual durante una petición, use `from()`: evita repetir el análisis.

## 5. Flujo del análisis

```text
URI cruda
  │  trim()
  ▼
scanner()  ── recorre byte a byte ── rechaza bytes de REJECTED_BYTES (403)
  │
  ▼
tokenizer() ── next_delimiter() ── corta el segmento en el siguiente "/" o en el primer "?"
  │              └─ decode_percent() ── decodifica %XX durante el recorrido
  ▼
tokens[]  ──►  build_processed_uri()  ──►  ProcessedURI (original + decodificada)
  │
  ▼
load_route_resolution() ── separa dir / route ── QueryParamComposer ──► RouteResolution
```

## 6. Estructura de un token

`get_tokens()` devuelve un array de tokens. Cada token tiene:

| Clave | Contenido |
| --- | --- |
| `lexeme` | Segmento tal como llegó, sin decodificar |
| `decoded_lexeme` | Segmento decodificado durante el análisis léxico |
| `length` | Longitud en bytes del lexema original |
| `optional` | `true` si el penúltimo byte del lexema es `?` (relevante en rutas registradas; en los ejemplos de la petición es `false`) |
| `tokentype` | `TokenType::TEXT_PLAIN` o `TokenType::PARAM` |
| `offset` | Posición del cursor al emitir el token |
| `method` | Método HTTP asociado; en la URI de la petición es `null` |

Los tokens **no incluyen las barras**: `/api/v1/users/42` produce los lexemas `api`, `v1`, `users` y `42`.
Las barras repetidas o finales no generan tokens: `//a//b/` se normaliza a `/a/b`.

## 7. Reglas del autómata

### 7.1 Separadores

- `/` separa segmentos.
- El **primer `?`** fuera de un parámetro termina el análisis de la ruta: todo lo que sigue es la cadena de
  consulta (sin incluir el `?`), que se entrega sin procesar a `QueryParamComposer`.

### 7.2 Bytes rechazados

Si aparece un byte de `REJECTED_BYTES`, se lanza `RouteException` con código **403**:

```text
Byte no válido (0x20), desplazamiento 2.
```

La tabla incluye los controles `0x00`–`0x1F`, el espacio (`0x20`), y también `"`, `;`, `<`, `>`, `\`, `^`,
`` ` ``, `|`, `0x7F`, `0xC0`, `0xC1` y `0xF5`–`0xFF`. La lista completa está en
`LexerSymbolInterface::REJECTED_BYTES`.

### 7.3 Decodificación porcentual (`%XX`)

La decodifica el propio autómata mientras recorre el segmento; no se usan `rawurldecode()` ni `urldecode()`.

| Entrada | `lexeme` | `decoded_lexeme` |
| --- | --- | --- |
| `/ciencias%20computacionales` | `ciencias%20computacionales` | `ciencias computacionales` |
| `/caf%C3%A9` | `caf%C3%A9` | `café` |
| `/x%00y` | `x%00y` | `x_y` (los controles se sustituyen por `_`) |
| `/a%2Fb` | `a%2Fb` | `a\\/b` (la barra se escapa como `\\/`) |
| `/a%zzb` | `a%zzb` | `a%zzb` (par no hexadecimal: queda crudo) |
| `/a%2` | `a%2` | `a%2` (secuencia incompleta: queda cruda) |

Reglas:

- Las letras hexadecimales `A–F` se normalizan a minúscula antes de consultar `PERCENT_ENCODING_MAP`.
- Un `%XX` válido se sustituye por su byte; si el byte está en `ESCAPE_SEQUENCES`, se escapa o se sustituye
  (`/`, `\`, `$`, `?`, `#` y varios controles).
- Una secuencia incompleta o ausente del mapa **no lanza excepción**: el `%` queda como dato crudo.
- La cadena de consulta **no** se decodifica aquí; va tal cual en ambas URIs.

### 7.4 Segmentos `.` y `..`

Se rechazan con **403** en cualquier posición, incluso después de que la resolución de directorios haya
terminado. Se comprueban tanto el lexema como el lexema decodificado, así que `%2e%2e` y `%2E%2E` también se
rechazan:

```text
El segmento de directorio '..' no está permitido
```

### 7.5 Llaves `{` y `}` en la petición (comportamiento actual)

`{` y `}` no están en `REJECTED_BYTES`, así que una petición con llaves literales se acepta y el segmento
completo se clasifica como `PARAM`; en cambio, la forma codificada se trata como texto:

| Entrada | `lexeme` | `decoded_lexeme` | `tokentype` |
| --- | --- | --- | --- |
| `/users/{id}` | `{id}` | `{id}` | `PARAM` |
| `/users/%7Bid%7D` | `%7Bid%7D` | `{id}` | `TEXT_PLAIN` |

> **Pendiente de decisión:** este comportamiento puede cambiar, porque el diseño contempla que las llaves
> sean válidas al registrar rutas pero no en la petición.

## 8. Resolución: `dir` y `route`

`load_route_resolution()` decide qué parte de la URI corresponde a directorios físicos y cuál es la ruta
lógica que debe resolver DLRoute.

1. Parte del directorio real del punto de entrada (el que contiene el script, p. ej. `public/`).
2. Cada segmento inicial se comprueba con `is_dir()` sobre la ruta construida. Mientras exista, pasa a `dir`.
   Los enlaces simbólicos a directorios se siguen y pueden encadenarse.
3. El primer segmento que **no** es directorio, y todos los posteriores, pasan a `route`, existan o no
   directorios con esos nombres.
4. Los segmentos se comparan **sin decodificar**: `%2e%2e` es un nombre literal, no `..`.

Ejemplo, con `public/api/v1/` existente en disco:

```bash
php public/index.php -r /api/v1/users/42
```

| Campo | Valor |
| --- | --- |
| `uri` | `/api/v1/users/42` |
| `dir` | `/api/v1` |
| `route` | `/users/42` |

Sin esos directorios, `dir` es `/` y `route` es `/api/v1/users/42`. La resolución no infiere el prefijo de
montaje comparando el punto de entrada con la URI: solo consulta el sistema de archivos.

## 9. Cadena de consulta

```bash
php public/index.php -r "/api/v1/users/42?x=1&y=a%20b"
```

| Campo | Valor |
| --- | --- |
| `route` | `/users/42` |
| `query_string` | `x=1&y=a%20b` |
| `uri` y `decoded_uri` | conservan `?x=1&y=a%20b` sin decodificar |
| `query_param_values` | 2 valores (`x` e `y`) |

El análisis de pares `nombre=valor` lo hace `QueryParamComposer`, que se documentará en un capítulo propio.

## 10. API pública de `RequestURILexer`

| Método | Devuelve |
| --- | --- |
| `from()` / `new()` | Instancia única / instancia nueva |
| `get_uri()` | URI procesada (original) |
| `get_decoded_uri()` | URI procesada con los segmentos decodificados |
| `get_dir()` | Directorio lógico base (`RouteResolution::$dir`) |
| `get_route()` | Ruta lógica (`RouteResolution::$route`), sin decodificar |
| `get_route_resolution()` | Objeto `RouteResolution` completo |
| `get_tokens()` | Tokens capturados |

Para reconstruir la ruta decodificada use `get_decoded_uri()`; `get_route()` conserva los lexemas tal como
llegaron.

## 11. Errores

| Situación | Excepción | Código |
| --- | --- | --- |
| Byte de `REJECTED_BYTES` | `RouteException` | 403 |
| Segmento `.` o `..` (también codificados) | `RouteException` | 403 |
| `?` mal ubicado dentro de un parámetro de ruta registrada (p. ej. `{id?x}`) | `RouteException` | — |

## 12. Rutas registradas (adelanto)

`RouterLexer` también analiza los **patrones** que el desarrollador registrará. Con `RouteGenerator`, un
parámetro opcional genera una variante de ruta por cada omisión:

```php
$generator = new RouteGenerator('/users/{id}/{slug?}', Methods::GET);
$generator->get_routes();
// ["/users/{id}", "/users/{id}/{slug}"]
```

| Patrón | Variantes generadas |
| --- | --- |
| `/users/{id}` | `/users/{id}` |
| `/a/{x?}/{y?}` | `/a`, `/a/{x}`, `/a/{x}/{y}` |
| `/blog/{slug}/` | `/blog/{slug}` (la barra final se descarta) |
| `/users/{}` | `RouteException`: un parámetro no puede estar vacío |

Cómo se registran estas rutas y cuándo se disparan queda para los capítulos siguientes, porque esa parte aún
no existe.

## 13. Qué falta por documentar

- Registro de rutas y despacho (no implementados).
- `RequestRouteLexer` y la extracción de valores de parámetros (en desarrollo).
- `QueryStringLexer` y `QueryParamComposer` (capítulo propio).
