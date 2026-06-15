# `QueryStringTokenType`

**Namespace:** `DLRoute\Core\Routing\Automaton`  
**Tipo:** `enum`  
**Disponible desde:** `v1.0.9`

---

## Descripción

Representa el tipo de un token capturado durante el análisis léxico del querystring de la petición HTTP.

Un parámetro de querystring tiene la forma `nombre=valor`, por lo que el autómata emite exactamente dos tipos de tokens por parámetro: primero `QUERY_NAME` y luego `QUERY_VALUE`. Cuando un parámetro no tiene valor asignado (e.g. `?activo`), se emite `QUERY_NAME` y el valor queda con `null` implícito.

---

## Casos

### `QUERY_NAME`

Indica que el token capturado corresponde al **nombre** del parámetro.

```
?nombre=David&rol=admin
  ──────                 ← QUERY_NAME: "nombre"
                ───      ← QUERY_NAME: "rol"
```

### `QUERY_VALUE`

Indica que el token capturado corresponde al **valor** del parámetro.

Todo lo que viene después del primer `=` hasta el siguiente `&` o el fin de la cadena es considerado valor — incluyendo `=` adicionales.

```
?nombre=David&checksum=abc==
        ─────               ← QUERY_VALUE: "David"
                       ───── ← QUERY_VALUE: "abc=="
```

Cuando el parámetro no tiene valor asignado, este token no se emite y el `QueryParamComposer` asigna `null` en su lugar.

---

## Reglas del autómata

| Situación | Tokens emitidos |
|---|---|
| `?campo=valor` | `QUERY_NAME("campo")` + `QUERY_VALUE("valor")` |
| `?campo` | `QUERY_NAME("campo")` |
| `?campo=` | `QUERY_NAME("campo")` — valor normalizado a `null` |
| `?=valor` | Descartado — valor huérfano sin nombre |
| `&&&&` | Descartado — separadores consecutivos vacíos |

---

## Uso interno

`QueryStringTokenType` es utilizado por:

- `QueryStringLexer` — para clasificar cada token durante el análisis léxico.
- `QueryParamComposer` — para componer pares `nombre → valor` a partir de los tokens.

No está diseñado para ser instanciado ni extendido por el desarrollador que usa `DLRoute`.