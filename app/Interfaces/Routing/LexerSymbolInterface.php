<?php

/**
 * DLUnire
 * Copyright (C) 2026 David E Luna M
 *
 * Operando bajo el establecimiento de comercio "DLUnire",
 * NIT 700551569-1, matrícula mercantil Nº 10007069
 * (matrícula mercantil personal Nº 10007068).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace DLRoute\Interfaces\Routing;

/**
 * Contrato para los símbolos utilizados por los analizadores léxicos.
 *
 * Define el conjunto de símbolos y tablas de conversión utilizados por los analizadores léxicos durante
 * el procesamiento de rutas, parámetros de consulta y secuencias binarias.
 *
 * Las constantes individuales representan símbolos con significado específico dentro del lenguaje léxico
 * utilizado por DLRoute, mientras que las tablas de conversión permiten transformar directamente entre
 * valores de byte y sus correspondientes símbolos.
 *
 * La representación explícita mediante secuencias hexadecimal permite trabajar directamente con el valor
 * binario de cada carácter, evitando conversiones dinámicas innecesarias durante el procesamiento del
 * autómata.
 *
 * Las tablas {@see BYTE_TO_SYMBOL} y {@see SYMBOL_TO_BYTE} contienen la correspondencia completa del dominio
 * de un byte, comprendido entre `0x00` y `0xFF`. Esta precomputación intercambia consumo de memoria por
 * menor costo computacional durante las operaciones de conversión.
 *
 * @package DLRoute\Interfaces\Routing
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <info@dlunire.dev>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0-or-later
 */
interface LexerSymbolInterface {

    /**
     * Llave de apertura `{`.
     * Marca el inicio de un segmento paramétrico dentro de un patrón de ruta.
     *
     * @var non-empty-string
     */
    public const BRACKET_OPEN = "\x7b";

    /**
     * Llave de cierre `}` — marca el fin de un segmento paramétrico en la ruta.
     *
     * @var non-empty-string
     */
    public const BRACKET_CLOSE = "\x7d";

    /**
     * Signo de interrogación `?` — indica que el parámetro precedente es opcional.
     *
     * @var non-empty-string 
     */
    public const OPTIONAL_PARAMETER = "\x3f";

    /**
     * Espacio en blanco
     * 
     * @var non-empty-string
     */
    public const WHITE_SPACE = "\x20";

    /**
     * Barra diagonal derecha `/`
     * 
     * @var non-empty-string
     */
    public const SLASH = "\x2f";

    /**
     * Separador entre parámetros del querystring.
     *
     * Corresponde al carácter «&» (0x26 en ASCII), que delimita cada par
     * «nombre=valor» en la cadena de parámetros de la petición HTTP.
     *
     * Ejemplo: en «nombre=David&rol=admin», el byte «&» indica al autómata
     * que el parámetro actual ha terminado y comienza uno nuevo.
     *
     * @var string
     */
    public const QUERY_SEPARATOR = "\x26";

    /**
     * Separador entre el nombre y el valor de un parámetro del querystring.
     *
     * Corresponde al carácter «=» (0x3D en ASCII), que delimita el nombre
     * del parámetro de su valor en cada par «nombre=valor».
     *
     * Ejemplo: en «nombre=David&rol=admin», el byte «=» indica al autómata
     * que el nombre del parámetro ha terminado y comienza su valor.
     *
     * @var string
     */
    public const QUERY_ASSIGN = "\x3d";

    /**
     * Carácter de guion bajo (underscore).
     *
     * Representa el valor hexadecimal "\x5f". Se utiliza durante la fase de
     * normalización o análisis léxico para sustituir los espacios en blanco
     * presentes en los nombres de las claves (keys). Esto garantiza que el
     * analizador semántico reciba identificadores válidos, continuos y
     * seguros para su posterior procesamiento o asignación.
     *
     * @var string
     */
    public const UNDERSCORE = "\x5f";

    /**
     * Representa el carácter de arroba ('@') expresado mediante su valor hexadecimal en Notación ASCII (0x40).
     * 
     * Se utiliza como un literal inmutable para operaciones de parseo, tokenización o concatenación,
     * garantizando el uso explícito del byte correspondiente sin depender de representaciones de cadena variables.
     *
     * @var string
     */
    public const AT_SIGN = "\x40";

