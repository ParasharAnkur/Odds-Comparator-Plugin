<?php
// Class to handle odds format conversion: fractional, decimal, and American
class OC_Odds_Converter {

    // Convert fractional odds (e.g. "5/2") to decimal format
    public static function to_decimal($fraction) {
        list($num, $den) = explode('/', $fraction);
        return $den != 0 ? round(($num / $den) + 1, 2) : 0;
    }

    // Convert decimal odds (e.g. 2.5) to fractional format
    public static function to_fractional($decimal) {
        $decimal -= 1;
        return self::decimal_to_fraction($decimal);
    }

    // Convert decimal odds to American format
    public static function to_american($decimal) {
        return $decimal >= 2.00
            ? '+' . round(($decimal - 1) * 100)
            : round(-100 / ($decimal - 1));
    }

    // Safe conversion of decimal to fractional (e.g. 1.75 => "3/4")
    private static function decimal_to_fraction($decimal) {
        $tolerance = 1.0E-6;
        $h1 = 1; $h2 = 0;
        $k1 = 0; $k2 = 1;
        $b = $decimal;

        // If input is exactly 0 or whole number, return safe fraction
        if ($decimal <= 0) return "0/1";
        if ($b == floor($b)) return ($b) . "/1";

        do {
            $a = floor($b);
            $aux = $h1; $h1 = $a * $h1 + $h2; $h2 = $aux;
            $aux = $k1; $k1 = $a * $k1 + $k2; $k2 = $aux;

            if ($b - $a == 0) break; // avoid division by zero

            $b = 1 / ($b - $a);
        } while (abs($decimal - $h1 / $k1) > $decimal * $tolerance);

        return "$h1/$k1";
    }
}
