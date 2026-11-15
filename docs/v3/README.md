# DLRoute v3 — Documentación

> **Estado: en desarrollo** (rama `dev-automaton`).
> La v2.x sigue siendo válida y soportada. Su tutorial está en [`docs/tutorial/`](../tutorial/README.md).

DLRoute v3 procesa cada petición como un pipeline de autómatas finitos: lee la URI byte a byte, la convierte
en tokens y trabaja sobre esos tokens, sin expresiones regulares.

## Qué existe hoy y qué no

| Componente | Estado |
| --- | --- |
| Análisis léxico de la URI de la petición (HTTP y CLI) | Implementado |
| Resolución de directorio lógico y ruta (`dir` / `route`) | Implementado |
| Autómata de la cadena de consulta | Implementado |
| Análisis léxico de rutas registradas y generación de variantes opcionales | Implementado |
| **Registro de rutas** | **No implementado** |
| **Despacho (disparo) de rutas registradas** | **No implementado** |
| Extracción de valores de parámetros de la petición (`RequestRouteLexer`) | En desarrollo |

## Capítulos

| # | Tema | Archivo |
|---|------|---------|
| 1 | Análisis léxico de la ruta de la petición (HTTP y CLI) | [01-analisis-lexico-peticion.md](01-analisis-lexico-peticion.md) |

## Convención

Igual que en v2: `snake_case` para métodos, variables y claves de array; `PascalCase` para clases.
Cada capítulo indica qué se verificó ejecutando el código y qué queda pendiente.