    /**
     * Carácter de tabulación horizontal (Horizontal Tab, HT).
     *
     * Representa el byte `0x09` de la tabla ASCII, utilizado para separar o alinear elementos mediante
     * espacios de tabulación.
     *
     * @var string
     */
    public const TAB = "\x09";

    /**
     * Tabla de conversión directa de valores de byte a símbolos.
     *
     * Cada índice representa un valor de byte comprendido entre `0x00` y `0xFF`, y su valor asociado
     * corresponde al símbolo representado por dicho byte.
     *
     * La tabla permite resolver la conversión mediante acceso directo, evitando operaciones dinámicas de
     * transformación durante el procesamiento léxico.
     *
     * El dominio completo de 256 valores se encuentra predefinido deliberadamente para favorecer el costo computacional
     * sobre el consumo de memoria.
     *
     * @var array<integer,string>
     */
    public const BYTE_TO_SYMBOL = [
        0x00 => "\x00",
        0x01 => "\x01",
        0x02 => "\x02",
        0x03 => "\x03",
        0x04 => "\x04",
        0x05 => "\x05",
        0x06 => "\x06",
        0x07 => "\x07",
        0x08 => "\x08",
        0x09 => "\x09",
        0x0a => "\x0a",
        0x0b => "\x0b",
        0x0c => "\x0c",
        0x0d => "\x0d",
        0x0e => "\x0e",
        0x0f => "\x0f",
        0x10 => "\x10",
        0x11 => "\x11",
        0x12 => "\x12",
        0x13 => "\x13",
        0x14 => "\x14",
        0x15 => "\x15",
        0x16 => "\x16",
        0x17 => "\x17",
        0x18 => "\x18",
        0x19 => "\x19",
        0x1a => "\x1a",
        0x1b => "\x1b",
        0x1c => "\x1c",
        0x1d => "\x1d",
        0x1e => "\x1e",
        0x1f => "\x1f",
        0x20 => "\x20",
        0x21 => "\x21",
        0x22 => "\x22",
        0x23 => "\x23",
        0x24 => "\x24",
        0x25 => "\x25",
        0x26 => "\x26",
        0x27 => "\x27",
        0x28 => "\x28",
        0x29 => "\x29",
        0x2a => "\x2a",
        0x2b => "\x2b",
        0x2c => "\x2c",
        0x2d => "\x2d",
        0x2e => "\x2e",
        0x2f => "\x2f",
        0x30 => "\x30",
        0x31 => "\x31",
        0x32 => "\x32",
        0x33 => "\x33",
        0x34 => "\x34",
        0x35 => "\x35",
        0x36 => "\x36",
        0x37 => "\x37",
        0x38 => "\x38",
        0x39 => "\x39",
        0x3a => "\x3a",
        0x3b => "\x3b",
        0x3c => "\x3c",
        0x3d => "\x3d",
        0x3e => "\x3e",
        0x3f => "\x3f",
        0x40 => "\x40",
        0x41 => "\x41",
        0x42 => "\x42",
        0x43 => "\x43",
        0x44 => "\x44",
        0x45 => "\x45",
        0x46 => "\x46",
        0x47 => "\x47",
        0x48 => "\x48",
        0x49 => "\x49",
        0x4a => "\x4a",
        0x4b => "\x4b",
        0x4c => "\x4c",
        0x4d => "\x4d",
        0x4e => "\x4e",
        0x4f => "\x4f",
        0x50 => "\x50",
        0x51 => "\x51",
        0x52 => "\x52",
        0x53 => "\x53",
        0x54 => "\x54",
        0x55 => "\x55",
        0x56 => "\x56",
        0x57 => "\x57",
        0x58 => "\x58",
        0x59 => "\x59",
        0x5a => "\x5a",
        0x5b => "\x5b",
        0x5c => "\x5c",
        0x5d => "\x5d",
        0x5e => "\x5e",
        0x5f => "\x5f",
        0x60 => "\x60",
        0x61 => "\x61",
        0x62 => "\x62",
        0x63 => "\x63",
        0x64 => "\x64",
        0x65 => "\x65",
        0x66 => "\x66",
        0x67 => "\x67",
        0x68 => "\x68",
        0x69 => "\x69",
        0x6a => "\x6a",
        0x6b => "\x6b",
        0x6c => "\x6c",
        0x6d => "\x6d",
        0x6e => "\x6e",
        0x6f => "\x6f",
        0x70 => "\x70",
        0x71 => "\x71",
        0x72 => "\x72",
        0x73 => "\x73",
        0x74 => "\x74",
        0x75 => "\x75",
        0x76 => "\x76",
        0x77 => "\x77",
        0x78 => "\x78",
        0x79 => "\x79",
        0x7a => "\x7a",
        0x7b => "\x7b",
        0x7c => "\x7c",
        0x7d => "\x7d",
        0x7e => "\x7e",
        0x7f => "\x7f",
        0x80 => "\x80",
        0x81 => "\x81",
        0x82 => "\x82",
        0x83 => "\x83",
        0x84 => "\x84",
        0x85 => "\x85",
        0x86 => "\x86",
        0x87 => "\x87",
        0x88 => "\x88",
        0x89 => "\x89",
        0x8a => "\x8a",
        0x8b => "\x8b",
        0x8c => "\x8c",
        0x8d => "\x8d",
        0x8e => "\x8e",
        0x8f => "\x8f",
        0x90 => "\x90",
        0x91 => "\x91",
        0x92 => "\x92",
        0x93 => "\x93",
        0x94 => "\x94",
        0x95 => "\x95",
        0x96 => "\x96",
        0x97 => "\x97",
        0x98 => "\x98",
        0x99 => "\x99",
        0x9a => "\x9a",
        0x9b => "\x9b",
        0x9c => "\x9c",
        0x9d => "\x9d",
        0x9e => "\x9e",
        0x9f => "\x9f",
        0xa0 => "\xa0",
        0xa1 => "\xa1",
        0xa2 => "\xa2",
        0xa3 => "\xa3",
        0xa4 => "\xa4",
        0xa5 => "\xa5",
        0xa6 => "\xa6",
        0xa7 => "\xa7",
        0xa8 => "\xa8",
        0xa9 => "\xa9",
        0xaa => "\xaa",
        0xab => "\xab",
        0xac => "\xac",
        0xad => "\xad",
        0xae => "\xae",
        0xaf => "\xaf",
        0xb0 => "\xb0",
        0xb1 => "\xb1",
        0xb2 => "\xb2",
        0xb3 => "\xb3",
        0xb4 => "\xb4",
        0xb5 => "\xb5",
        0xb6 => "\xb6",
        0xb7 => "\xb7",
        0xb8 => "\xb8",
        0xb9 => "\xb9",
        0xba => "\xba",
        0xbb => "\xbb",
        0xbc => "\xbc",
        0xbd => "\xbd",
        0xbe => "\xbe",
        0xbf => "\xbf",
        0xc0 => "\xc0",
        0xc1 => "\xc1",
        0xc2 => "\xc2",
        0xc3 => "\xc3",
        0xc4 => "\xc4",
        0xc5 => "\xc5",
        0xc6 => "\xc6",
        0xc7 => "\xc7",
        0xc8 => "\xc8",
        0xc9 => "\xc9",
        0xca => "\xca",
        0xcb => "\xcb",
        0xcc => "\xcc",
        0xcd => "\xcd",
        0xce => "\xce",
        0xcf => "\xcf",
        0xd0 => "\xd0",
        0xd1 => "\xd1",
        0xd2 => "\xd2",
        0xd3 => "\xd3",
        0xd4 => "\xd4",
        0xd5 => "\xd5",
        0xd6 => "\xd6",
        0xd7 => "\xd7",
        0xd8 => "\xd8",
        0xd9 => "\xd9",
        0xda => "\xda",
        0xdb => "\xdb",
        0xdc => "\xdc",
        0xdd => "\xdd",
        0xde => "\xde",
        0xdf => "\xdf",
        0xe0 => "\xe0",
        0xe1 => "\xe1",
        0xe2 => "\xe2",
        0xe3 => "\xe3",
        0xe4 => "\xe4",
        0xe5 => "\xe5",
        0xe6 => "\xe6",
        0xe7 => "\xe7",
        0xe8 => "\xe8",
        0xe9 => "\xe9",
        0xea => "\xea",
        0xeb => "\xeb",
        0xec => "\xec",
        0xed => "\xed",
        0xee => "\xee",
        0xef => "\xef",
        0xf0 => "\xf0",
        0xf1 => "\xf1",
        0xf2 => "\xf2",
        0xf3 => "\xf3",
        0xf4 => "\xf4",
        0xf5 => "\xf5",
        0xf6 => "\xf6",
        0xf7 => "\xf7",
        0xf8 => "\xf8",
        0xf9 => "\xf9",
        0xfa => "\xfa",
        0xfb => "\xfb",
        0xfc => "\xfc",
        0xfd => "\xfd",
        0xfe => "\xfe",
        0xff => "\xff",
    ];

