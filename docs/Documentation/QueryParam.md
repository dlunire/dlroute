# `QueryParam`

**Namespace:** `DLRoute\Core\Data`  
**Tipo:** `class` (DTO)  
**Disponible desde:** `v1.0.9`

---

## Descripción

`QueryParam` actúa como un **Data Transfer Object (DTO)** inmutable. Representa un bloque atómico de información derivado de un token generado durante el análisis léxico de las rutas o el querystring.

## Arquitectura

Al construir DLRoute con tolerancia cero a errores de estado y escalabilidad, `QueryParam` reemplaza los simples arrays asociativos por objetos formales estrictamente tipados. 

* **Inmutabilidad:** Garantiza que, una vez que un parámetro es leído de la petición del cliente y encapsulado, no puede sufrir mutaciones inesperadas en partes profundas del ciclo de vida de DLRoute.
* **Seguridad de Tipos:** Su interacción a menudo va de la mano de `QueryParamValue` para validar la naturaleza del contenido de forma predecible antes de llegar al controlador del desarrollador.

---

Este DTO es fundamental para sostener el enrutador en un paradigma orientado a objetos rígido, dejando atrás las prácticas inseguras de las matrices genéricas.