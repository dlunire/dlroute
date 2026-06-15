# `QueryStringLexer`

**Namespace:** `DLRoute\Core\Routing\Automaton`  
**Tipo:** `abstract class`  
**Implementa:** `RouteLexerInterface`  
**Disponible desde:** `v1.0.9`

---

## Descripción

Analizador léxico base del querystring de la petición HTTP.

Define el autómata completo de análisis — carga, escaneo y emisión de tokens — y expone `get_tokens()` a las clases concretas que lo extiendan. No puede instanciarse directamente porque el consumo de los tokens depende del contexto de cada subclase.

El análisis se realiza en **una sola pasada** sobre la cadena de bytes, sin `parse_str()`, sin `explode()` y sin expresiones regulares.

---

## Arquitectura del autómata

El autómata opera en dos niveles:

```
Nivel 1 — scanner()
  Itera sobre la cadena completa byte a byte.
  Cada iteración delega en el subautómata.

Nivel 2 — request_emit_token()
  Procesa un bloque completo (segmento entre «&» o entre inicio y «&»).
  Cambia el estado interno según los bytes encontrados.
  Emite tokens como instancias de QueryParam.
```

### Estados internos

El estado del subautómata está representado por `$tokentype`:

| Estado        | Significado                                    |
| ------------- | ---------------------------------------------- |
| `QUERY_NAME`  | El cursor está leyendo el nombre del parámetro |
| `QUERY_VALUE` | El cursor está leyendo el valor del parámetro  |

El estado siempre inicia en `QUERY_NAME` porque el primer byte de cualquier bloque pertenece al nombre del parámetro.

---

## Constantes heredadas de `RouteLexerInterface`

| Constante         | Valor        | Descripción                                    |
| ----------------- | ------------ | ---------------------------------------------- |
| `QUERY_SEPARATOR` | `\x26` (`&`) | Separador entre parámetros del querystring     |
| `QUERY_ASSIGN`    | `\x3d` (`=`) | Separador entre nombre y valor de un parámetro |

---

## Propiedades

| Propiedad       | Tipo                   | Visibilidad        | Descripción                                                   |
| --------------- | ---------------------- | ------------------ | ------------------------------------------------------------- |
| `$query_string` | `?string`              | `private readonly` | Cadena del querystring decodificada, o `null` si está ausente |
| `$offset`       | `int`                  | `private`          | Posición actual del cursor del autómata                       |
| `$size`         | `int`                  | `private readonly` | Longitud en bytes del querystring. Vale `0` si está vacío     |
| `$tokentype`    | `QueryStringTokenType` | `private`          | Estado actual del subautómata. Siempre inicia en `QUERY_NAME` |
| `$tokens`       | `QueryParam[]`         | `protected`        | Tokens capturados durante el análisis                         |
| `$token_count`  | `int`                  | `protected`        | Cantidad de tokens capturados. Mantenido en O(1)              |

---

## Métodos

### `__construct()`

Inicializa el analizador en dos fases:

1. `load_query_string()` — carga y decodifica `$_SERVER['QUERY_STRING']`, calcula `$size`.
2. `scanner()` — ejecuta el autómata y popula `$tokens`.

Si el querystring está vacío o ausente, `scanner()` retorna inmediatamente y `$tokens` queda vacío.

---

### `get_tokens(): QueryParam[]` — `protected`

Devuelve los tokens capturados durante el análisis léxico. Cada elemento es una instancia inmutable de `QueryParam`.

---

## Comportamiento ante cadenas malformadas

`QueryStringLexer` es permisivo por diseño — el querystring viene del cliente, no del desarrollador. No lanza excepciones. En su lugar:

| Entrada         | Comportamiento                                                                                           |
| --------------- | -------------------------------------------------------------------------------------------------------- |
| `=valor`        | Valor huérfano — descartado (`$length < 1`)                                                              |
| `&&&&`          | Separadores vacíos — descartados (`$length < 1`)                                                         |
| `campo=`        | Valor vacío — emitido como `QUERY_VALUE` con lexema vacío, normalizado a `null` por `QueryParamComposer` |
| `campo 1=valor` | Aceptado — espacios en nombres son válidos                                                               |
| `campo=a=b=c`   | Aceptado — todo después del primer `=` es valor: `"a=b=c"`                                               |

---

## Ejemplo de tokens producidos

Para el querystring `campo 1= algún valor&&&&otro-campo&field=valor&&&`:

```
[0] QUERY_NAME  → "campo 1"       offset:  0  length:  7
[1] QUERY_VALUE → " algún valor"  offset:  8  length: 13
[2] QUERY_NAME  → "otro-campo"    offset: 25  length: 10
[3] QUERY_NAME  → "field"         offset: 36  length:  5
[4] QUERY_VALUE → "valor"         offset: 42  length:  5
```

---

## Extensión

`QueryStringLexer` está diseñado para ser extendido por `QueryParamComposer`, que consume los tokens y los transforma en pares `nombre → valor` listos para el desarrollador.

```php
final class QueryParamComposer extends QueryStringLexer {
    // ...
}
```