    /**
     * Tabla de conversión directa de símbolos a valores de byte.
     *
     * Cada clave representa un símbolo de un byte y su valor asociado corresponde al valor entero comprendido
     * entre `0x00` y `0xFF`.
     *
     * La tabla permite obtener directamente el valor numérico de un símbolo sin recurrir a operaciones dinámicas
     * de conversión durante el procesamiento léxico.
     *
     * El dominio completo de 256 símbolos se encuentra predefinido deliberadamente para favorecer el costo
     * computacional sobre el consumo de memoria.
     *
     * @var array<string,integer>
     */
    public const SYMBOL_TO_BYTE = [
        "\x00" => 0x00,
        "\x01" => 0x01,
        "\x02" => 0x02,
        "\x03" => 0x03,
        "\x04" => 0x04,
        "\x05" => 0x05,
        "\x06" => 0x06,
        "\x07" => 0x07,
        "\x08" => 0x08,
        "\x09" => 0x09,
        "\x0a" => 0x0a,
        "\x0b" => 0x0b,
        "\x0c" => 0x0c,
        "\x0d" => 0x0d,
        "\x0e" => 0x0e,
        "\x0f" => 0x0f,
        "\x10" => 0x10,
        "\x11" => 0x11,
        "\x12" => 0x12,
        "\x13" => 0x13,
        "\x14" => 0x14,
        "\x15" => 0x15,
        "\x16" => 0x16,
        "\x17" => 0x17,
        "\x18" => 0x18,
        "\x19" => 0x19,
        "\x1a" => 0x1a,
        "\x1b" => 0x1b,
        "\x1c" => 0x1c,
        "\x1d" => 0x1d,
        "\x1e" => 0x1e,
        "\x1f" => 0x1f,
        "\x20" => 0x20,
        "\x21" => 0x21,
        "\x22" => 0x22,
        "\x23" => 0x23,
        "\x24" => 0x24,
        "\x25" => 0x25,
        "\x26" => 0x26,
        "\x27" => 0x27,
        "\x28" => 0x28,
        "\x29" => 0x29,
        "\x2a" => 0x2a,
        "\x2b" => 0x2b,
        "\x2c" => 0x2c,
        "\x2d" => 0x2d,
        "\x2e" => 0x2e,
        "\x2f" => 0x2f,
        "\x30" => 0x30,
        "\x31" => 0x31,
        "\x32" => 0x32,
        "\x33" => 0x33,
        "\x34" => 0x34,
        "\x35" => 0x35,
        "\x36" => 0x36,
        "\x37" => 0x37,
        "\x38" => 0x38,
        "\x39" => 0x39,
        "\x3a" => 0x3a,
        "\x3b" => 0x3b,
        "\x3c" => 0x3c,
        "\x3d" => 0x3d,
        "\x3e" => 0x3e,
        "\x3f" => 0x3f,
        "\x40" => 0x40,
        "\x41" => 0x41,
        "\x42" => 0x42,
        "\x43" => 0x43,
        "\x44" => 0x44,
        "\x45" => 0x45,
        "\x46" => 0x46,
        "\x47" => 0x47,
        "\x48" => 0x48,
        "\x49" => 0x49,
        "\x4a" => 0x4a,
        "\x4b" => 0x4b,
        "\x4c" => 0x4c,
        "\x4d" => 0x4d,
        "\x4e" => 0x4e,
        "\x4f" => 0x4f,
        "\x50" => 0x50,
        "\x51" => 0x51,
        "\x52" => 0x52,
        "\x53" => 0x53,
        "\x54" => 0x54,
        "\x55" => 0x55,
        "\x56" => 0x56,
        "\x57" => 0x57,
        "\x58" => 0x58,
        "\x59" => 0x59,
        "\x5a" => 0x5a,
        "\x5b" => 0x5b,
        "\x5c" => 0x5c,
        "\x5d" => 0x5d,
        "\x5e" => 0x5e,
        "\x5f" => 0x5f,
        "\x60" => 0x60,
        "\x61" => 0x61,
        "\x62" => 0x62,
        "\x63" => 0x63,
        "\x64" => 0x64,
        "\x65" => 0x65,
        "\x66" => 0x66,
        "\x67" => 0x67,
        "\x68" => 0x68,
        "\x69" => 0x69,
        "\x6a" => 0x6a,
        "\x6b" => 0x6b,
        "\x6c" => 0x6c,
        "\x6d" => 0x6d,
        "\x6e" => 0x6e,
        "\x6f" => 0x6f,
        "\x70" => 0x70,
        "\x71" => 0x71,
        "\x72" => 0x72,
        "\x73" => 0x73,
        "\x74" => 0x74,
        "\x75" => 0x75,
        "\x76" => 0x76,
        "\x77" => 0x77,
        "\x78" => 0x78,
        "\x79" => 0x79,
        "\x7a" => 0x7a,
        "\x7b" => 0x7b,
        "\x7c" => 0x7c,
        "\x7d" => 0x7d,
        "\x7e" => 0x7e,
        "\x7f" => 0x7f,
        "\x80" => 0x80,
        "\x81" => 0x81,
        "\x82" => 0x82,
        "\x83" => 0x83,
        "\x84" => 0x84,
        "\x85" => 0x85,
        "\x86" => 0x86,
        "\x87" => 0x87,
        "\x88" => 0x88,
        "\x89" => 0x89,
        "\x8a" => 0x8a,
        "\x8b" => 0x8b,
        "\x8c" => 0x8c,
        "\x8d" => 0x8d,
        "\x8e" => 0x8e,
        "\x8f" => 0x8f,
        "\x90" => 0x90,
        "\x91" => 0x91,
        "\x92" => 0x92,
        "\x93" => 0x93,
        "\x94" => 0x94,
        "\x95" => 0x95,
        "\x96" => 0x96,
        "\x97" => 0x97,
        "\x98" => 0x98,
        "\x99" => 0x99,
        "\x9a" => 0x9a,
        "\x9b" => 0x9b,
        "\x9c" => 0x9c,
        "\x9d" => 0x9d,
        "\x9e" => 0x9e,
        "\x9f" => 0x9f,
        "\xa0" => 0xa0,
        "\xa1" => 0xa1,
        "\xa2" => 0xa2,
        "\xa3" => 0xa3,
        "\xa4" => 0xa4,
        "\xa5" => 0xa5,
        "\xa6" => 0xa6,
        "\xa7" => 0xa7,
        "\xa8" => 0xa8,
        "\xa9" => 0xa9,
        "\xaa" => 0xaa,
        "\xab" => 0xab,
        "\xac" => 0xac,
        "\xad" => 0xad,
        "\xae" => 0xae,
        "\xaf" => 0xaf,
        "\xb0" => 0xb0,
        "\xb1" => 0xb1,
        "\xb2" => 0xb2,
        "\xb3" => 0xb3,
        "\xb4" => 0xb4,
        "\xb5" => 0xb5,
        "\xb6" => 0xb6,
        "\xb7" => 0xb7,
        "\xb8" => 0xb8,
        "\xb9" => 0xb9,
        "\xba" => 0xba,
        "\xbb" => 0xbb,
        "\xbc" => 0xbc,
        "\xbd" => 0xbd,
        "\xbe" => 0xbe,
        "\xbf" => 0xbf,
        "\xc0" => 0xc0,
        "\xc1" => 0xc1,
        "\xc2" => 0xc2,
        "\xc3" => 0xc3,
        "\xc4" => 0xc4,
        "\xc5" => 0xc5,
        "\xc6" => 0xc6,
        "\xc7" => 0xc7,
        "\xc8" => 0xc8,
        "\xc9" => 0xc9,
        "\xca" => 0xca,
        "\xcb" => 0xcb,
        "\xcc" => 0xcc,
        "\xcd" => 0xcd,
        "\xce" => 0xce,
        "\xcf" => 0xcf,
        "\xd0" => 0xd0,
        "\xd1" => 0xd1,
        "\xd2" => 0xd2,
        "\xd3" => 0xd3,
        "\xd4" => 0xd4,
        "\xd5" => 0xd5,
        "\xd6" => 0xd6,
        "\xd7" => 0xd7,
        "\xd8" => 0xd8,
        "\xd9" => 0xd9,
        "\xda" => 0xda,
        "\xdb" => 0xdb,
        "\xdc" => 0xdc,
        "\xdd" => 0xdd,
        "\xde" => 0xde,
        "\xdf" => 0xdf,
        "\xe0" => 0xe0,
        "\xe1" => 0xe1,
        "\xe2" => 0xe2,
        "\xe3" => 0xe3,
        "\xe4" => 0xe4,
        "\xe5" => 0xe5,
        "\xe6" => 0xe6,
        "\xe7" => 0xe7,
        "\xe8" => 0xe8,
        "\xe9" => 0xe9,
        "\xea" => 0xea,
        "\xeb" => 0xeb,
        "\xec" => 0xec,
        "\xed" => 0xed,
        "\xee" => 0xee,
        "\xef" => 0xef,
        "\xf0" => 0xf0,
        "\xf1" => 0xf1,
        "\xf2" => 0xf2,
        "\xf3" => 0xf3,
        "\xf4" => 0xf4,
        "\xf5" => 0xf5,
        "\xf6" => 0xf6,
        "\xf7" => 0xf7,
        "\xf8" => 0xf8,
        "\xf9" => 0xf9,
        "\xfa" => 0xfa,
        "\xfb" => 0xfb,
        "\xfc" => 0xfc,
        "\xfd" => 0xfd,
        "\xfe" => 0xfe,
        "\xff" => 0xff,
    ];

