# `QueryParamValue`

**Namespace:** `DLRoute\Core\Data`  
**Tipo:** `class` (Value Object / DTO)  
**Disponible desde:** `v1.0.9`

---

## Descripción

`QueryParamValue` es un objeto de valor (Value Object) diseñado para encapsular de forma segura y tipada el valor de un parámetro extraído de la cadena de consulta (*query string*). Trabaja en estricta coordinación con `QueryParam` para aislar el contenido crudo de la petición y evitar la manipulación directa de tipos primitivos sueltos dentro del motor de rutas.

Al adoptar un enfoque de diseño orientado a objetos rígido, esta clase asegura que cada valor procesado por el autómata léxico cumpla con las expectativas de integridad estructural antes de ser inyectado en el ciclo de vida de la aplicación.

## Características Principales

* **Encapsulación Rígida:** Protege el valor recuperado de la URI, asegurando que se mantenga inmutable durante la fase de análisis léxico y emparejamiento.
* **Compatibilidad de Tipos:** Proporciona los mecanismos necesarios para evaluar y retornar el valor en su estado correspondiente (por ejemplo, manejando cadenas de texto o estados nulos de manera uniforme).
* **Abstracción de Datos:** Evita que el resto del sistema de enrutamiento dependa de validaciones manuales repetitivas sobre variables globales o arrays asociativos nativos sucios.

---

## Integración en el Flujo

Durante la ejecución del autómata, `QueryParamComposer` utiliza `QueryParamValue` para empaquetar de forma exacta la información que corresponde a un token de valor (`QUERY_VALUE`), construyendo un mapa de parámetros limpio, predecible y altamente eficiente en consumo de CPU.