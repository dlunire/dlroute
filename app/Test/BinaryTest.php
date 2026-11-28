<?php

declare(strict_types=1);

namespace DLRoute\Test;

use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\Routing\LexerSymbolInterface;

final class BinaryTest implements LexerSymbolInterface {

    private array $test_binary = [];

    private int $mask = 0;

    private array $buffer = [];

    private function __construct() {
        $capacity = 8;
        $this->mask = $capacity - 1;

        $table = array_fill(0, $capacity, [
            'offset' => 0,
            'length' => 0,
        ]);
        $this->test_binary = $table;
    }

    public static function from(): static {
        return new static();
    }

    public function execute_test(): void {
        echo "Probando si existe colisión...\n";
        $tab = "\x20\x20\x20\x20";

        /** @var non-empty-string $a */
        $a = "abcd";

        /** @var non-empty-string $b */
        $b = "abce";

        $int_A = $this->calculate(domain: $a);
        $mod_A = $int_A % 321;
        $pos_A = 191 + $mod_A;

        $buffer_A = join(separator: " ", array: $this->buffer);

        $int_B = $this->calculate(domain: $b);
        $mod_B = $int_B % 321;
        $pos_B = 191 + $mod_B;

        $buffer_B = join(separator: " ", array: $this->buffer);

        if ($int_A === $int_B) {
            echo "\nRESULTADO\nExiste una colición:\n\x20\x20\x20";
            echo "{$tab}PA_<A>: {$pos_A}\n{$tab}N({$a})<Mod A: {$mod_A}> → {$int_A}\n{$tab}PA_<B>: {$pos_B}\n{$tab}({$b})<Mod B: {$mod_B}> → {$int_B}\n";
            echo "\nBuffer A: {$buffer_A}\nBuffer B: {$buffer_B}\n";

            return;
        }

        echo "Resultado: no se ha encontrado colisiones.\n";
        echo "{$tab}PA_<A>: {$pos_A}\n{$tab}N({$a})<Mod A: {$mod_A}> → {$int_A}\n{$tab}PA_<B>: {$pos_B}\n{$tab}({$b})<Mod B: {$mod_B}> → {$int_B}\n";
        echo "\nBuffer A: {$buffer_A}\nBuffer B: {$buffer_B}\n";
    }

    private function calculate(string $domain): int {
        return $this->calculate_value($domain, \strlen($domain));
    }
    /**
     * Una función que me sirve para probar si existe o no algún tipo de colisión en el cálculo 
     * numérico de los bytes.
     *
     * @param string $domain Dominio o entrada a ser evaluada para probar las colisiones.
     * @param int $length Longitud del dominio a evluar. El valor ha sido previamente calculado.
     * @return integer
     */
    private function calculate_value(string $domain, int $length): int {
        if ($length > 4) {
            throw new RouteException("Por ahora, no se permite más de 4 bytes como argumento en \$domain", 500);
        }

        /** @var int $sum Suma de valores */
        $sum = 0;

        $this->buffer = [];

        /**
         * Aquí aplicamos el modelo matemático que incluye la sumatoria para evaluar potenciales
         * colisines.
         */
        for ($index = 0; $index < $length; ++$index) {
            $symbol = $domain[$index];

            /** @var int $byte */
            $byte = self::SYMBOL_TO_BYTE[$symbol];

            $value = 256 ** ($length - ($index + 1));
            $sum += $byte * $value;

            $this->buffer[] = "{$symbol}({$byte})";
        }

        return $sum;
    }
}