    /**
     * Mapa estático de decodificación de secuencias percent-encoded a sus valores de byte crudos ($00$ a $ff$).
     * 
     * Permite resolver de forma determinista y sin sobrecarga de ejecución (`sprintf`, `dechex`) 
     * los caracteres codificados en URIs dentro del autómata de enrutamiento.
     * 
     * @var array<string, int>
     */
    public const PERCENT_ENCODING_MAP = [
        "%00" => 0x00,
        "%01" => 0x01,
        "%02" => 0x02,
        "%03" => 0x03,
        "%04" => 0x04,
        "%05" => 0x05,
        "%06" => 0x06,
        "%07" => 0x07,
        "%08" => 0x08,
        "%09" => 0x09,
        "%0a" => 0x0a,
        "%0b" => 0x0b,
        "%0c" => 0x0c,
        "%0d" => 0x0d,
        "%0e" => 0x0e,
        "%0f" => 0x0f,
        "%10" => 0x10,
        "%11" => 0x11,
        "%12" => 0x12,
        "%13" => 0x13,
        "%14" => 0x14,
        "%15" => 0x15,
        "%16" => 0x16,
        "%17" => 0x17,
        "%18" => 0x18,
        "%19" => 0x19,
        "%1a" => 0x1a,
        "%1b" => 0x1b,
        "%1c" => 0x1c,
        "%1d" => 0x1d,
        "%1e" => 0x1e,
        "%1f" => 0x1f,
        "%20" => 0x20,
        "%21" => 0x21,
        "%22" => 0x22,
        "%23" => 0x23,
        "%24" => 0x24,
        "%25" => 0x25,
        "%26" => 0x26,
        "%27" => 0x27,
        "%28" => 0x28,
        "%29" => 0x29,
        "%2a" => 0x2a,
        "%2b" => 0x2b,
        "%2c" => 0x2c,
        "%2d" => 0x2d,
        "%2e" => 0x2e,
        "%2f" => 0x2f,
        "%30" => 0x30,
        "%31" => 0x31,
        "%32" => 0x32,
        "%33" => 0x33,
        "%34" => 0x34,
        "%35" => 0x35,
        "%36" => 0x36,
        "%37" => 0x37,
        "%38" => 0x38,
        "%39" => 0x39,
        "%3a" => 0x3a,
        "%3b" => 0x3b,
        "%3c" => 0x3c,
        "%3d" => 0x3d,
        "%3e" => 0x3e,
        "%3f" => 0x3f,
        "%40" => 0x40,
        "%41" => 0x41,
        "%42" => 0x42,
        "%43" => 0x43,
        "%44" => 0x44,
        "%45" => 0x45,
        "%46" => 0x46,
        "%47" => 0x47,
        "%48" => 0x48,
        "%49" => 0x49,
        "%4a" => 0x4a,
        "%4b" => 0x4b,
        "%4c" => 0x4c,
        "%4d" => 0x4d,
        "%4e" => 0x4e,
        "%4f" => 0x4f,
        "%50" => 0x50,
        "%51" => 0x51,
        "%52" => 0x52,
        "%53" => 0x53,
        "%54" => 0x54,
        "%55" => 0x55,
        "%56" => 0x56,
        "%57" => 0x57,
        "%58" => 0x58,
        "%59" => 0x59,
        "%5a" => 0x5a,
        "%5b" => 0x5b,
        "%5c" => 0x5c,
        "%5d" => 0x5d,
        "%5e" => 0x5e,
        "%5f" => 0x5f,
        "%60" => 0x60,
        "%61" => 0x61,
        "%62" => 0x62,
        "%63" => 0x63,
        "%64" => 0x64,
        "%65" => 0x65,
        "%66" => 0x66,
        "%67" => 0x67,
        "%68" => 0x68,
        "%69" => 0x69,
        "%6a" => 0x6a,
        "%6b" => 0x6b,
        "%6c" => 0x6c,
        "%6d" => 0x6d,
        "%6e" => 0x6e,
        "%6f" => 0x6f,
        "%70" => 0x70,
        "%71" => 0x71,
        "%72" => 0x72,
        "%73" => 0x73,
        "%74" => 0x74,
        "%75" => 0x75,
        "%76" => 0x76,
        "%77" => 0x77,
        "%78" => 0x78,
        "%79" => 0x79,
        "%7a" => 0x7a,
        "%7b" => 0x7b,
        "%7c" => 0x7c,
        "%7d" => 0x7d,
        "%7e" => 0x7e,
        "%7f" => 0x7f,
        "%80" => 0x80,
        "%81" => 0x81,
        "%82" => 0x82,
        "%83" => 0x83,
        "%84" => 0x84,
        "%85" => 0x85,
        "%86" => 0x86,
        "%87" => 0x87,
        "%88" => 0x88,
        "%89" => 0x89,
        "%8a" => 0x8a,
        "%8b" => 0x8b,
        "%8c" => 0x8c,
        "%8d" => 0x8d,
        "%8e" => 0x8e,
        "%8f" => 0x8f,
        "%90" => 0x90,
        "%91" => 0x91,
        "%92" => 0x92,
        "%93" => 0x93,
        "%94" => 0x94,
        "%95" => 0x95,
        "%96" => 0x96,
        "%97" => 0x97,
        "%98" => 0x98,
        "%99" => 0x99,
        "%9a" => 0x9a,
        "%9b" => 0x9b,
        "%9c" => 0x9c,
        "%9d" => 0x9d,
        "%9e" => 0x9e,
        "%9f" => 0x9f,
        "%a0" => 0xa0,
        "%a1" => 0xa1,
        "%a2" => 0xa2,
        "%a3" => 0xa3,
        "%a4" => 0xa4,
        "%a5" => 0xa5,
        "%a6" => 0xa6,
        "%a7" => 0xa7,
        "%a8" => 0xa8,
        "%a9" => 0xa9,
        "%aa" => 0xaa,
        "%ab" => 0xab,
        "%ac" => 0xac,
        "%ad" => 0xad,
        "%ae" => 0xae,
        "%af" => 0xaf,
        "%b0" => 0xb0,
        "%b1" => 0xb1,
        "%b2" => 0xb2,
        "%b3" => 0xb3,
        "%b4" => 0xb4,
        "%b5" => 0xb5,
        "%b6" => 0xb6,
        "%b7" => 0xb7,
        "%b8" => 0xb8,
        "%b9" => 0xb9,
        "%ba" => 0xba,
        "%bb" => 0xbb,
        "%bc" => 0xbc,
        "%bd" => 0xbd,
        "%be" => 0xbe,
        "%bf" => 0xbf,
        "%c0" => 0xc0,
        "%c1" => 0xc1,
        "%c2" => 0xc2,
        "%c3" => 0xc3,
        "%c4" => 0xc4,
        "%c5" => 0xc5,
        "%c6" => 0xc6,
        "%c7" => 0xc7,
        "%c8" => 0xc8,
        "%c9" => 0xc9,
        "%ca" => 0xca,
        "%cb" => 0xcb,
        "%cc" => 0xcc,
        "%cd" => 0xcd,
        "%ce" => 0xce,
        "%cf" => 0xcf,
        "%d0" => 0xd0,
        "%d1" => 0xd1,
        "%d2" => 0xd2,
        "%d3" => 0xd3,
        "%d4" => 0xd4,
        "%d5" => 0xd5,
        "%d6" => 0xd6,
        "%d7" => 0xd7,
        "%d8" => 0xd8,
        "%d9" => 0xd9,
        "%da" => 0xda,
        "%db" => 0xdb,
        "%dc" => 0xdc,
        "%dd" => 0xdd,
        "%de" => 0xde,
        "%df" => 0xdf,
        "%e0" => 0xe0,
        "%e1" => 0xe1,
        "%e2" => 0xe2,
        "%e3" => 0xe3,
        "%e4" => 0xe4,
        "%e5" => 0xe5,
        "%e6" => 0xe6,
        "%e7" => 0xe7,
        "%e8" => 0xe8,
        "%e9" => 0xe9,
        "%ea" => 0xea,
        "%eb" => 0xeb,
        "%ec" => 0xec,
        "%ed" => 0xed,
        "%ee" => 0xee,
        "%ef" => 0xef,
        "%f0" => 0xf0,
        "%f1" => 0xf1,
        "%f2" => 0xf2,
        "%f3" => 0xf3,
        "%f4" => 0xf4,
        "%f5" => 0xf5,
        "%f6" => 0xf6,
        "%f7" => 0xf7,
        "%f8" => 0xf8,
        "%f9" => 0xf9,
        "%fa" => 0xfa,
        "%fb" => 0xfb,
        "%fc" => 0xfc,
        "%fd" => 0xfd,
        "%fe" => 0xfe,
        "%ff" => 0xff
    ];

