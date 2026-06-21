# `TokenType`

**Namespace:** `DLRoute\Enums`  
**Tipo:** `enum`  
**Usado por:** `DLRoute\Route\RouteLexer`

---

## Descripción

Representa el tipo de un token capturado durante el análisis léxico de una URI registrada por el desarrollador.

A diferencia de `QueryStringTokenType` — que clasifica los tokens del querystring de la petición HTTP entrante — `TokenType` clasifica los segmentos estructurales de la URI definida en el código fuente de la aplicación. Es el vocabulario formal con el que el `RouteLexer` descompone una ruta como `/{uuid?}/usuarios/{id}` en unidades atómicas antes de registrarla en el despachador.

El autómata del `RouteLexer` emite exactamente un valor de este enum por cada token detectado durante el escaneo byte a byte de la URI.

---

## Casos

### `SEPARATOR`

Indica que el token capturado es un separador de segmento de ruta — el carácter `/`.

```
/{id}/usuarios
─              ← SEPARATOR
      ─        ← SEPARATOR
```

Es el delimitador estructural de la URI. El autómata lo usa para delimitar el inicio de cada segmento y avanzar el cursor al siguiente bloque.

---

### `LITERAL`

Indica que el token capturado es un segmento estático — texto fijo que debe coincidir exactamente con la URI de la petición entrante.

```
/api/usuarios/{id}
 ───             ← LITERAL: "api"
     ────────    ← LITERAL: "usuarios"
```

Un segmento literal no contiene llaves, no es un parámetro dinámico y no admite variación en el matching. Su presencia en la URI registrada exige una coincidencia exacta byte a byte con el segmento equivalente de la petición.

---

### `PARAM`

Indica que el token capturado es un parámetro dinámico obligatorio — un segmento encerrado entre llaves sin el modificador `?`.

```
/usuarios/{id}/perfil
           ──          ← PARAM: "id"
```

Un parámetro dinámico obligatorio debe estar presente en la URI de la petición. Si el segmento está ausente, la ruta no coincide. El valor capturado en tiempo de despacho queda disponible como propiedad del objeto `$params` inyectado en el controlador.

---

### `OPTIONAL`

Indica que el token capturado es un parámetro dinámico opcional — un segmento encerrado entre llaves con el modificador `?`.

```
/productos/{uuid?}/detalle
            ────            ← OPTIONAL: "uuid"
```

Un parámetro opcional genera el registro simultáneo de dos rutas en el despachador:

- `/productos/detalle` — sin el parámetro
- `/productos/{uuid}/detalle` — con el parámetro

Si el modificador `?` va seguido de cualquier carácter distinto de `}`, el `RouteLexer` emite un `RouteException` con la posición exacta del byte problemático, el fragmento recibido y el formato correcto esperado.

```
// ❌ Sintaxis inválida
/{ciencia?=algo}/usuarios

RouteException: Se esperaba una llave de cierre (}) después del símbolo «?» (posición 9).
En su lugar, se recibió «?=algo}/usuarios».
Los parámetros opcionales deben tener el formato → «{parametro?}»
Ruta definida: «/{ciencia?=algo}/usuarios»
```

---

### `QUERY_SEPARATOR`

Indica que el token capturado es el delimitador de inicio del querystring — el carácter `?` cuando aparece fuera de una definición de parámetro.

```
/api/usuarios?filtro=activo
             ─              ← QUERY_SEPARATOR
```

La presencia de este token le indica al autómata que los bytes subsiguientes pertenecen al querystring y no a la estructura de la ruta. A partir de este punto, el análisis de la URI registrada concluye y el control pasa al subsistema de querystring.

---

### `QUERY_STRING`

Indica que el token capturado es el contenido del querystring — todo lo que sigue al `QUERY_SEPARATOR`.

```
/api/usuarios?filtro=activo&pagina=2
              ─────────────────────── ← QUERY_STRING: "filtro=activo&pagina=2"
```

En el contexto del `RouteLexer`, este token representa el fragmento completo del querystring tal como fue definido en la URI registrada por el desarrollador. Su análisis detallado (nombre → valor, offsets, normalización) es responsabilidad del `QueryStringLexer`, no del `RouteLexer`.

---

### `END`

Indica que el autómata ha alcanzado el final de la cadena de bytes de la URI. No representa ningún carácter de la URI — es una señal de terminación formal del análisis léxico.

```
/api/{id}
         ← END (posición: strlen($uri))
```

Cuando el autómata emite `END`, el `RouteLexer` ha completado la descomposición de la URI en tokens y puede proceder al registro de la ruta en el despachador.

---

## Flujo del autómata

Una URI completa produce tokens en este orden:

```
URI: /api/{uuid?}/usuarios/{id}

[0] SEPARATOR  → "/"
[1] LITERAL    → "api"
[2] SEPARATOR  → "/"
[3] OPTIONAL   → "uuid"
[4] SEPARATOR  → "/"
[5] LITERAL    → "usuarios"
[6] SEPARATOR  → "/"
[7] PARAM      → "id"
[8] END
```

Una URI con querystring definido:

```
URI: /api/{id}?debug=true

[0] SEPARATOR      → "/"
[1] LITERAL        → "api"
[2] SEPARATOR      → "/"
[3] PARAM          → "id"
[4] QUERY_SEPARATOR → "?"
[5] QUERY_STRING   → "debug=true"
[6] END
```

---

## Relación con `QueryStringTokenType`

`TokenType` y `QueryStringTokenType` son los vocabularios formales de dos autómatas distintos dentro de DLRoute:

| Enum | Autómata | Entrada | Propósito |
|---|---|---|---|
| `TokenType` | `RouteLexer` | URI definida por el desarrollador | Validar y descomponer la estructura de la ruta |
| `QueryStringTokenType` | `QueryStringLexer` | Querystring de la petición HTTP | Analizar los parámetros enviados por el cliente |

Ambos operan byte a byte en una sola pasada. Ninguno usa `preg_match()`, `explode()` ni funciones nativas de parsing de PHP.

---

## Uso interno

`TokenType` no está diseñado para ser consumido directamente por el desarrollador que usa DLRoute. Es parte del motor interno del `RouteLexer` y su ciclo de vida termina cuando el análisis léxico de la URI concluye y la ruta queda registrada en el despachador.

## Véase también

- [`QueryStringTokenType`](QueryStringTokenType.md) — enum equivalente para el análisis léxico del querystring
- [`QueryStringLexer`](QueryStringLexer.md) — autómata de análisis del querystring
- [`QueryParamComposer`](QueryParamComposer.md) — composición de pares nombre → valor desde tokens