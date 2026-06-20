# `TelemetryRequest`

**Namespace:** `DLRoute\Core\Telemetry`  
**Tipo:** `class`  
**Disponible desde:** `v1.0.9`

---

## Descripción

`TelemetryRequest` es la clase encargada de recopilar, estructurar y exponer métricas de rendimiento y metadatos específicos del ciclo de vida de una petición HTTP entrante. A diferencia del objeto de telemetría global del entorno (`Telemetry`), esta clase se enfoca exclusivamente en la interacción activa entre la solicitud del cliente y el despachador de rutas.

Esta clase fue reubicada estratégicamente hacia el namespace central `DLRoute\Core\Telemetry` en la versión `v1.0.9` para consolidar el subsistema de diagnóstico del framework de enrutamiento.

## Responsabilidades Principales

* **Perfilado de la Petición:** Registra el estado exacto del método HTTP, la URI solicitada y los componentes críticos del direccionamiento durante el proceso de emparejamiento (*matching*).
* **Diagnóstico de Rendimiento:** Sirve como base para auditar la eficiencia con la que el autómata finito procesa el registro de rutas frente a una solicitud HTTP en tiempo real.
* **Soporte de Depuración:** Proporciona un punto único de acceso para que herramientas de depuración o sistemas de registro externos extraigan el contexto operativo de una solicitud sin interferir con la velocidad de ejecución del enrutador.

---

## Arquitectura y Buenas Prácticas

`TelemetryRequest` implementa contratos estrictos del ecosistema para garantizar que la recolección de telemetría añada una sobrecarga de memoria prácticamente nula (cero dependencias externas y llamadas optimizadas a bajo nivel), respetando los estándares de alto rendimiento de DLRoute.