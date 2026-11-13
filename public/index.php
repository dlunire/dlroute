<?php

use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * Configuración del benchmark.
 */
$iterations = 10000;
$warmup_iterations = 100;

/**
 * Primer análisis.
 *
 * Mide la construcción inicial del singleton y el procesamiento
 * de la URI por primera vez.
 */
$start_time = hrtime(true);

$request = RequestURILexer::from();

$first_analysis_time = hrtime(true) - $start_time;

/**
 * Calentamiento.
 *
 * Ejecuta accesos adicionales al singleton antes de la medición.
 */
for ($i = 0; $i < $warmup_iterations; ++$i) {
    $request = RequestURILexer::from();
}

/**
 * Benchmark de reutilización.
 */
$total_time = 0;

for ($i = 0; $i < $iterations; ++$i) {
    $start_time = hrtime(true);

    $request = RequestURILexer::from();

    $total_time += hrtime(true) - $start_time;
}

/**
 * Resultados.
 */
printf(
    "Motor: DLRoute (Automaton)\n" .
        "Iteraciones: %d\n" .
        "Calentamiento: %d\n\n" .
        "=== PRIMER ANÁLISIS ===\n" .
        "Tiempo: %.6f ms\n\n" .
        "=== REUTILIZACIÓN DEL SINGLETON ===\n" .
        "Tiempo acumulado: %.6f ms\n" .
        "Tiempo promedio: %.6f ms\n",
    $iterations,
    $warmup_iterations,
    $first_analysis_time / 1_000_000,
    $total_time / 1_000_000,
    ($total_time / $iterations) / 1_000_000
);


print_r($request->get_route_resolution());