    /**
     * Bytes que provocan el rechazo de la petición con una excepción.
     *
     * Se evalúan sobre los segmentos de la ruta y sobre los nombres de los parámetros del querystring,
     * no sobre los valores, que llegan crudos. Si aparece alguno, el analizador lanza una `RouteException`
     * en lugar de normalizarlo.
     *
     * Son bytes que RFC 3986 no admite sin codificar y que, además, pueden alterar el significado de la ruta
     * o resultar peligrosos si se escriben sin escapar, o bytes que nunca aparecen en UTF-8 válido:
     *
     *  - `NUL` (`0x00`): no es válido en rutas del sistema de archivos.
     *  - `"` (`0x22`), `<` (`0x3C`) y `>` (`0x3E`): delimitadores de HTML.
     *  - `\` (`0x5C`): separador de directorios en algunos sistemas.
     *  - `^` (`0x5E`), `` ` `` (`0x60`) y `|` (`0x7C`).
     *  - `0xC0` y `0xC1`: nunca son válidos en UTF-8 (solo codificarían secuencias sobrelargas).
     *  - `0xF5` a `0xFF`: nunca son válidos en UTF-8.
     *
     * Las llaves `{` (`0x7B`) y `}` (`0x7D`) no se rechazan: pasan como cualquier otro byte.
     *
     * No incluye los controles «0x01» a «0x1F», el espacio («0x20») ni «DEL» («0x7F»), que se sustituyen
     * por «_», ni el resto de bytes «0x80» a «0xFF» (secuencias UTF-8 válidas).
     *
     * Tampoco se decodifican las secuencias «%XX»: «%5c» llega como texto literal y no se rechaza.
     *
     * Las claves son símbolos de un byte, igual que en {@see self::SYMBOL_TO_BYTE}, y se
     * consultan con `self::REJECTED_BYTES[$byte] ?? false`:
     *
     * ```php
     * if (self::REJECTED_BYTES[$byte] ?? false) {
     *     throw new RouteException(message: "...", code: 403);
     * }
     * ```
     *
     * @var array<string,true>
     */
    public const REJECTED_BYTES = [
        "\x00" => true,
        "\x22" => true,
        "\x3c" => true,
        "\x3e" => true,
        "\x5c" => true,
        "\x5e" => true,
        "\x60" => true,
        "\x7c" => true,
        "\xc0" => true,
        "\xc1" => true,
        "\xf5" => true,
        "\xf6" => true,
        "\xf7" => true,
        "\xf8" => true,
        "\xf9" => true,
        "\xfa" => true,
        "\xfb" => true,
        "\xfc" => true,
        "\xfd" => true,
        "\xfe" => true,
        "\xff" => true,
    ];
}
