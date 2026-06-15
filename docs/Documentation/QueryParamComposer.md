# `QueryParamComposer`

**Namespace:** `DLRoute\Core\Routing\Automaton\QueryParams`  
**Tipo:** `final class`  
**Extiende:** `QueryStringLexer`  
**Disponible desde:** `v1.0.9`

---

## Descripción

`QueryParamComposer` es la capa consumidora del autómata de análisis de consultas. Su responsabilidad principal es tomar los tokens extraídos a bajo nivel por el `QueryStringLexer` (`QUERY_NAME`, `QUERY_VALUE`) y orquestarlos en estructuras de datos de clave-valor limpias y unificadas.

Esta clase abstrae al motor de rutas del uso de funciones nativas lentas o inseguras como `parse_str()`, iterando con máxima eficiencia y controlando exactamente cómo cada parámetro es emparejado.

## Comportamiento Principal

* **Emparejamiento de Tokens:** Une cada nombre de parámetro con su valor asignado emitido por el autómata.
* **Manejo de Valores Nulos:** Si el autómata emite un `QUERY_NAME` pero la URI no contenía un operador de asignación (`=`) o no poseía un valor (ej. `?filtro_activo`), el composer asume la omisión explícita y asigna automáticamente `null` a la clave.

---

## Uso Interno

El sistema `DLRoute` instancia automáticamente el compositor antes de inyectar las variables capturadas hacia la interfaz de `RouterData` y `DLRequest`, garantizando integridad referencial desde la URI hasta la ejecución de tu controlador.